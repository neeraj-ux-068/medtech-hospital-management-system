import { useEffect, useRef, useState } from 'react'
import type { RefObject } from 'react'
import { Link } from 'react-router-dom'
import { Bell, ChevronDown, Menu } from 'lucide-react'
import Breadcrumbs from './Breadcrumbs'
import type { Breadcrumb } from './Breadcrumbs'
import { SearchField } from './FormControls'

type Props = {
  breadcrumbs: Breadcrumb[]
  menuRef: RefObject<HTMLButtonElement | null>
  sidebarOpen: boolean
  onOpenNavigation: () => void
  search?: { value: string; onChange: (value: string) => void }
}

export default function AppHeader({ breadcrumbs, menuRef, sidebarOpen, onOpenNavigation, search }: Props) {
  const [notificationsOpen, setNotificationsOpen] = useState(false)
  const notificationRef = useRef<HTMLButtonElement>(null)
  const popoverRef = useRef<HTMLDivElement>(null)
  useEffect(() => {
    if (!notificationsOpen) return
    const onKey = (event: KeyboardEvent) => {
      if (event.key === 'Escape') { setNotificationsOpen(false); notificationRef.current?.focus() }
    }
    const onPointer = (event: PointerEvent) => {
      if (event.target instanceof Node && !popoverRef.current?.contains(event.target)) setNotificationsOpen(false)
    }
    document.addEventListener('keydown', onKey)
    document.addEventListener('pointerdown', onPointer)
    return () => { document.removeEventListener('keydown', onKey); document.removeEventListener('pointerdown', onPointer) }
  }, [notificationsOpen])
  return <header className="topbar">
    <div className="topbar-left">
      <button ref={menuRef} aria-expanded={sidebarOpen} aria-controls="main-navigation" className="mobile-menu" onClick={onOpenNavigation} aria-label="Open navigation"><Menu size={22} /></button>
      <Breadcrumbs items={breadcrumbs} />
    </div>
    <div className="topbar-actions">
      {search && <SearchField className="search-box" label="Search synthetic appointments" value={search.value}
        onChange={event => search.onChange(event.target.value)} placeholder="Search appointments..." />}
      <div className="notification-wrap" ref={popoverRef}>
        <button ref={notificationRef} className={`icon-button ${notificationsOpen ? 'icon-button-active' : ''}`}
          onClick={() => setNotificationsOpen(value => !value)} aria-label="Demo notifications"
          aria-expanded={notificationsOpen} aria-controls={notificationsOpen ? 'demo-notifications' : undefined}>
          <Bell size={19} /><span className="notification-dot" />
        </button>
        {notificationsOpen && <div id="demo-notifications" className="notification-popover">
          <strong>Demo notifications</strong><p>You have 3 new appointment updates.</p><p>2 new patient registrations today.</p>
          <button onClick={() => { setNotificationsOpen(false); notificationRef.current?.focus() }}>Close</button>
        </div>}
      </div>
      <div className="topbar-divider" />
      <Link className="topbar-user" to="/settings" aria-label="Demo administrator settings">
        <div className="profile-avatar small-avatar">AD</div><span><strong>Demo admin</strong><small>Preview identity</small></span><ChevronDown size={16} />
      </Link>
    </div>
  </header>
}
