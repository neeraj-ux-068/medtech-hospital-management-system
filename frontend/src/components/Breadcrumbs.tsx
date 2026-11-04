import { Fragment } from 'react'
import { Link } from 'react-router-dom'
import { ChevronRight } from 'lucide-react'

export type Breadcrumb = { label: string; to?: string }

export default function Breadcrumbs({ items }: { items: Breadcrumb[] }) {
  return <nav className="breadcrumb" aria-label="Breadcrumb">
    {items.map((item, index) => <Fragment key={`${item.label}-${index}`}>
      {index > 0 && <ChevronRight size={15} aria-hidden="true" />}
      {index === items.length - 1 ? <strong aria-current="page">{item.label}</strong> : item.to ? <Link to={item.to}>{item.label}</Link> : <span>{item.label}</span>}
    </Fragment>)}
  </nav>
}
