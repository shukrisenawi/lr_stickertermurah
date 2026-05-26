import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';
import { useState } from 'react';
import {
  AlertCircle,
  Plus,
  MapPin,
  Contact,
  Search,
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

interface ExtractProps {
  rawText: string;
  contacts: ExtractedContact[];
  swalError?: string | null;
}

export default function Extract({ rawText, contacts, swalError }: ExtractProps) {
  const {
    data: extractData,
    setData: setExtractData,
    post: postExtract,
    processing: extracting,
  } = useForm({ raw_text: rawText });

  const { post: postAddAddress, processing: addingAddress } = useForm();
  const { post: postAddUser, processing: addingUser } = useForm();

  const [expanded, setExpanded] = useState<Record<number, boolean>>({});

  const handleExtract = (e: React.FormEvent) => {
    e.preventDefault();
    postExtract(route('admin.contacts.extract.run'));
  };

  const toggleExpand = (index: number) => {
    setExpanded((prev) => ({ ...prev, [index]: !prev[index] }));
  };

  return (
    <AdminLayout>
      <Head title="Ekstrak Contact" />
      <div className="space-y-6">
        <div className="admin-page-head">
          <div>
            <h2 className="text-2xl font-bold text-slate-900">Ekstrak Contact</h2>
            <p className="admin-page-copy">
              Tampal teks untuk mengekstrak maklumat contact secara automatik.
            </p>
          </div>
        </div>

        {swalError && (
          <div className="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
            <div className="flex items-start gap-2">
              <AlertCircle className="h-5 w-5 shrink-0" />
              <p className="font-medium">{swalError}</p>
            </div>
          </div>
        )}

        <form onSubmit={handleExtract} className="admin-flat-card p-6 space-y-4">
          <label htmlFor="raw_text" className="block text-xs font-semibold uppercase tracking-wider text-slate-500">
            Teks Sumber
          </label>
          <textarea
            id="raw_text"
            value={extractData.raw_text}
            onChange={(e) => setExtractData('raw_text', e.target.value)}
            rows={8}
            placeholder="Tampal teks yang mengandungi nama, no telefon, dan alamat..."
            className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
          />
          <div className="flex items-center justify-between">
            <p className="text-xs text-slate-500">
              Format: NAMA | NO TELEFON | ALAMAT
            </p>
            <button
              type="submit"
              disabled={extracting}
              className="admin-btn-primary text-sm"
            >
              <Search className="h-4 w-4" />
              {extracting ? 'Mengekstrak...' : 'Ekstrak'}
            </button>
          </div>
        </form>

        {contacts.length > 0 && (
          <div className="space-y-4">
            <h3 className="text-lg font-bold text-slate-900">
              Hasil Ekstrak ({contacts.length} contact)
            </h3>
            {contacts.map((contact, idx) => (
              <div key={contact.name + contact.phone + contact.address} className="admin-flat-card overflow-hidden">
                <div className="p-4 sm:p-6">
                  <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div className="space-y-1">
                      <p className="text-base font-semibold text-slate-900">{contact.name}</p>
                      <div className="flex items-center gap-1.5 text-sm text-slate-600">
                        <Contact className="h-3.5 w-3.5 text-slate-400" />
                        {contact.phone}
                      </div>
                      <div className="flex items-center gap-1.5 text-sm text-slate-600">
                        <MapPin className="h-3.5 w-3.5 text-slate-400" />
                        {contact.address}
                      </div>
                      {contact.postcode !== '-' && (
                        <p className="text-xs text-slate-500">Poskod: {contact.postcode}</p>
                      )}
                    </div>
                    <div className="flex flex-wrap gap-2">
                      <form
                        onSubmit={(e) => {
                          e.preventDefault();
                          postAddUser(route('admin.contacts.extract.add-user'), {
                            data: { name: contact.name, phone: contact.phone, address: contact.address, postcode: contact.postcode },
                          } as any);
                        }}
                      >
                        <button
                          type="submit"
                          disabled={addingUser}
                          className="admin-btn-secondary text-xs"
                        >
                          <Plus className="h-3 w-3" />
                          Pengguna Baru
                        </button>
                      </form>
                    </div>
                  </div>

                  {contact.suggestions.length > 0 && (
                    <div className="mt-4">
                      <button
                        type="button"
                        onClick={() => toggleExpand(idx)}
                        className="text-xs font-medium text-brand-600 hover:text-brand-700"
                      >
                        {expanded[idx] ? 'Sembunyi' : 'Tunjuk'} padanan pengguna ({contact.suggestions.length})
                      </button>
                      {expanded[idx] && (
                        <div className="mt-3 space-y-2">
                          {contact.suggestions.map((s) => (
                            <form
                              key={s.id}
                              onSubmit={(e) => {
                                e.preventDefault();
                                postAddAddress(route('admin.contacts.extract.add-address'), {
                                  data: { user_id: s.id, name: contact.name, phone: contact.phone, address: contact.address, postcode: contact.postcode },
                                } as any);
                              }}
                              className="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 p-3"
                            >
                              <div className="text-sm">
                                <p className="font-medium text-slate-900">{s.name}</p>
                                <p className="text-xs text-slate-500">{s.email} — {s.latest_address}</p>
                              </div>
                              <button
                                type="submit"
                                disabled={addingAddress}
                                className="admin-btn-primary text-xs"
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
            ))}
          </div>
        )}
      </div>
    </AdminLayout>
  );
}
