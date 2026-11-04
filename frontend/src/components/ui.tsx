import type { ComponentProps, ReactNode } from 'react'
import { Link } from 'react-router-dom'

export function Button({ className = '', variant = 'secondary', type = 'button', ...props }: ComponentProps<'button'> & { variant?: 'primary' | 'secondary' }) {
  return <button type={type} className={`${variant}-button ${className}`} {...props} />
}

export function LinkButton({ className = 'primary-button', ...props }: ComponentProps<typeof Link>) {
  return <Link className={className} {...props} />
}

export function Panel({ className = '', ...props }: ComponentProps<'section'>) {
  return <section className={`panel ${className}`} {...props} />
}

export function PageHeader({ eyebrow, title, description, actions }: { eyebrow: string; title: string; description: string; actions?: ReactNode }) {
  return <section className="welcome-row">
    <div><div className="eyebrow"><span className="live-dot" />{eyebrow}</div>
      <h1 tabIndex={-1}>{title}</h1><p>{description}</p></div>
    {actions && <div className="welcome-actions">{actions}</div>}
  </section>
}

export function TableRegion({ label, className = '', children }: { label: string; className?: string; children: ReactNode }) {
  return <div className={`table-wrap ${className}`} tabIndex={0} role="region" aria-label={label}>{children}</div>
}

export function StatusBadge({ tone = 'neutral', children }: { tone?: string; children: ReactNode }) {
  return <span className={`status-pill ${tone}`}>{children}</span>
}

export function PageFooter({ label = 'MedTech HMS' }: { label?: string }) {
  return <footer className="page-footer"><span>{label}</span><span>Synthetic data · Read-only preview</span></footer>
}

export function EmptyState({ title, description, children }: { title: string; description: string; children?: ReactNode }) {
  return <div className="patient-empty"><h2>{title}</h2><p>{description}</p>{children}</div>
}
