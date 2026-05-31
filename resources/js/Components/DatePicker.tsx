import { forwardRef } from 'react';
import RDP from 'react-datepicker';
import 'react-datepicker/dist/react-datepicker.css';
import { Calendar } from 'lucide-react';
import { maskDate } from '@/lib/utils';

interface DatePickerProps {
  value: string;
  onChange: (date: string) => void;
  placeholder?: string;
}

interface InputProps {
  value?: string;
  onClick?: () => void;
  onChange?: (e: React.ChangeEvent<HTMLInputElement>) => void;
  placeholder?: string;
}

const DateInput = forwardRef<HTMLInputElement, InputProps>(
  ({ value, onClick, onChange, placeholder }, ref) => {
    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
      e.target.value = maskDate(e.target.value);
      onChange?.(e);
    };

    return (
      <div className="relative">
        <input
          ref={ref}
          type="text"
          value={value || ''}
          onChange={handleChange}
          onClick={onClick}
          placeholder={placeholder}
          className="w-full rounded-xl border border-slate-200 bg-white pl-10 pr-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
        />
        <Calendar className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
      </div>
    );
  }
);

export default function DatePicker({ value, onChange, placeholder = 'dd-mm-YYYY' }: DatePickerProps) {
  const selected = value ? parseDate(value) : null;

  function parseDate(str: string): Date | null {
    const [d, m, y] = str.split('-').map(Number);
    if (d && m && y) return new Date(y, m - 1, d);
    return null;
  }

  function formatDate(date: Date): string {
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    return `${day}-${month}-${year}`;
  }

  return (
    <RDP
      selected={selected}
      onChange={(date: Date | null) => onChange(date ? formatDate(date) : '')}
      dateFormat="dd-MM-yyyy"
      customInput={<DateInput placeholder={placeholder} />}
      popperClassName="!z-50"
      wrapperClassName="w-full"
    />
  );
}
