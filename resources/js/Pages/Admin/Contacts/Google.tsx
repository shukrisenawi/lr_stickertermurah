import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';
import {
  AlertCircle,
  ArrowDown,
  ArrowUp,
  ArrowUpDown,
  CheckCircle2,
  ContactRound,
  Link2,
  MessageCircle,
  Pencil,
  Plus,
  Search,
  ShieldCheck,
  Trash2,
  Unlink,
  X,
} from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

interface Connection {
  email: string | null;
  connected_at: string | null;
}

interface GoogleContact {
  resource_name: string;
  etag: string | null;
  name: string;
  phone: string | null;
  email: string | null;
  address: string | null;
}

type ContactSort = 'latest' | 'name' | 'phone' | 'email' | 'address';
type SortDirection = 'asc' | 'desc';
type ContactGroup = 'company' | 'sticker' | 'personal';

interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

interface PaginatedGoogleContacts {
  data: GoogleContact[];
  from: number | null;
  to: number | null;
  total: number;
  last_page: number;
  links: PaginationLink[];
}

interface GoogleContactsProps {
  isConfigured: boolean;
  callbackUrl: string;
  connection: Connection | null;
  contacts: PaginatedGoogleContacts;
  contactSearch: string;
  contactSort: ContactSort;
  contactDirection: SortDirection;
  contactGroup: ContactGroup;
  contactsError: string | null;
}

interface UpdateForm {
  resource_name: string;
  etag: string;
  name: string;
  phone: string;
  email: string;
  address: string;
}

const fieldClass = 'mt-1.5';

function paginationLabel(label: string): string {
  return label
    .replace(/&laquo;|&raquo;/g, '')
    .replace('Previous', 'Sebelumnya')
    .replace('Next', 'Seterusnya')
    .trim();
}

function whatsappUrl(phone: string | null): string | null {
  const digits = (phone ?? '').replace(/\D/g, '');
  if (digits === '') return null;

  const normalized = digits.startsWith('00')
    ? digits.slice(2)
    : digits.startsWith('0')
      ? `60${digits.slice(1)}`
      : digits;

  return normalized.length >= 9 && normalized.length <= 15
    ? `https://wa.me/${normalized}`
    : null;
}

export default function GoogleContacts({ isConfigured, callbackUrl, connection, contacts, contactSearch: initialContactSearch, contactSort, contactDirection, contactGroup, contactsError }: GoogleContactsProps) {
  const [editingContact, setEditingContact] = useState<GoogleContact | null>(null);
  const [contactSearch, setContactSearch] = useState(initialContactSearch);
  const [selectedResources, setSelectedResources] = useState<string[]>([]);
  const searchReady = useRef(false);
  const searchSort = useRef({ sort: contactSort, direction: contactDirection });
  const searchGroup = useRef<ContactGroup>(contactGroup);
  searchSort.current = { sort: contactSort, direction: contactDirection };
  searchGroup.current = contactGroup;
  const updateForm = useForm<UpdateForm>({
    resource_name: '',
    etag: '',
    name: '',
    phone: '',
    email: '',
    address: '',
  });
  const visibleResourceNames = contacts.data.map((contact) => contact.resource_name);
  const selectedVisibleCount = visibleResourceNames.filter((resourceName) => selectedResources.includes(resourceName)).length;
  const allVisibleSelected = visibleResourceNames.length > 0 && selectedVisibleCount === visibleResourceNames.length;
  const contactGroupLabel = contactGroup === 'company' ? 'Company' : contactGroup === 'sticker' ? 'Sticker' : 'Personal';

  useEffect(() => {
    if (!searchReady.current) {
      searchReady.current = true;
      return;
    }

    const timeout = window.setTimeout(() => {
      const query = contactSearch.trim();

      router.get(
        route('admin.contacts.google.index'),
        {
          ...(query === '' ? {} : { q: query }),
          sort: searchSort.current.sort,
          direction: searchSort.current.direction,
          group: searchGroup.current,
        },
        { preserveState: true, preserveScroll: true, replace: true },
      );
    }, 300);

    return () => window.clearTimeout(timeout);
  }, [contactSearch]);

  const sortContacts = (column: ContactSort) => {
    const nextDirection: SortDirection = contactSort === column && contactDirection === 'asc' ? 'desc' : 'asc';
    const query = contactSearch.trim();

    router.get(
      route('admin.contacts.google.index'),
      {
        ...(query === '' ? {} : { q: query }),
        sort: column,
        direction: nextDirection,
        group: contactGroup,
      },
      { preserveState: true, preserveScroll: true },
    );
  };

  const sortIcon = (column: ContactSort) => {
    if (contactSort !== column) return <ArrowUpDown className="h-3.5 w-3.5 text-slate-400" />;

    return contactDirection === 'asc'
      ? <ArrowUp className="h-3.5 w-3.5 text-brand-600" />
      : <ArrowDown className="h-3.5 w-3.5 text-brand-600" />;
  };

  const switchGroup = (group: ContactGroup) => {
    if (group === contactGroup) return;

    const query = contactSearch.trim();
    router.get(
      route('admin.contacts.google.index'),
      {
        ...(query === '' ? {} : { q: query }),
        sort: contactSort,
        direction: contactDirection,
        group,
      },
      { preserveState: true, preserveScroll: true },
    );
  };

  const toggleContactSelection = (resourceName: string) => {
    setSelectedResources((current) => current.includes(resourceName)
      ? current.filter((selectedResource) => selectedResource !== resourceName)
      : [...current, resourceName]);
  };

  const toggleVisibleSelection = () => {
    setSelectedResources((current) => {
      if (allVisibleSelected) {
        return current.filter((resourceName) => !visibleResourceNames.includes(resourceName));
      }

      return Array.from(new Set([...current, ...visibleResourceNames]));
    });
  };

  const disconnect = () => {
    if (window.confirm('Putuskan sambungan akaun Google Contacts ini?')) {
      router.post(route('admin.contacts.google.disconnect'));
    }
  };

  const openEditForm = (contact: GoogleContact) => {
    setEditingContact(contact);
    updateForm.setData({
      resource_name: contact.resource_name,
      etag: contact.etag ?? '',
      name: contact.name,
      phone: contact.phone ?? '',
      email: contact.email ?? '',
      address: contact.address ?? '',
    });
  };

  const closeEditForm = () => {
    setEditingContact(null);
    updateForm.reset();
  };

  const deleteContact = (contact: GoogleContact) => {
    if (!window.confirm(`Adakah anda pasti mahu memadam contact ${contact.name}?`)) return;

    if (editingContact?.resource_name === contact.resource_name) {
      closeEditForm();
    }

    router.delete(route('admin.contacts.google.destroy'), {
      data: { resource_name: contact.resource_name },
      preserveScroll: true,
      onSuccess: () => setSelectedResources((current) => current.filter((resourceName) => resourceName !== contact.resource_name)),
    });
  };

  const deleteSelectedContacts = () => {
    if (selectedResources.length === 0) return;
    if (!window.confirm(`Adakah anda pasti mahu memadam ${selectedResources.length} contact yang dipilih?`)) return;

    router.delete(route('admin.contacts.google.bulk-destroy'), {
      data: { resource_names: selectedResources },
      preserveScroll: true,
      onSuccess: () => setSelectedResources([]),
    });
  };

  return (
    <AdminLayout>
      <Head title="Contact" />
      <div className="space-y-6">
        <div className="admin-page-head">
          <div>
            <div className="mb-2 flex items-center gap-2 text-brand-600">
              <ContactRound className="h-5 w-5" />
              <span className="text-xs font-bold uppercase tracking-[0.18em]">Google People</span>
            </div>
            <h2 className="text-2xl font-bold text-slate-900">Contact</h2>
            <p className="admin-page-copy">Tambah contact manual atau terus daripada rekod customer.</p>
          </div>

          {connection && (
            <div className="admin-page-actions">
              <span className="admin-status bg-emerald-50 text-emerald-700">
                <CheckCircle2 className="h-3.5 w-3.5" />
                {connection.email ?? 'Google disambungkan'}
              </span>
              <Link href={route('admin.contacts.google.create')} className="admin-btn-primary">
                <Plus className="h-4 w-4" />
                Tambah Contact
              </Link>
              <button type="button" onClick={disconnect} className="admin-btn-secondary">
                <Unlink className="h-4 w-4" />
                Putuskan
              </button>
            </div>
          )}
        </div>

        {!isConfigured && (
          <div className="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-amber-900">
            <div className="flex items-start gap-3">
              <AlertCircle className="mt-0.5 h-5 w-5 shrink-0" />
              <div>
                <p className="font-semibold">Google OAuth belum dikonfigurasi</p>
                <p className="mt-1 text-sm text-amber-800">
                  Isi <code>GOOGLE_CLIENT_ID</code> dan <code>GOOGLE_CLIENT_SECRET</code>, aktifkan People API, kemudian daftar callback <code>{callbackUrl}</code> dalam Google Cloud.
                </p>
              </div>
            </div>
          </div>
        )}

        {isConfigured && !connection && (
          <div className="admin-flat-card overflow-hidden">
            <div className="grid gap-0 lg:grid-cols-[1.2fr_0.8fr]">
              <div className="p-6 sm:p-8">
                <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-50 text-brand-600">
                  <Link2 className="h-6 w-6" />
                </div>
                <h3 className="mt-5 text-xl font-bold text-slate-900">Sambungkan Google Contacts</h3>
                <p className="mt-2 max-w-xl text-sm leading-6 text-slate-600">
                  Kebenaran Google diperlukan untuk menyemak nombor sedia ada dan menyimpan contact baharu ke akaun pilihan anda.
                </p>
                <div className="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-3">
                  <p className="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500">Authorized redirect URI</p>
                  <code className="mt-1 block break-all text-xs text-slate-700">{callbackUrl}</code>
                </div>
                <a href={route('admin.contacts.google.connect')} className="admin-btn-primary mt-6">
                  <Link2 className="h-4 w-4" />
                  Sambung Akaun Google
                </a>
              </div>
              <div className="border-t border-slate-200 bg-slate-50 p-6 sm:p-8 lg:border-l lg:border-t-0">
                <p className="admin-mini-label">Perlindungan Pendua</p>
                <div className="mt-4 flex items-start gap-3">
                  <ShieldCheck className="h-5 w-5 shrink-0 text-emerald-600" />
                  <p className="text-sm leading-6 text-slate-600">
                    Sistem membandingkan nombor yang dinormalisasi dengan cache Google Contacts sebelum menyimpan.
                  </p>
                </div>
              </div>
            </div>
          </div>
        )}

        {connection && (
          <>
            {contactsError && (
              <div className="rounded-2xl border border-rose-200 bg-rose-50 p-5 text-rose-900">
                <div className="flex items-start gap-3">
                  <AlertCircle className="mt-0.5 h-5 w-5 shrink-0" />
                  <p className="text-sm">{contactsError}</p>
                </div>
              </div>
            )}

            <div className="admin-table-card">
              <div className="flex flex-col gap-3 border-b border-slate-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                <div>
                  <h3 className="font-bold text-slate-900">Senarai Contact</h3>
                   <p className="mt-1 text-sm text-slate-500">{contacts.total} {contactGroupLabel} contact dalam akaun Google yang disambungkan.</p>
                </div>
                <div className="relative w-full sm:max-w-xs">
                  <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                  <input
                    type="search"
                    value={contactSearch}
                    onChange={(event) => setContactSearch(event.target.value)}
                    className="w-full pl-10"
                    placeholder="Cari contact..."
                  />
                </div>
              </div>
              <div className="flex items-center gap-1 border-b border-slate-200 px-4 pt-2.5 sm:px-5">
                 {(['company', 'sticker', 'personal'] as ContactGroup[]).map((group) => (
                  <button
                    key={group}
                    type="button"
                    onClick={() => switchGroup(group)}
                    className={`border-b-2 px-3 py-2 text-xs font-semibold transition ${contactGroup === group ? 'border-brand-600 text-brand-700' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700'}`}
                  >
                     {group === 'company' ? 'Company' : group === 'sticker' ? 'Sticker' : 'Personal'}
                  </button>
                ))}
              </div>
              {selectedResources.length > 0 && (
                <div className="flex flex-wrap items-center justify-between gap-3 border-b border-brand-100 bg-brand-50/60 px-4 py-2.5 sm:px-5">
                  <p className="text-sm font-medium text-brand-800">{selectedResources.length} contact dipilih</p>
                  <button type="button" onClick={deleteSelectedContacts} className="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-rose-600 transition hover:bg-rose-100">
                    <Trash2 className="h-3.5 w-3.5" />
                    Padam Dipilih
                  </button>
                </div>
              )}
              <div className="admin-table-wrap">
                <table className="admin-table [&_td]:px-3 [&_td]:py-2 [&_th]:px-3 [&_th]:py-2">
                  <thead>
                    <tr>
                      <th className="w-10">
                        <input
                          type="checkbox"
                          checked={allVisibleSelected}
                          onChange={toggleVisibleSelection}
                          aria-label="Pilih semua contact di halaman ini"
                          className="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                        />
                      </th>
                      <th>
                        <button type="button" onClick={() => sortContacts('name')} className="inline-flex items-center gap-1.5 text-left hover:text-brand-600">
                          Nama
                          {sortIcon('name')}
                        </button>
                      </th>
                      <th>
                        <button type="button" onClick={() => sortContacts('phone')} className="inline-flex items-center gap-1.5 text-left hover:text-brand-600">
                          Telefon
                          {sortIcon('phone')}
                        </button>
                      </th>
                      <th>
                        <button type="button" onClick={() => sortContacts('email')} className="inline-flex items-center gap-1.5 text-left hover:text-brand-600">
                          Emel
                          {sortIcon('email')}
                        </button>
                      </th>
                      <th>
                        <button type="button" onClick={() => sortContacts('address')} className="inline-flex items-center gap-1.5 text-left hover:text-brand-600">
                          Alamat
                          {sortIcon('address')}
                        </button>
                      </th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    {contacts.data.length === 0 ? (
                      <tr>
                        <td colSpan={6} className="py-16 text-center">
                          <div className="admin-table-empty">
                            <ContactRound className="mx-auto h-12 w-12 text-slate-300" />
                             <p className="admin-table-empty-title">{contacts.total === 0 ? `Tiada ${contactGroupLabel} Contact` : 'Tiada contact dijumpai'}</p>
                            <p className="admin-table-empty-desc">
                              {contacts.total === 0 ? 'Klik "Tambah Contact" untuk mula.' : 'Cuba kata carian yang lain.'}
                            </p>
                          </div>
                        </td>
                      </tr>
                    ) : (
                      contacts.data.map((contact) => (
                        <tr key={contact.resource_name}>
                          <td className="w-10">
                            <input
                              type="checkbox"
                              checked={selectedResources.includes(contact.resource_name)}
                              onChange={() => toggleContactSelection(contact.resource_name)}
                              aria-label={`Pilih contact ${contact.name}`}
                              className="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                            />
                          </td>
                          <td className="font-medium text-slate-900">{contact.name}</td>
                          <td>{contact.phone ?? '-'}</td>
                          <td>{contact.email ?? '-'}</td>
                          <td className="max-w-[260px] truncate text-slate-500">{contact.address ?? '-'}</td>
                          <td>
                            <div className="flex items-center justify-end gap-0.5">
                              {whatsappUrl(contact.phone) && (
                                <a
                                  href={whatsappUrl(contact.phone) ?? undefined}
                                  target="_blank"
                                  rel="noreferrer"
                                  aria-label={`WhatsApp ${contact.name}`}
                                  className="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-[11px] font-medium text-emerald-600 transition hover:bg-emerald-50"
                                >
                                  <MessageCircle className="h-3.5 w-3.5" />
                                  WhatsApp
                                </a>
                              )}
                              <button
                                type="button"
                                onClick={() => openEditForm(contact)}
                                aria-label={`Kemaskini contact ${contact.name}`}
                                className="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-[11px] font-medium text-slate-600 transition hover:bg-slate-50"
                              >
                                <Pencil className="h-3.5 w-3.5" />
                                Edit
                              </button>
                              <button
                                type="button"
                                onClick={() => deleteContact(contact)}
                                aria-label={`Padam contact ${contact.name}`}
                                className="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-[11px] font-medium text-rose-600 transition hover:bg-rose-50"
                              >
                                <Trash2 className="h-3.5 w-3.5" />
                                Padam
                              </button>
                            </div>
                          </td>
                        </tr>
                      ))
                    )}
                  </tbody>
                </table>
              </div>

              {contacts.links.length > 3 && (
                <div className="flex flex-col gap-3 border-t border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                  <p className="text-sm text-slate-500">
                    Menunjukkan {contacts.from ?? 0}-{contacts.to ?? 0} daripada {contacts.total} contact
                  </p>
                  <div className="flex items-center gap-1">
                    {contacts.links.map((link) => (
                      link.url ? (
                        <Link
                          key={`${link.label}-${link.url}`}
                          href={link.url}
                          preserveState
                          onClick={() => window.scrollTo({ top: 0, behavior: 'smooth' })}
                          aria-current={link.active ? 'page' : undefined}
                          className={`rounded-lg px-3 py-1.5 text-sm font-medium transition ${link.active ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-50'}`}
                        >
                          {paginationLabel(link.label)}
                        </Link>
                      ) : (
                        <span key={`${link.label}-disabled`} className="rounded-lg px-3 py-1.5 text-sm text-slate-400">
                          {paginationLabel(link.label)}
                        </span>
                      )
                    ))}
                  </div>
                </div>
              )}
            </div>

            {editingContact && (
              <div
                className="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm"
                role="presentation"
              >
                <div
                  className="max-h-[calc(100vh-2rem)] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white shadow-2xl"
                  role="dialog"
                  aria-modal="true"
                  aria-labelledby="edit-contact-title"
                >
                  <div className="flex items-start justify-between border-b border-slate-100 p-5 sm:p-6">
                    <div>
                      <h3 id="edit-contact-title" className="font-bold text-slate-900">Kemaskini Contact</h3>
                      <p className="mt-0.5 text-sm text-slate-500">Ubah maklumat contact Google yang dipilih.</p>
                    </div>
                    <button type="button" onClick={closeEditForm} className="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600" aria-label="Tutup borang kemaskini">
                      <X className="h-5 w-5" />
                    </button>
                  </div>
                  <form
                    onSubmit={(event) => {
                      event.preventDefault();
                      updateForm.put(route('admin.contacts.google.update'), {
                        preserveScroll: true,
                        onSuccess: closeEditForm,
                      });
                    }}
                    className="grid gap-4 p-5 sm:grid-cols-2 sm:p-6"
                  >
                    <div>
                      <label htmlFor="edit-contact-name">Nama contact</label>
                      <input
                        id="edit-contact-name"
                        type="text"
                        value={updateForm.data.name}
                        onChange={(event) => updateForm.setData('name', event.target.value)}
                        className={fieldClass}
                        required
                      />
                      {updateForm.errors.name && <p className="mt-1 text-xs text-rose-600">{updateForm.errors.name}</p>}
                    </div>
                    <div>
                      <label htmlFor="edit-contact-phone">Nombor telefon</label>
                      <input
                        id="edit-contact-phone"
                        type="text"
                        inputMode="tel"
                        value={updateForm.data.phone}
                        onChange={(event) => updateForm.setData('phone', event.target.value)}
                        className={fieldClass}
                        required
                      />
                      {updateForm.errors.phone && <p className="mt-1 text-xs text-rose-600">{updateForm.errors.phone}</p>}
                    </div>
                    <div>
                      <label htmlFor="edit-contact-email">Emel (pilihan)</label>
                      <input
                        id="edit-contact-email"
                        type="email"
                        value={updateForm.data.email}
                        onChange={(event) => updateForm.setData('email', event.target.value)}
                        className={fieldClass}
                      />
                      {updateForm.errors.email && <p className="mt-1 text-xs text-rose-600">{updateForm.errors.email}</p>}
                    </div>
                    <div>
                      <label htmlFor="edit-contact-address">Alamat (pilihan)</label>
                      <textarea
                        id="edit-contact-address"
                        rows={3}
                        value={updateForm.data.address}
                        onChange={(event) => updateForm.setData('address', event.target.value)}
                        className={fieldClass}
                      />
                      {updateForm.errors.address && <p className="mt-1 text-xs text-rose-600">{updateForm.errors.address}</p>}
                    </div>
                    <div className="flex justify-end gap-2 border-t border-slate-100 pt-4 sm:col-span-2">
                      <button type="button" onClick={closeEditForm} className="admin-btn-secondary">
                        Batal
                      </button>
                      <button type="submit" disabled={updateForm.processing} className="admin-btn-primary disabled:cursor-not-allowed disabled:opacity-60">
                        {updateForm.processing ? 'Menyimpan...' : 'Simpan Perubahan'}
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            )}

          </>
        )}
      </div>
    </AdminLayout>
  );
}
