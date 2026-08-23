import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, router, useForm } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import {
  AlertCircle,
  Check,
  CheckCircle2,
  Clipboard,
  Copy,
  MapPin,
  Contact,
  Phone,
  Search,
  UserPlus,
  X,
} from 'lucide-react';

interface Suggestion {
  id: number;
  name: string;
  email: string;
  latest_address: string;
  score: number;
}

interface ExtractedContact {
  name: string;
  phone: string;
  address: string;
  postcode: string;
  suggestions: Suggestion[];
}

interface PhoneConflict {
  user_id: number;
  user_name: string;
  name: string;
  phone: string;
  address: string;
  postcode: string;
}

interface CustomerSearchResult {
  id: number;
  name: string;
  email: string | null;
  no_tel: string | null;
}

interface ExtractProps {
  rawText: string;
  contacts: ExtractedContact[];
  swalError?: string | null;
  duplicateError?: string | null;
  phoneConflict?: PhoneConflict | null;
  success?: string | null;
  successType?: 'customer' | 'address' | null;
  createdUserId?: number | null;
  createdAddressId?: number | null;
}

interface CopyableValueProps {
  label: string;
  value: string;
  copied: boolean;
  icon: React.ComponentType<{ className?: string }>;
  onCopy: () => void;
}

function CopyableValue({ label, value, copied, icon: Icon, onCopy }: CopyableValueProps) {
  return (
    <button
      type="button"
      onClick={onCopy}
      className="group flex w-full items-start gap-3 rounded-xl border border-transparent p-2 text-left transition hover:border-brand-200 hover:bg-brand-50"
      title={`Klik untuk salin ${label.toLowerCase()}`}
    >
      <span className="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500 transition group-hover:bg-white group-hover:text-brand-600">
        <Icon className="h-3.5 w-3.5" />
      </span>
      <span className="min-w-0 flex-1">
        <span className="block text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400">{label}</span>
        <span className="mt-0.5 block break-words text-sm font-medium text-slate-700">{value}</span>
      </span>
      <span className="mt-1 shrink-0 text-slate-400 transition group-hover:text-brand-600" aria-hidden="true">
        {copied ? <Check className="h-4 w-4 text-emerald-500" /> : <Copy className="h-4 w-4" />}
      </span>
    </button>
  );
}

function phoneForCopy(phone: string): string {
  let digits = phone.replace(/\D/g, '');

  if (digits.startsWith('00')) {
    digits = digits.slice(2);
  }

  if (digits.startsWith('60')) {
    return digits.slice(2);
  }

  return digits.startsWith('0') ? digits.slice(1) : digits;
}

export default function Extract({ rawText, contacts, swalError, duplicateError, phoneConflict, success, successType, createdUserId, createdAddressId }: ExtractProps) {
  const {
    data: extractData,
    setData: setExtractData,
    post: postExtract,
    processing: extracting,
  } = useForm({ raw_text: rawText });

  const [expanded, setExpanded] = useState<Record<number, boolean>>({});
  const [copiedField, setCopiedField] = useState<string | null>(null);
  const [creatingKey, setCreatingKey] = useState<string | null>(null);
  const [pendingConflict, setPendingConflict] = useState<PhoneConflict | null>(null);
  const [notice, setNotice] = useState<string | null>(null);
  const [successModalOpen, setSuccessModalOpen] = useState(false);
  const [addressContact, setAddressContact] = useState<ExtractedContact | null>(null);
  const [addressSearch, setAddressSearch] = useState('');
  const [addressResults, setAddressResults] = useState<CustomerSearchResult[]>([]);
  const [searchingCustomers, setSearchingCustomers] = useState(false);
  const [addingAddress, setAddingAddress] = useState(false);

  useEffect(() => {
    setNotice(duplicateError ?? swalError ?? null);
  }, [duplicateError, swalError]);

  useEffect(() => {
    if (phoneConflict) {
      setPendingConflict(phoneConflict);
    }
  }, [phoneConflict]);

  useEffect(() => {
    if (!success || !createdUserId || !createdAddressId) {
      setSuccessModalOpen(false);
      return;
    }

    setSuccessModalOpen(true);
    const timeout = window.setTimeout(() => {
      router.visit(route('admin.projects.create', { user_id: createdUserId, address_id: createdAddressId }));
    }, 1600);

    return () => window.clearTimeout(timeout);
  }, [success, createdUserId, createdAddressId]);

  useEffect(() => {
    if (!addressContact) {
      setAddressResults([]);
      setSearchingCustomers(false);
      return;
    }

    const query = addressSearch.trim();
    if (query.length < 2) {
      setAddressResults([]);
      setSearchingCustomers(false);
      return;
    }

    const controller = new AbortController();
    const timeout = window.setTimeout(async () => {
      setSearchingCustomers(true);

      try {
        const response = await fetch(`${route('admin.contacts.extract.customers.search')}?q=${encodeURIComponent(query)}`, {
          headers: { Accept: 'application/json' },
          signal: controller.signal,
        });

        if (response.ok) {
          const payload = await response.json() as { results: CustomerSearchResult[] };
          setAddressResults(payload.results);
        } else {
          setAddressResults([]);
        }
      } catch (error) {
        if ((error as DOMException).name !== 'AbortError') {
          setAddressResults([]);
        }
      } finally {
        if (!controller.signal.aborted) {
          setSearchingCustomers(false);
        }
      }
    }, 250);

    return () => {
      window.clearTimeout(timeout);
      controller.abort();
    };
  }, [addressContact, addressSearch]);

  const handleExtract = (e: React.FormEvent) => {
    e.preventDefault();
    postExtract(route('admin.contacts.extract.run'));
  };

  const toggleExpand = (index: number) => {
    setExpanded((prev) => ({ ...prev, [index]: !prev[index] }));
  };

  const copyText = async (value: string, key: string) => {
    try {
      if (navigator.clipboard?.writeText) {
        await navigator.clipboard.writeText(value);
      } else {
        throw new Error('Clipboard API tidak tersedia.');
      }
    } catch {
      const fallback = document.createElement('textarea');
      fallback.value = value;
      fallback.setAttribute('readonly', '');
      fallback.style.position = 'fixed';
      fallback.style.opacity = '0';
      document.body.appendChild(fallback);
      fallback.select();
      document.execCommand('copy');
      fallback.remove();
    }

    setCopiedField(key);
    window.setTimeout(() => {
      setCopiedField((current) => current === key ? null : current);
    }, 1400);
  };

  const getContactKey = (contact: Pick<ExtractedContact, 'name' | 'phone' | 'address'>) => (
    `${contact.name}|${contact.phone}|${contact.address}`
  );

  const createCustomer = (contact: ExtractedContact, forceAddress = false) => {
    const key = getContactKey(contact);
    setCreatingKey(key);

    router.post(route('admin.contacts.extract.add-user'), {
      name: contact.name,
      phone: contact.phone,
      address: contact.address,
      postcode: contact.postcode,
      ...(forceAddress ? { force_address: true } : {}),
    }, {
      preserveScroll: true,
      onFinish: () => setCreatingKey(null),
    });
  };

  const confirmAddress = () => {
    if (!pendingConflict) return;

    const contact: ExtractedContact = {
      name: pendingConflict.name,
      phone: pendingConflict.phone,
      address: pendingConflict.address,
      postcode: pendingConflict.postcode,
      suggestions: [],
    };

    setPendingConflict(null);
    createCustomer(contact, true);
  };

  const openAddressModal = (contact: ExtractedContact) => {
    setAddressContact(contact);
    setAddressSearch('');
    setAddressResults([]);
  };

  const addExtractedAddress = (contact: ExtractedContact, userId: number, redirectToProject = false) => {
    setAddingAddress(true);
    router.post(route('admin.contacts.extract.add-address'), {
      user_id: userId,
      name: contact.name,
      phone: contact.phone,
      address: contact.address,
      postcode: contact.postcode,
      ...(redirectToProject ? { redirect_to_project: true } : {}),
    }, {
      preserveScroll: true,
      onStart: () => {
        if (redirectToProject) setAddressContact(null);
      },
      onFinish: () => setAddingAddress(false),
    });
  };

  const addAddressToCustomer = (customer: CustomerSearchResult) => {
    if (!addressContact) return;

    addExtractedAddress(addressContact, customer.id, true);
  };

  return (
    <AdminLayout>
      <Head title="Ekstrak Contact" />
      <div className="flex flex-col gap-6">
        <div className="admin-page-head">
          <div>
            <h2 className="text-2xl font-bold text-slate-900">Ekstrak Contact</h2>
            <p className="admin-page-copy">
              Tampal teks untuk mengekstrak maklumat contact secara automatik.
            </p>
          </div>
        </div>

        {notice && (
          <div role="alert" className="flex items-start gap-3 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
            <AlertCircle className="mt-0.5 h-5 w-5 shrink-0" />
            <p className="flex-1 font-medium">{notice}</p>
            <button
              type="button"
              onClick={() => setNotice(null)}
              className="rounded-lg p-1 text-rose-500 transition hover:bg-rose-100"
              aria-label="Tutup mesej"
            >
              <X className="h-4 w-4" />
            </button>
          </div>
        )}

        <form onSubmit={handleExtract} className="order-2 admin-flat-card space-y-4 p-5 sm:p-6">
          <div className="flex items-start gap-3">
            <div className="admin-icon-badge">
              <Search className="h-5 w-5" />
            </div>
            <div>
              <label htmlFor="raw_text" className="block">
                Teks Sumber
              </label>
              <p className="mt-1 text-sm text-slate-500">AI akan asingkan nama, nombor telefon dan alamat daripada teks yang ditampal.</p>
            </div>
          </div>
          <textarea
            id="raw_text"
            value={extractData.raw_text}
            onChange={(e) => setExtractData('raw_text', e.target.value)}
            rows={8}
            placeholder="Tampal teks WhatsApp, senarai contact atau format NAMA | NO TELEFON | ALAMAT..."
            className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
          />
          <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div className="flex items-center gap-2 text-xs text-slate-500">
              <Clipboard className="h-3.5 w-3.5" />
              <span>Klik mana-mana nama, telefon atau alamat untuk salin.</span>
            </div>
            <button
              type="submit"
              disabled={extracting}
              className="admin-btn-primary text-sm disabled:cursor-not-allowed disabled:opacity-60"
            >
              <Search className="h-4 w-4" />
              {extracting ? 'Mengekstrak...' : 'Ekstrak Dengan AI'}
            </button>
          </div>
        </form>

        {contacts.length > 0 && (
          <div className="order-1 space-y-4">
            <div className="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
              <div>
                <p className="admin-mini-label">Maklumat tersusun</p>
                <h3 className="text-lg font-bold text-slate-900">Hasil Ekstrak</h3>
              </div>
              <span className="admin-soft-badge">{contacts.length} contact</span>
            </div>
            {contacts.map((contact, idx) => {
              const key = getContactKey(contact);
              const isCreating = creatingKey === key;
              const displayName = contact.name.toLocaleUpperCase('ms-MY');
              const displayPhone = contact.phone.toLocaleUpperCase('ms-MY');
              const displayAddress = contact.address.toLocaleUpperCase('ms-MY');

              return (
                <div key={`${key}-${contact.postcode}`} className="admin-flat-card overflow-hidden">
                  <div className="border-b border-slate-100 bg-slate-50/70 px-4 py-3 sm:px-6">
                    <div className="flex items-center justify-between gap-3">
                      <div>
                        <p className="text-[10px] font-bold uppercase tracking-[0.18em] text-brand-600">Contact {idx + 1}</p>
                        <p className="mt-0.5 text-xs text-slate-500">Klik maklumat untuk copy terus ke clipboard</p>
                      </div>
                      <span className="rounded-full bg-white px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-400 shadow-sm">
                        AI
                      </span>
                    </div>
                  </div>
                  <div className="p-4 sm:p-6">
                    <div className="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                      <div className="grid min-w-0 flex-1 gap-1 sm:grid-cols-2 xl:grid-cols-3">
                        <CopyableValue
                          label="Nama"
                          value={displayName}
                          icon={Contact}
                          copied={copiedField === `${key}-name`}
                          onCopy={() => copyText(displayName, `${key}-name`)}
                        />
                        <CopyableValue
                          label="No. Telefon"
                          value={displayPhone}
                          icon={Phone}
                          copied={copiedField === `${key}-phone`}
                          onCopy={() => copyText(phoneForCopy(displayPhone), `${key}-phone`)}
                        />
                        <CopyableValue
                          label="Alamat"
                          value={displayAddress}
                          icon={MapPin}
                          copied={copiedField === `${key}-address`}
                          onCopy={() => copyText(displayAddress, `${key}-address`)}
                        />
                      </div>
                      <div className="flex shrink-0 flex-col gap-2 sm:flex-row xl:flex-col">
                        <button
                          type="button"
                          onClick={() => createCustomer(contact)}
                          disabled={isCreating || creatingKey !== null}
                          className="admin-btn-primary text-xs disabled:cursor-not-allowed disabled:opacity-60"
                        >
                          <UserPlus className="h-3.5 w-3.5" />
                          {isCreating ? 'Menyimpan...' : 'Create Customer'}
                        </button>
                        <button
                          type="button"
                          onClick={() => openAddressModal(contact)}
                          disabled={creatingKey !== null || addingAddress}
                          className="admin-btn-secondary text-xs disabled:cursor-not-allowed disabled:opacity-60"
                        >
                          <MapPin className="h-3.5 w-3.5" />
                          Tambah Alamat
                        </button>
                      </div>
                    </div>

                    {contact.postcode !== '-' && (
                      <p className="mt-3 pl-2 text-xs text-slate-500">Poskod: {contact.postcode}</p>
                    )}

                    {contact.suggestions.length > 0 && (
                      <div className="mt-5 border-t border-slate-100 pt-4">
                        <button
                          type="button"
                          onClick={() => toggleExpand(idx)}
                          className="text-xs font-semibold text-brand-600 hover:text-brand-700"
                        >
                          {expanded[idx] ? 'Sembunyi' : 'Tunjuk'} padanan customer ({contact.suggestions.length})
                        </button>
                        {expanded[idx] && (
                          <div className="mt-3 space-y-2">
                            {contact.suggestions.map((s) => (
                              <form
                                key={s.id}
                                onSubmit={(e) => {
                                  e.preventDefault();
                                  addExtractedAddress(contact, s.id);
                                }}
                                className="flex flex-col gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3 sm:flex-row sm:items-center sm:justify-between"
                              >
                                <div className="min-w-0 text-sm">
                                  <p className="font-medium text-slate-900">{s.name}</p>
                                  <p className="truncate text-xs text-slate-500">{s.email} — {s.latest_address}</p>
                                </div>
                                <button
                                  type="submit"
                                  disabled={addingAddress}
                                  className="admin-btn-secondary shrink-0 text-xs disabled:cursor-not-allowed disabled:opacity-60"
                                >
                                  <MapPin className="h-3 w-3" />
                                  Tambah Alamat
                                </button>
                              </form>
                            ))}
                          </div>
                        )}
                      </div>
                    )}
                  </div>
                </div>
              );
            })}
          </div>
        )}
      </div>

      {pendingConflict && (
        <div className="fixed inset-0 z-[90] flex items-center justify-center bg-slate-950/55 p-4" role="dialog" aria-modal="true" aria-labelledby="phone-conflict-title">
          <div className="w-full max-w-lg overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl">
            <div className="flex items-start justify-between gap-4 border-b border-slate-100 px-5 py-4 sm:px-6">
              <div className="flex items-start gap-3">
                <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">
                  <AlertCircle className="h-5 w-5" />
                </div>
                <div>
                  <h2 id="phone-conflict-title" className="font-bold text-slate-900">Nombor telefon sudah wujud</h2>
                  <p className="mt-1 text-sm text-slate-500">Nombor ini telah digunakan oleh customer sedia ada.</p>
                </div>
              </div>
              <button
                type="button"
                onClick={() => setPendingConflict(null)}
                className="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                aria-label="Tutup modal"
              >
                <X className="h-5 w-5" />
              </button>
            </div>
            <div className="space-y-4 px-5 py-5 sm:px-6">
              <div className="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                <p>Customer: <strong>{pendingConflict.user_name}</strong></p>
                <p className="mt-1">Telefon: <strong>{pendingConflict.phone}</strong></p>
              </div>
              <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p className="admin-mini-label">Alamat baharu</p>
                <p className="mt-2 text-sm font-medium leading-6 text-slate-800">{pendingConflict.address}</p>
              </div>
              <p className="text-sm leading-6 text-slate-600">Adakah anda mahu simpan alamat ini di bawah customer tersebut?</p>
            </div>
            <div className="flex flex-col-reverse gap-2 border-t border-slate-100 bg-slate-50 px-5 py-4 sm:flex-row sm:justify-end sm:px-6">
              <button
                type="button"
                onClick={() => setPendingConflict(null)}
                className="admin-btn-secondary w-full text-sm sm:w-auto"
              >
                Batal
              </button>
              <button
                type="button"
                onClick={confirmAddress}
                disabled={creatingKey !== null}
                className="admin-btn-primary w-full text-sm disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
              >
                <MapPin className="h-4 w-4" />
                Simpan Alamat
              </button>
            </div>
          </div>
        </div>
      )}

      {addressContact && (
        <div className="fixed inset-0 z-[90] flex items-center justify-center bg-slate-950/55 p-4" role="dialog" aria-modal="true" aria-labelledby="add-address-title">
          <div className="w-full max-w-lg overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl">
            <div className="flex items-start justify-between gap-4 border-b border-slate-100 px-5 py-4 sm:px-6">
              <div className="flex items-start gap-3">
                <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-brand-50 text-brand-600">
                  <MapPin className="h-5 w-5" />
                </div>
                <div>
                  <h2 id="add-address-title" className="font-bold text-slate-900">Tambah Alamat Customer</h2>
                  <p className="mt-1 text-sm text-slate-500">Cari user yang hendak menerima alamat ini.</p>
                </div>
              </div>
              <button
                type="button"
                onClick={() => setAddressContact(null)}
                className="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                aria-label="Tutup modal tambah alamat"
              >
                <X className="h-5 w-5" />
              </button>
            </div>
            <div className="space-y-4 px-5 py-5 sm:px-6">
              <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm">
                <p className="font-semibold text-slate-900">{addressContact.name}</p>
                <p className="mt-1 text-slate-500">{addressContact.phone}</p>
                <p className="mt-2 leading-6 text-slate-700">{addressContact.address}</p>
              </div>
              <div>
                <label htmlFor="address-customer-search" className="admin-mini-label">Cari user</label>
                <input
                  id="address-customer-search"
                  type="search"
                  value={addressSearch}
                  onChange={(event) => setAddressSearch(event.target.value)}
                  placeholder="Nama, email atau nombor telefon..."
                  className="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                />
              </div>
              <div className="max-h-56 overflow-y-auto rounded-xl border border-slate-200">
                {addressSearch.trim().length < 2 ? (
                  <p className="p-4 text-sm text-slate-500">Taip sekurang-kurangnya 2 aksara untuk mencari user.</p>
                ) : searchingCustomers ? (
                  <p className="p-4 text-sm text-slate-500">Mencari user...</p>
                ) : addressResults.length === 0 ? (
                  <p className="p-4 text-sm text-slate-500">User tidak dijumpai.</p>
                ) : (
                  addressResults.map((customer) => (
                    <button
                      key={customer.id}
                      type="button"
                      onClick={() => addAddressToCustomer(customer)}
                      disabled={addingAddress}
                      className="flex w-full flex-col border-b border-slate-100 px-4 py-3 text-left transition last:border-b-0 hover:bg-brand-50 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                      <span className="text-sm font-semibold text-slate-900">{customer.name}</span>
                      <span className="mt-0.5 text-xs text-slate-500">{customer.no_tel ?? customer.email ?? 'Tiada maklumat tambahan'}</span>
                    </button>
                  ))
                )}
              </div>
            </div>
          </div>
        </div>
      )}

      {successModalOpen && createdUserId && (
        <div className="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/60 p-4" role="dialog" aria-modal="true" aria-labelledby="create-customer-success-title">
          <div className="w-full max-w-md rounded-3xl border border-emerald-200 bg-white p-6 text-center shadow-2xl sm:p-8">
            <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
              <CheckCircle2 className="h-8 w-8" />
            </div>
            <h2 id="create-customer-success-title" className="mt-4 text-xl font-bold text-slate-900">{successType === 'address' ? 'Alamat berjaya ditambah' : 'Customer berjaya dicipta'}</h2>
            <p className="mt-2 text-sm leading-6 text-slate-600">{success}</p>
            <p className="mt-3 text-xs font-semibold uppercase tracking-[0.14em] text-brand-600">Membuka Create Project...</p>
            <button
              type="button"
              onClick={() => router.visit(route('admin.projects.create', { user_id: createdUserId, address_id: createdAddressId }))}
              className="admin-btn-primary mt-6 w-full justify-center"
            >
              Teruskan Sekarang
            </button>
          </div>
        </div>
      )}
    </AdminLayout>
  );
}
