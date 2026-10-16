import { useEffect, useRef, useState } from 'react'
import { Check, ChevronDown, X } from 'lucide-react'

export default function MultiSelect({ options, value, onChange, placeholder = 'Select...' }) {
  const [open, setOpen] = useState(false)
  const ref = useRef(null)

  useEffect(() => {
    const close = (e) => {
      if (ref.current && !ref.current.contains(e.target)) setOpen(false)
    }
    document.addEventListener('mousedown', close)
    return () => document.removeEventListener('mousedown', close)
  }, [])

  const toggle = (id) =>
    onChange(value.includes(id) ? value.filter((v) => v !== id) : [...value, id])

  const selected = options.filter((o) => value.includes(o.value))

  return (
    <div className="relative" ref={ref}>
      <div
        className="field flex min-h-10 cursor-pointer flex-wrap items-center gap-1.5 pr-9"
        onClick={() => setOpen(!open)}
      >
        {selected.length === 0 && <span className="text-slate-400">{placeholder}</span>}
        {selected.map((o) => (
          <span
            key={o.value}
            className="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800"
          >
            {o.label}
            <X
              size={12}
              className="cursor-pointer"
              onClick={(e) => {
                e.stopPropagation()
                toggle(o.value)
              }}
            />
          </span>
        ))}
        <ChevronDown size={16} className="absolute right-3 top-3 text-slate-400" />
      </div>

      {open && (
        <ul className="absolute z-20 mt-1 max-h-52 w-full overflow-y-auto rounded-lg border border-slate-200 bg-white py-1 shadow-lg">
          {options.length === 0 && <li className="px-3 py-2 text-sm text-slate-500">No options</li>}
          {options.map((o) => (
            <li
              key={o.value}
              onClick={() => toggle(o.value)}
              className="flex cursor-pointer items-center justify-between px-3 py-2 text-sm hover:bg-slate-50"
            >
              <span>{o.label}</span>
              {value.includes(o.value) && <Check size={14} className="text-blue-600" />}
            </li>
          ))}
        </ul>
      )}
    </div>
  )
}
