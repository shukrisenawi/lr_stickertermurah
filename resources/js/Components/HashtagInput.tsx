import { useCallback, useEffect, useRef, useState } from 'react';
import { X, Hash } from 'lucide-react';

interface HashtagInputProps {
  value: string[];
  onChange: (tags: string[]) => void;
  searchUrl: string;
  label?: string;
  placeholder?: string;
  error?: string;
}

export default function HashtagInput({
  value,
  onChange,
  searchUrl,
  label = 'Hashtag',
  placeholder = 'Taip #cookies, #kuih...',
  error,
}: HashtagInputProps) {
  const [input, setInput] = useState('');
  const [suggestions, setSuggestions] = useState<string[]>([]);
  const [activeIndex, setActiveIndex] = useState(0);
  const [isOpen, setIsOpen] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const abortRef = useRef<AbortController | null>(null);
  const containerRef = useRef<HTMLDivElement>(null);

  const tags = value || [];

  const normalize = useCallback((raw: string): string => {
    return raw
      .replace(/^#+/, '')
      .toLowerCase()
      .replace(/[^a-z0-9_\-]/g, '')
      .trim();
  }, []);

  const addTag = (raw: string) => {
    const tag = normalize(raw);
    if (!tag) return;
    if (tags.includes(tag)) return;
    onChange([...tags, tag]);
    setInput('');
    setSuggestions([]);
    setIsOpen(false);
  };

  const removeTag = (tag: string) => {
    onChange(tags.filter((t) => t !== tag));
  };

  useEffect(() => {
    const query = normalize(input);
    if (query.length < 3) {
      setSuggestions([]);
      setIsOpen(false);
      return;
    }

    if (abortRef.current) abortRef.current.abort();
    const controller = new AbortController();
    abortRef.current = controller;

    setIsLoading(true);
    fetch(`${searchUrl}?q=${encodeURIComponent(query)}`, {
      signal: controller.signal,
      headers: { Accept: 'application/json' },
    })
      .then((res) => res.json())
      .then((data: string[]) => {
        const filtered = data.filter((tag) => !tags.includes(tag));
        setSuggestions(filtered);
        setActiveIndex(0);
        setIsOpen(filtered.length > 0);
      })
      .catch(() => {
        setSuggestions([]);
        setIsOpen(false);
      })
      .finally(() => setIsLoading(false));

    return () => controller.abort();
  }, [input, tags, searchUrl, normalize]);

  useEffect(() => {
    const handleClick = (e: MouseEvent) => {
      if (!containerRef.current?.contains(e.target as Node)) {
        setIsOpen(false);
      }
    };
    document.addEventListener('mousedown', handleClick);
    return () => document.removeEventListener('mousedown', handleClick);
  }, []);

  const handleKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      if (isOpen && suggestions[activeIndex]) {
        addTag(suggestions[activeIndex]);
      } else {
        addTag(input);
      }
      return;
    }

    if (e.key === 'Backspace' && input === '' && tags.length > 0) {
      removeTag(tags[tags.length - 1]);
      return;
    }

    if (e.key === 'ArrowDown') {
      e.preventDefault();
      setActiveIndex((prev) => (prev + 1) % suggestions.length);
      return;
    }

    if (e.key === 'ArrowUp') {
      e.preventDefault();
      setActiveIndex((prev) => (prev - 1 + suggestions.length) % suggestions.length);
      return;
    }

    if (e.key === 'Escape') {
      setIsOpen(false);
    }
  };

  return (
    <div ref={containerRef} className="space-y-1.5">
      {label && <label htmlFor="hashtag-input" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">{label}</label>}
      <div
        className={`flex min-h-[46px] flex-wrap items-center gap-2 rounded-xl border bg-white px-3 py-2 transition ${
          error ? 'border-rose-300 focus-within:ring-rose-100' : 'border-slate-200 focus-within:border-brand-300 focus-within:ring-2 focus-within:ring-brand-100'
        }`}
      >
        {tags.map((tag) => (
          <span
            key={tag}
            className="inline-flex items-center gap-1 rounded-full bg-brand-50 px-2.5 py-1 text-xs font-bold text-brand-700"
          >
            <Hash className="h-3 w-3" />
            #{tag}
            <button
              type="button"
              onClick={() => removeTag(tag)}
              className="ml-0.5 rounded-full p-0.5 hover:bg-brand-100"
              aria-label={`Buang #${tag}`}
            >
              <X className="h-3 w-3" />
            </button>
          </span>
        ))}
        <input
          id="hashtag-input"
          type="text"
          value={input}
          onChange={(e) => setInput(e.target.value)}
          onKeyDown={handleKeyDown}
          onFocus={() => {
            if (suggestions.length > 0) setIsOpen(true);
          }}
          placeholder={tags.length === 0 ? placeholder : ''}
          className="min-w-[120px] flex-1 bg-transparent text-sm text-slate-900 outline-none placeholder:text-slate-400"
        />
      </div>

      {isOpen && (
        <div className="relative z-50">
          <ul className="absolute left-0 right-0 mt-1 max-h-48 overflow-auto rounded-xl border border-slate-200 bg-white py-1 shadow-lg">
            {suggestions.map((tag, idx) => (
              <li key={tag}>
                <button
                  type="button"
                  onClick={() => addTag(tag)}
                  onMouseEnter={() => setActiveIndex(idx)}
                  className={`w-full px-4 py-2 text-left text-sm transition ${
                    idx === activeIndex ? 'bg-brand-50 text-brand-700 font-semibold' : 'text-slate-700 hover:bg-slate-50'
                  }`}
                >
                  <span className="text-slate-400">#</span>
                  {tag}
                </button>
              </li>
            ))}
          </ul>
        </div>
      )}

      {isLoading && <p className="text-[11px] text-slate-400">Mencari tag...</p>}
      {error && <p className="text-sm text-rose-600">{error}</p>}
    </div>
  );
}
