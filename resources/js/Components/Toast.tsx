import { useEffect, useState, createContext, useContext, useCallback } from 'react';
import { X, CheckCircle, AlertCircle, Info, AlertTriangle } from 'lucide-react';
import { cn } from '@/lib/utils';

interface Toast {
  id: string;
  message: string;
  type: 'success' | 'error' | 'info' | 'warning';
  duration?: number;
}

interface ToastContextValue {
  addToast: (toast: Omit<Toast, 'id'>) => void;
  removeToast: (id: string) => void;
}

const ToastContext = createContext<ToastContextValue | null>(null);

export function useToast() {
  const ctx = useContext(ToastContext);
  if (!ctx) throw new Error('useToast must be used within ToastProvider');
  return ctx;
}

function ToastItem({ toast, onRemove }: { toast: Toast; onRemove: (id: string) => void }) {
  const [visible, setVisible] = useState(false);

  useEffect(() => {
    const enter = requestAnimationFrame(() => setVisible(true));
    const timer = setTimeout(() => {
      setVisible(false);
      setTimeout(() => onRemove(toast.id), 300);
    }, toast.duration ?? 5000);

    return () => {
      cancelAnimationFrame(enter);
      clearTimeout(timer);
    };
  }, [toast, onRemove]);

  const icons = {
    success: <CheckCircle className="h-5 w-5 text-emerald-500" />,
    error: <AlertCircle className="h-5 w-5 text-rose-500" />,
    info: <Info className="h-5 w-5 text-sky-500" />,
    warning: <AlertTriangle className="h-5 w-5 text-amber-500" />,
  };

  const borders = {
    success: 'border-emerald-200 bg-emerald-50',
    error: 'border-rose-200 bg-rose-50',
    info: 'border-sky-200 bg-sky-50',
    warning: 'border-amber-200 bg-amber-50',
  };

  return (
    <div
      className={cn(
        'flex items-start gap-3 rounded-xl border p-4 shadow-lg transition-all duration-300 max-w-sm',
        borders[toast.type],
        visible ? 'translate-x-0 opacity-100' : 'translate-x-full opacity-0'
      )}
      role="alert"
    >
      <div className="shrink-0">{icons[toast.type]}</div>
      <p className="flex-1 text-sm font-medium text-slate-800">{toast.message}</p>
      <button
        type="button"
        onClick={() => {
          setVisible(false);
          setTimeout(() => onRemove(toast.id), 300);
        }}
        className="shrink-0 rounded-lg p-1 text-slate-400 hover:bg-slate-200/50 hover:text-slate-600 transition"
        aria-label="Tutup"
      >
        <X className="h-4 w-4" />
      </button>
    </div>
  );
}

export function ToastProvider({ children }: { children: React.ReactNode }) {
  const [toasts, setToasts] = useState<Toast[]>([]);

  const addToast = useCallback((toast: Omit<Toast, 'id'>) => {
    const id = Math.random().toString(36).slice(2, 9);
    setToasts((prev) => [...prev, { ...toast, id }]);
  }, []);

  const removeToast = useCallback((id: string) => {
    setToasts((prev) => prev.filter((t) => t.id !== id));
  }, []);

  return (
    <ToastContext.Provider value={{ addToast, removeToast }}>
      {children}
      <div className="fixed bottom-4 right-4 z-[100] flex flex-col gap-3">
        {toasts.map((toast) => (
          <ToastItem key={toast.id} toast={toast} onRemove={removeToast} />
        ))}
      </div>
    </ToastContext.Provider>
  );
}
