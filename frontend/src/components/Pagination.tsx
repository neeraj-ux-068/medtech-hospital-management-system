import { ChevronLeft, ChevronRight } from 'lucide-react'
import { Button } from './ui'

export default function Pagination({ page, pageSize, total, onChange, label = 'Directory pages' }: {
  page: number; pageSize: number; total: number; onChange: (page: number) => void; label?: string
}) {
  const pages = Math.max(1, Math.ceil(total / pageSize))
  const current = Math.max(1, Math.min(page, pages))
  const first = Math.max(1, Math.min(current - 2, pages - 4))
  const visible = Array.from({ length: Math.min(5, pages) }, (_, index) => first + index)
  return <nav className="patient-pagination" aria-label={label}>
    <span>Showing {total ? (current - 1) * pageSize + 1 : 0}–{Math.min(current * pageSize, total)} of {total}</span>
    <div>
      <Button disabled={current === 1} onClick={() => onChange(current - 1)} aria-label="Previous page"><ChevronLeft size={16} /></Button>
      {first > 1 && <Button onClick={() => onChange(1)} aria-label="First page">1</Button>}
      {first > 2 && <span aria-hidden="true">…</span>}
      {visible.map(number => <Button key={number} variant={number === current ? 'primary' : 'secondary'}
        aria-current={number === current ? 'page' : undefined} aria-label={`Page ${number}`} onClick={() => onChange(number)}>{number}</Button>)}
      {first + visible.length < pages && <span aria-hidden="true">…</span>}
      {first + visible.length <= pages && <Button onClick={() => onChange(pages)} aria-label="Last page">{pages}</Button>}
      <Button disabled={current === pages} onClick={() => onChange(current + 1)} aria-label="Next page"><ChevronRight size={16} /></Button>
    </div>
  </nav>
}
