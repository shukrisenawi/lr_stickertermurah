import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, router, useForm } from '@inertiajs/react';
import {
  ArrowDownCircle,
  ArrowUpCircle,
  CalendarRange,
  CircleDollarSign,
  Landmark,
  Pencil,
  Plus,
  Save,
  Trash2,
  Wallet,
  X,
} from 'lucide-react';
import { useEffect, useState } from 'react';

interface MonthlyRecord {
  id: number;
  bank_account_id: number;
  year: number;
  month: number;
  income: number;
  expense: number;
  closing_balance: number;
  notes: string | null;
}

interface BankAccount {
  id: number;
  name: string;
  account_number: string | null;
  account_holder: string | null;
  previous_year_balance: number;
  year_starting_balance: number;
  records_count: number;
  current_balance: number;
  income: number;
  expense: number;
  records: MonthlyRecord[];
}

interface BankAccountsProps {
  banks: BankAccount[];
  year: number;
  currentYear: number;
  currentMonth: number;
  years: number[];
  totals: {
    current_balance: number;
    income: number;
    expense: number;
    bank_count: number;
  };
}

interface BankFormData {
  name: string;
  account_number: string;
  account_holder: string;
  previous_year_balance: string;
}

interface MonthlyFormData {
  bank_account_id: string;
  year: string;
  month: string;
  income: string;
  expense: string;
  notes: string;
}

const monthNames = [
  'Januari',
  'Februari',
  'Mac',
  'April',
  'Mei',
  'Jun',
  'Julai',
  'Ogos',
  'September',
  'Oktober',
  'November',
  'Disember',
];

function formatCurrency(amount: number): string {
  return new Intl.NumberFormat('ms-MY', { style: 'currency', currency: 'MYR' }).format(amount);
}

function amountValue(value: string): number {
  const amount = Number(value);

  return Number.isFinite(amount) ? amount : 0;
}

function recordForMonth(bank: BankAccount, month: number): MonthlyRecord | undefined {
  return bank.records.find((record) => record.month === month);
}

function startingBalance(bank: BankAccount | undefined, month: number, excludedRecordId?: number): number {
  if (!bank) return 0;

  const previousRecords = bank.records.filter((record) => record.id !== excludedRecordId && record.month < month);
  const previousRecord = previousRecords[previousRecords.length - 1];

  return previousRecord?.closing_balance ?? bank.year_starting_balance;
}

export default function BankAccountsIndex({ banks, year, currentYear, currentMonth, years, totals }: BankAccountsProps) {
  const [bankModalOpen, setBankModalOpen] = useState(false);
  const [monthlyModalOpen, setMonthlyModalOpen] = useState(false);
  const [editingBank, setEditingBank] = useState<BankAccount | null>(null);
  const [editingMonthlyRecord, setEditingMonthlyRecord] = useState<MonthlyRecord | null>(null);
  const bankForm = useForm<BankFormData>({
    name: '',
    account_number: '',
    account_holder: '',
    previous_year_balance: '0',
  });
  const monthlyForm = useForm<MonthlyFormData>({
    bank_account_id: banks[0] ? String(banks[0].id) : '',
    year: String(year),
    month: String(currentMonth),
    income: '0',
    expense: '0',
    notes: '',
  });
  const deleteBankForm = useForm();
  const deleteMonthlyRecordForm = useForm();

  const openCreateBank = () => {
    bankForm.reset();
    bankForm.clearErrors();
    setEditingBank(null);
    setBankModalOpen(true);
  };

  const openEditBank = (bank: BankAccount) => {
    bankForm.setData({
      name: bank.name,
      account_number: bank.account_number ?? '',
      account_holder: bank.account_holder ?? '',
      previous_year_balance: String(bank.previous_year_balance),
    });
    bankForm.clearErrors();
    setEditingBank(bank);
    setBankModalOpen(true);
  };

  const closeBankModal = () => {
    if (bankForm.processing) return;

    setBankModalOpen(false);
    setEditingBank(null);
    bankForm.reset();
    bankForm.clearErrors();
  };

  const submitBank = (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();

    const options = {
      preserveScroll: true,
      onSuccess: () => {
        setBankModalOpen(false);
        setEditingBank(null);
        bankForm.reset();
      },
    };

    if (editingBank) {
      bankForm.put(route('admin.bank-accounts.update', editingBank.id), options);
      return;
    }

    bankForm.post(route('admin.bank-accounts.store'), options);
  };

  const openCreateMonthlyRecord = (bank: BankAccount, month = currentMonth) => {
    monthlyForm.setData({
      bank_account_id: String(bank.id),
      year: String(year),
      month: String(month),
      income: '0',
      expense: '0',
      notes: '',
    });
    monthlyForm.clearErrors();
    setEditingMonthlyRecord(null);
    setMonthlyModalOpen(true);
  };

  const openEditMonthlyRecord = (record: MonthlyRecord) => {
    monthlyForm.setData({
      bank_account_id: String(record.bank_account_id),
      year: String(record.year),
      month: String(record.month),
      income: String(record.income),
      expense: String(record.expense),
      notes: record.notes ?? '',
    });
    monthlyForm.clearErrors();
    setEditingMonthlyRecord(record);
    setMonthlyModalOpen(true);
  };

  const closeMonthlyModal = () => {
    if (monthlyForm.processing) return;

    setMonthlyModalOpen(false);
    setEditingMonthlyRecord(null);
    monthlyForm.setData({
      bank_account_id: banks[0] ? String(banks[0].id) : '',
      year: String(year),
      month: String(currentMonth),
      income: '0',
      expense: '0',
      notes: '',
    });
    monthlyForm.clearErrors();
  };

  const submitMonthlyRecord = (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();

    const options = {
      preserveScroll: true,
      onSuccess: () => {
        setMonthlyModalOpen(false);
        setEditingMonthlyRecord(null);
        monthlyForm.reset();
      },
    };

    if (editingMonthlyRecord) {
      monthlyForm.put(route('admin.bank-accounts.records.update', editingMonthlyRecord.id), options);
      return;
    }

    monthlyForm.post(route('admin.bank-accounts.records.store'), options);
  };

  const handleYearChange = (selectedYear: string) => {
    router.get(route('admin.bank-accounts.index', { year: selectedYear }), {}, {
      preserveScroll: true,
      replace: true,
    });
  };

  const handleDeleteBank = (bank: BankAccount) => {
    if (bank.records_count > 0) return;
    if (!window.confirm(`Padam akaun bank "${bank.name}"?`)) return;

    deleteBankForm.delete(route('admin.bank-accounts.destroy', bank.id), { preserveScroll: true });
  };

  const handleDeleteMonthlyRecord = (record: MonthlyRecord, bankName: string) => {
    if (!window.confirm(`Padam rekod ${monthNames[record.month - 1]} ${record.year} untuk ${bankName}?`)) return;

    deleteMonthlyRecordForm.delete(route('admin.bank-accounts.records.destroy', record.id), { preserveScroll: true });
  };

  useEffect(() => {
    if (!bankModalOpen && !monthlyModalOpen) return;

    const previousOverflow = document.body.style.overflow;
    document.body.style.overflow = 'hidden';
    const closeOnEscape = (event: KeyboardEvent) => {
      if (event.key !== 'Escape') return;
      if (bankModalOpen && !bankForm.processing) {
        setBankModalOpen(false);
        setEditingBank(null);
      }
      if (monthlyModalOpen && !monthlyForm.processing) {
        setMonthlyModalOpen(false);
        setEditingMonthlyRecord(null);
      }
    };

    window.addEventListener('keydown', closeOnEscape);

    return () => {
      document.body.style.overflow = previousOverflow;
      window.removeEventListener('keydown', closeOnEscape);
    };
  }, [bankModalOpen, monthlyModalOpen, bankForm.processing, monthlyForm.processing]);

  const selectedMonthlyBank = banks.find((bank) => String(bank.id) === monthlyForm.data.bank_account_id);
  const monthlyStartingBalance = startingBalance(
    selectedMonthlyBank,
    Number(monthlyForm.data.month),
    editingMonthlyRecord?.id,
  );
  const monthlyClosingBalance = monthlyStartingBalance
    + amountValue(monthlyForm.data.income)
    - amountValue(monthlyForm.data.expense);

  return (
    <AdminLayout>
      <Head title={`Akaun Bank ${year}`} />
      <div className="space-y-6">
        <div className="admin-page-head">
          <div>
            <div className="mb-2 flex items-center gap-2 text-brand-600">
              <Landmark className="h-5 w-5" />
              <span className="text-xs font-bold uppercase tracking-[0.18em]">Pengurusan Kewangan</span>
            </div>
            <h2 className="text-2xl font-bold text-slate-900">Akaun Bank</h2>
            <p className="admin-page-copy">Rekod duit masuk, duit keluar dan baki akhir bagi setiap bank.</p>
          </div>
          <div className="admin-page-actions">
            {banks.length > 0 && (
              <button type="button" onClick={() => openCreateMonthlyRecord(banks[0])} className="admin-btn-secondary">
                <CircleDollarSign className="h-4 w-4" />
                Tambah Rekod Bulanan
              </button>
            )}
            <button type="button" onClick={openCreateBank} className="admin-btn-primary">
              <Plus className="h-4 w-4" />
              Tambah Bank
            </button>
          </div>
        </div>

        <div className="admin-toolbar-card">
          <div className="flex items-center gap-3">
            <div className="admin-icon-badge">
              <CalendarRange className="h-4 w-4" />
            </div>
            <div>
              <p className="admin-mini-label">Tahun paparan</p>
              <p className="text-sm font-semibold text-slate-900">Lihat rekod bulanan mengikut tahun</p>
            </div>
          </div>
          <div className="flex flex-wrap items-center gap-3">
            <label htmlFor="bank-record-year" className="sr-only">Tahun rekod bank</label>
            <select
              id="bank-record-year"
              value={year}
              onChange={(event) => handleYearChange(event.target.value)}
              className="!w-auto min-w-32"
            >
              {years.map((availableYear) => <option key={availableYear} value={availableYear}>{availableYear}</option>)}
            </select>
            <span className="admin-soft-badge">
              {year === currentYear ? 'Tahun semasa' : `Tahun ${year}`}
            </span>
          </div>
        </div>

        <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
          <div className="admin-stat-card flex items-center gap-4">
            <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
              <Wallet className="h-5 w-5" />
            </div>
            <div>
              <p className="admin-mini-label">Baki semasa</p>
              <p className="mt-1 text-xl font-bold tracking-tight text-slate-900">{formatCurrency(totals.current_balance)}</p>
            </div>
          </div>
          <div className="admin-stat-card flex items-center gap-4">
            <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-brand-50 text-brand-600">
              <ArrowUpCircle className="h-5 w-5" />
            </div>
            <div>
              <p className="admin-mini-label">Duit masuk {year}</p>
              <p className="mt-1 text-xl font-bold tracking-tight text-slate-900">{formatCurrency(totals.income)}</p>
            </div>
          </div>
          <div className="admin-stat-card flex items-center gap-4">
            <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-rose-50 text-rose-600">
              <ArrowDownCircle className="h-5 w-5" />
            </div>
            <div>
              <p className="admin-mini-label">Duit keluar {year}</p>
              <p className="mt-1 text-xl font-bold tracking-tight text-slate-900">{formatCurrency(totals.expense)}</p>
            </div>
          </div>
          <div className="admin-stat-card flex items-center gap-4">
            <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-violet-50 text-violet-600">
              <Landmark className="h-5 w-5" />
            </div>
            <div>
              <p className="admin-mini-label">Jumlah bank</p>
              <p className="mt-1 text-xl font-bold tracking-tight text-slate-900">{totals.bank_count}</p>
            </div>
          </div>
        </div>

        {banks.length === 0 ? (
          <div className="admin-flat-card px-5 py-16 text-center sm:px-6">
            <Landmark className="mx-auto h-12 w-12 text-slate-300" />
            <p className="mt-3 text-lg font-semibold text-slate-900">Belum ada akaun bank</p>
            <p className="mx-auto mt-1 max-w-md text-sm text-slate-500">Tambah akaun bank pertama untuk mula merekod duit masuk, duit keluar dan baki bulanan.</p>
            <button type="button" onClick={openCreateBank} className="admin-btn-primary mt-5">
              <Plus className="h-4 w-4" />
              Tambah Bank Pertama
            </button>
          </div>
        ) : (
          <div className="space-y-5">
            {banks.map((bank) => (
              <section key={bank.id} className="admin-table-card">
                <div className="flex flex-col gap-4 border-b border-slate-200 px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
                  <div className="flex min-w-0 items-start gap-3">
                    <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-brand-50 text-brand-600">
                      <Landmark className="h-5 w-5" />
                    </div>
                    <div className="min-w-0">
                      <h3 className="truncate font-bold text-slate-900">{bank.name}</h3>
                      <p className="mt-0.5 text-xs text-slate-500">
                        {[bank.account_number ? `Akaun ${bank.account_number}` : '', bank.account_holder ?? ''].filter(Boolean).join(' · ') || 'Maklumat akaun belum diisi'}
                      </p>
                    </div>
                  </div>
                  <div className="flex flex-wrap items-center gap-2">
                    <div className="mr-1 rounded-xl bg-slate-50 px-3 py-2 text-right">
                      <p className="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400">Baki terakhir</p>
                      <p className="mt-0.5 text-sm font-bold text-slate-900">{formatCurrency(bank.current_balance)}</p>
                    </div>
                    <button type="button" onClick={() => openCreateMonthlyRecord(bank)} className="admin-btn-secondary !px-3 !py-2" aria-label={`Tambah rekod bulanan untuk ${bank.name}`}>
                      <Plus className="h-4 w-4" />
                      <span className="hidden sm:inline">Tambah Bulan</span>
                    </button>
                    <button type="button" onClick={() => openEditBank(bank)} className="rounded-xl border border-slate-200 bg-white p-2.5 text-slate-600 transition hover:border-brand-200 hover:bg-brand-50 hover:text-brand-600" aria-label={`Kemaskini ${bank.name}`}>
                      <Pencil className="h-4 w-4" />
                    </button>
                    <button
                      type="button"
                      onClick={() => handleDeleteBank(bank)}
                      disabled={bank.records_count > 0}
                      className="rounded-xl border border-slate-200 bg-white p-2.5 text-rose-500 transition hover:border-rose-200 hover:bg-rose-50 disabled:cursor-not-allowed disabled:opacity-30"
                      aria-label={`Padam ${bank.name}`}
                      title={bank.records_count > 0 ? 'Bank yang mempunyai rekod tidak boleh dipadam' : 'Padam bank'}
                    >
                      <Trash2 className="h-4 w-4" />
                    </button>
                  </div>
                </div>

                <div className="grid gap-3 border-b border-slate-100 bg-slate-50/70 px-5 py-4 sm:grid-cols-4">
                  <div>
                    <p className="admin-mini-label">Baki tahun lepas</p>
                    <p className="mt-1 text-sm font-bold text-slate-900">{formatCurrency(bank.previous_year_balance)}</p>
                  </div>
                  <div>
                    <p className="admin-mini-label">Duit masuk {year}</p>
                    <p className="mt-1 text-sm font-bold text-emerald-700">{formatCurrency(bank.income)}</p>
                  </div>
                  <div>
                    <p className="admin-mini-label">Duit keluar {year}</p>
                    <p className="mt-1 text-sm font-bold text-rose-600">{formatCurrency(bank.expense)}</p>
                  </div>
                  <div>
                    <p className="admin-mini-label">Rekod tahun ini</p>
                    <p className="mt-1 text-sm font-bold text-slate-900">{bank.records.length} daripada 12 bulan</p>
                  </div>
                </div>

                <div className="admin-table-wrap">
                  <table className="admin-table min-w-[680px]">
                    <thead>
                      <tr>
                        <th>Bulan</th>
                        <th>Duit Masuk</th>
                        <th>Duit Keluar</th>
                        <th>Baki Akhir</th>
                        <th className="text-right">Tindakan</th>
                      </tr>
                    </thead>
                    <tbody>
                      {monthNames.map((monthName, monthIndex) => {
                        const month = monthIndex + 1;
                        const record = recordForMonth(bank, month);

                        return (
                          <tr key={`${bank.id}-${month}`}>
                            <td className="font-semibold text-slate-900">
                              {monthName}
                              {!record && <span className="ml-2 text-[10px] font-medium uppercase tracking-wide text-slate-400">Belum direkod</span>}
                            </td>
                            <td className="whitespace-nowrap font-semibold text-emerald-700">{record ? formatCurrency(record.income) : '-'}</td>
                            <td className="whitespace-nowrap font-semibold text-rose-600">{record ? formatCurrency(record.expense) : '-'}</td>
                            <td className="whitespace-nowrap font-bold text-slate-900">{record ? formatCurrency(record.closing_balance) : '-'}</td>
                            <td>
                              <div className="flex items-center justify-end gap-1">
                                <button
                                  type="button"
                                  onClick={() => record ? openEditMonthlyRecord(record) : openCreateMonthlyRecord(bank, month)}
                                  className="rounded-lg p-2 text-slate-600 transition hover:bg-brand-50 hover:text-brand-600"
                                  aria-label={record ? `Kemaskini rekod ${monthName} ${year} untuk ${bank.name}` : `Tambah rekod ${monthName} ${year} untuk ${bank.name}`}
                                >
                                  {record ? <Pencil className="h-4 w-4" /> : <Plus className="h-4 w-4" />}
                                </button>
                                {record && (
                                  <button
                                    type="button"
                                    onClick={() => handleDeleteMonthlyRecord(record, bank.name)}
                                    className="rounded-lg p-2 text-rose-500 transition hover:bg-rose-50"
                                    aria-label={`Padam rekod ${monthName} ${year} untuk ${bank.name}`}
                                  >
                                    <Trash2 className="h-4 w-4" />
                                  </button>
                                )}
                              </div>
                            </td>
                          </tr>
                        );
                      })}
                    </tbody>
                  </table>
                </div>
              </section>
            ))}
          </div>
        )}
      </div>

      {bankModalOpen && (
        <div
          className="fixed inset-0 z-[60] flex items-center justify-center bg-slate-950/65 p-4 backdrop-blur-sm"
          role="presentation"
        >
          <div className="w-full max-w-lg rounded-3xl border border-white/70 bg-slate-50 p-6 shadow-2xl shadow-slate-950/25" role="dialog" aria-modal="true" aria-labelledby="bank-modal-title">
            <div className="mb-5 flex items-start justify-between gap-4">
              <div>
                <p className="text-[10px] font-bold uppercase tracking-[0.18em] text-brand-600">Akaun Bank</p>
                <h3 id="bank-modal-title" className="mt-1 text-lg font-bold text-slate-900">{editingBank ? 'Kemaskini Akaun Bank' : 'Tambah Akaun Bank'}</h3>
                <p className="mt-1 text-sm text-slate-500">Simpan lebih daripada satu akaun bank untuk rekod kewangan berasingan.</p>
              </div>
              <button type="button" onClick={closeBankModal} disabled={bankForm.processing} aria-label="Tutup borang bank" className="rounded-full p-1.5 text-slate-400 transition hover:bg-white hover:text-slate-700 disabled:opacity-50">
                <X className="h-5 w-5" />
              </button>
            </div>
            <form onSubmit={submitBank} className="space-y-4">
              <div>
                <label htmlFor="bank-name">Nama bank</label>
                <input id="bank-name" type="text" value={bankForm.data.name} onChange={(event) => bankForm.setData('name', event.target.value)} className="mt-1.5" placeholder="Contoh: Maybank" />
                {bankForm.errors.name && <p className="mt-1 text-xs text-rose-600">{bankForm.errors.name}</p>}
              </div>
              <div>
                <label htmlFor="bank-account-number">No. akaun (pilihan)</label>
                <input id="bank-account-number" type="text" value={bankForm.data.account_number} onChange={(event) => bankForm.setData('account_number', event.target.value)} className="mt-1.5" placeholder="Contoh: 5142 0099 1234" />
                {bankForm.errors.account_number && <p className="mt-1 text-xs text-rose-600">{bankForm.errors.account_number}</p>}
              </div>
              <div>
                <label htmlFor="bank-account-holder">Nama pemilik akaun (pilihan)</label>
                <input id="bank-account-holder" type="text" value={bankForm.data.account_holder} onChange={(event) => bankForm.setData('account_holder', event.target.value)} className="mt-1.5" placeholder="Contoh: SH Best Creative Design" />
                {bankForm.errors.account_holder && <p className="mt-1 text-xs text-rose-600">{bankForm.errors.account_holder}</p>}
              </div>
              <div>
                <label htmlFor="bank-previous-year-balance">Baki tahun lepas (RM)</label>
                <input id="bank-previous-year-balance" type="number" min="0" step="0.01" value={bankForm.data.previous_year_balance} onChange={(event) => bankForm.setData('previous_year_balance', event.target.value)} className="mt-1.5" placeholder="0.00" />
                {bankForm.errors.previous_year_balance && <p className="mt-1 text-xs text-rose-600">{bankForm.errors.previous_year_balance}</p>}
              </div>
              <div className="flex justify-end gap-2 pt-2">
                <button type="button" onClick={closeBankModal} disabled={bankForm.processing} className="admin-btn-secondary">Batal</button>
                <button type="submit" disabled={bankForm.processing} className="admin-btn-primary disabled:cursor-not-allowed disabled:opacity-60">
                  <Save className="h-4 w-4" />
                  {bankForm.processing ? 'Menyimpan...' : editingBank ? 'Kemaskini' : 'Simpan Bank'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {monthlyModalOpen && (
        <div
          className="fixed inset-0 z-[60] flex items-center justify-center bg-slate-950/65 p-4 backdrop-blur-sm"
          role="presentation"
        >
          <div className="max-h-[calc(100dvh-2rem)] w-full max-w-2xl overflow-y-auto rounded-3xl border border-white/70 bg-slate-50 p-6 shadow-2xl shadow-slate-950/25" role="dialog" aria-modal="true" aria-labelledby="monthly-record-modal-title">
            <div className="mb-5 flex items-start justify-between gap-4">
              <div>
                <p className="text-[10px] font-bold uppercase tracking-[0.18em] text-brand-600">Rekod Kewangan Bulanan</p>
                <h3 id="monthly-record-modal-title" className="mt-1 text-lg font-bold text-slate-900">{editingMonthlyRecord ? 'Kemaskini Rekod Bulanan' : 'Tambah Rekod Bulanan'}</h3>
                <p className="mt-1 text-sm text-slate-500">Baki akhir dikira automatik daripada baki tahun lepas, baki bulan sebelumnya, duit masuk dan duit keluar.</p>
              </div>
              <button type="button" onClick={closeMonthlyModal} disabled={monthlyForm.processing} aria-label="Tutup borang rekod bulanan" className="rounded-full p-1.5 text-slate-400 transition hover:bg-white hover:text-slate-700 disabled:opacity-50">
                <X className="h-5 w-5" />
              </button>
            </div>
            <form onSubmit={submitMonthlyRecord} className="space-y-4">
              <div className="grid gap-4 sm:grid-cols-3">
                <div className="sm:col-span-3">
                  <label htmlFor="monthly-bank-account">Bank</label>
                  <select id="monthly-bank-account" value={monthlyForm.data.bank_account_id} onChange={(event) => monthlyForm.setData('bank_account_id', event.target.value)} className="mt-1.5">
                    <option value="">Pilih bank</option>
                    {banks.map((bank) => <option key={bank.id} value={bank.id}>{bank.name}</option>)}
                  </select>
                  {monthlyForm.errors.bank_account_id && <p className="mt-1 text-xs text-rose-600">{monthlyForm.errors.bank_account_id}</p>}
                </div>
                <div>
                  <label htmlFor="monthly-record-year">Tahun</label>
                  <input id="monthly-record-year" type="number" min="2000" max="2100" value={monthlyForm.data.year} onChange={(event) => monthlyForm.setData('year', event.target.value)} className="mt-1.5" />
                  {monthlyForm.errors.year && <p className="mt-1 text-xs text-rose-600">{monthlyForm.errors.year}</p>}
                </div>
                <div className="sm:col-span-2">
                  <label htmlFor="monthly-record-month">Bulan</label>
                  <select id="monthly-record-month" value={monthlyForm.data.month} onChange={(event) => monthlyForm.setData('month', event.target.value)} className="mt-1.5">
                    {monthNames.map((monthName, index) => <option key={monthName} value={index + 1}>{monthName}</option>)}
                  </select>
                  {monthlyForm.errors.month && <p className="mt-1 text-xs text-rose-600">{monthlyForm.errors.month}</p>}
                </div>
              </div>

              <div className="grid gap-4 sm:grid-cols-2">
                <div>
                  <label htmlFor="monthly-income">Duit masuk (RM)</label>
                  <input id="monthly-income" type="number" min="0" step="0.01" value={monthlyForm.data.income} onChange={(event) => monthlyForm.setData('income', event.target.value)} className="mt-1.5" placeholder="0.00" />
                  {monthlyForm.errors.income && <p className="mt-1 text-xs text-rose-600">{monthlyForm.errors.income}</p>}
                </div>
                <div>
                  <label htmlFor="monthly-expense">Duit keluar (RM)</label>
                  <input id="monthly-expense" type="number" min="0" step="0.01" value={monthlyForm.data.expense} onChange={(event) => monthlyForm.setData('expense', event.target.value)} className="mt-1.5" placeholder="0.00" />
                  {monthlyForm.errors.expense && <p className="mt-1 text-xs text-rose-600">{monthlyForm.errors.expense}</p>}
                </div>
              </div>

              <div className="flex items-center justify-between gap-4 rounded-2xl border border-brand-100 bg-brand-50 px-4 py-3">
                <div>
                  <p className="text-[10px] font-bold uppercase tracking-[0.16em] text-brand-700">Baki akhir</p>
                  <p className="mt-0.5 text-xs text-brand-700/80">Akan dikira selepas simpan</p>
                </div>
                <p className="text-lg font-bold text-brand-700">{formatCurrency(monthlyClosingBalance)}</p>
              </div>

              <div>
                <label htmlFor="monthly-record-notes">Nota (pilihan)</label>
                <textarea id="monthly-record-notes" rows={3} value={monthlyForm.data.notes} onChange={(event) => monthlyForm.setData('notes', event.target.value)} className="mt-1.5" placeholder="Contoh: Baki disemak berdasarkan penyata bank bulan ini" />
                {monthlyForm.errors.notes && <p className="mt-1 text-xs text-rose-600">{monthlyForm.errors.notes}</p>}
              </div>

              <div className="flex justify-end gap-2 pt-2">
                <button type="button" onClick={closeMonthlyModal} disabled={monthlyForm.processing} className="admin-btn-secondary">Batal</button>
                <button type="submit" disabled={monthlyForm.processing || banks.length === 0} className="admin-btn-primary disabled:cursor-not-allowed disabled:opacity-60">
                  <Save className="h-4 w-4" />
                  {monthlyForm.processing ? 'Menyimpan...' : editingMonthlyRecord ? 'Kemaskini Rekod' : 'Simpan Rekod'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </AdminLayout>
  );
}
