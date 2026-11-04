import { ChevronLeft, LayoutDashboard } from 'lucide-react'
import { LinkButton, PageFooter } from './ui'

export default function PlaceholderPage({ title, description = 'This module is planned for a later work package.' }: { title: string; description?: string }) {
  return <><section className="module-placeholder">
    <div className="module-icon"><LayoutDashboard size={25} /></div>
    <span className="eyebrow">MEDTECH HMS PREVIEW</span>
    <h1 tabIndex={-1}>{title}</h1><p>{description}</p>
    <LinkButton to="/dashboard"><ChevronLeft size={17} />Back to dashboard</LinkButton>
  </section><PageFooter /></>
}
