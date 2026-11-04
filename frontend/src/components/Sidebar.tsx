import type { RefObject } from 'react'
import { NavLink, useLocation } from 'react-router-dom'
import { Activity, ChevronRight, MoreHorizontal, Settings, ShieldCheck, X } from 'lucide-react'
import { navigation } from '../app/navigation'

type Props = {
  open: boolean
  sidebarRef: RefObject<HTMLElement | null>
  onDismiss: () => void
  onNavigate: () => void
}

export default function Sidebar({ open, sidebarRef, onDismiss, onNavigate }: Props) {
  const { pathname } = useLocation()
  const closeAfterNavigation = (path: string) => pathname === path ? onDismiss() : onNavigate()
  return <aside id="main-navigation" ref={sidebarRef} className={`sidebar ${open ? 'sidebar-open' : ''}`}
    role={open ? 'dialog' : undefined} aria-modal={open || undefined} aria-label="Workspace navigation">
    <div className="brand">
      <div className="brand-mark"><Activity size={23} strokeWidth={2.5} /></div>
      <div className="brand-copy"><strong>MedTech<span> HMS</span></strong><small>Hospital Management</small></div>
      <button className="sidebar-close" onClick={onDismiss} aria-label="Close sidebar"><X size={19} /></button>
    </div>
    <div className="nav-label">MAIN MENU</div>
    <nav className="side-nav" aria-label="Main navigation">
      {navigation.map(({ label, path, icon: Icon }) => <NavLink key={path} to={path} end={path !== '/patients'}
        className={({ isActive }) => `nav-item ${isActive ? 'active' : ''}`} onClick={() => closeAfterNavigation(path)}>
        <Icon size={19} /><span>{label}</span>
      </NavLink>)}
    </nav>
    <div className="nav-label management-label">MANAGEMENT</div>
    <nav className="side-nav" aria-label="Management navigation">
      <NavLink to="/settings" end className={({ isActive }) => `nav-item ${isActive ? 'active' : ''}`} onClick={() => closeAfterNavigation('/settings')}>
        <Settings size={19} /><span>Settings</span>
      </NavLink>
    </nav>
    <div className="sidebar-bottom">
      <div className="support-card">
        <div className="support-icon"><ShieldCheck size={20} /></div><strong>Need assistance?</strong>
        <p>Contact the MedTech support team for help.</p>
        <NavLink to="/settings" onClick={() => closeAfterNavigation('/settings')}>Get support <ChevronRight size={14} /></NavLink>
      </div>
      <NavLink className="profile-card" to="/settings" onClick={() => closeAfterNavigation('/settings')}>
        <div className="profile-avatar">AD</div><div className="profile-info"><strong>Demo administrator</strong><small>Preview identity</small></div>
        <MoreHorizontal size={20} className="profile-more" />
      </NavLink>
    </div>
  </aside>
}
