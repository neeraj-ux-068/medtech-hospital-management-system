import type { ComponentProps, ReactNode } from 'react'
import { Search } from 'lucide-react'

export function SearchField({ label, className = '', ...props }: Omit<ComponentProps<'input'>, 'type'> & { label: string }) {
  return <label className={`search-field ${className}`}><span className="sr-only">{label}</span>
    <Search size={17} aria-hidden="true" /><input type="search" {...props} />
  </label>
}

export function SelectField({ label, children, ...props }: ComponentProps<'select'> & { label: string; children: ReactNode }) {
  return <label className="select-field">{label}<select {...props}>{children}</select></label>
}
