import { useEffect, useRef, useState } from 'react'
import { Outlet, useLocation, matchPath } from 'react-router-dom'
import { pageTitle } from './navigation'
import AppHeader from '../components/AppHeader'
import Sidebar from '../components/Sidebar'
import { PageErrorBoundary } from '../components/Feedback'

export default function AppLayout() {
  const location = useLocation()
  const [sidebarOpen, setSidebarOpen] = useState(false)
  const [search, setSearch] = useState('')
  const sidebarRef = useRef<HTMLElement>(null)
  const menuRef = useRef<HTMLButtonElement>(null)
  const returnFocus = useRef(false)
  function closeSidebar() { returnFocus.current = true; setSidebarOpen(false) }
  const activePage = pageTitle(location.pathname)
  const breadcrumbs = [
    { label: 'Workspace', to: '/dashboard' },
    ...(matchPath('/patients/:patientId', location.pathname) ? [{ label: 'Patients', to: '/patients' }, { label: 'Profile' }] : [{ label: activePage }]),
  ]

  useEffect(() => {
    document.title = `${activePage} · MedTech HMS preview`
    document.querySelector<HTMLElement>('#page-content h1')?.focus()
    window.scrollTo(0, 0)
  }, [location.pathname, activePage])

  useEffect(() => {
    if (!sidebarOpen) {
      if (returnFocus.current) menuRef.current?.focus()
      returnFocus.current = false
      return
    }
    const previousOverflow = document.body.style.overflow
    document.body.style.overflow = 'hidden'
    const drawer = sidebarRef.current
    const focusable = () => Array.from(drawer?.querySelectorAll<HTMLElement>('a[href], button:not([disabled])') ?? [])
      .filter(element => element.getClientRects().length > 0)
    focusable()[0]?.focus()
    const close = () => { returnFocus.current = true; setSidebarOpen(false) }
    const onKeyDown = (event: KeyboardEvent) => {
      if (event.key === 'Escape') { event.preventDefault(); close() }
      if (event.key !== 'Tab') return
      const items = focusable()
      const first = items[0]
      const last = items[items.length - 1]
      if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus() }
      else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus() }
    }
    const media = window.matchMedia('(min-width: 901px)')
    const onResize = () => { if (media.matches) setSidebarOpen(false) }
    document.addEventListener('keydown', onKeyDown)
    media.addEventListener('change', onResize)
    return () => {
      document.body.style.overflow = previousOverflow
      document.removeEventListener('keydown', onKeyDown)
      media.removeEventListener('change', onResize)
    }
  }, [sidebarOpen])

  return (
    <div className="app-shell">
      <a inert={sidebarOpen} className="skip-link" href="#page-content">Skip to content</a>
      {sidebarOpen && (
        <button
          tabIndex={-1}
          className="mobile-overlay"
          aria-label="Close navigation"
          onClick={closeSidebar}
        />
      )}

      <Sidebar open={sidebarOpen} sidebarRef={sidebarRef} onDismiss={closeSidebar} onNavigate={() => setSidebarOpen(false)} />

      <main className="main-area" inert={sidebarOpen}>
        <AppHeader key={location.pathname} breadcrumbs={breadcrumbs} menuRef={menuRef} sidebarOpen={sidebarOpen}
          onOpenNavigation={() => setSidebarOpen(true)}
          search={activePage === 'Dashboard' ? { value: search, onChange: setSearch } : undefined} />

        <div id="page-content" className="page-content" tabIndex={-1}>
          <PageErrorBoundary key={location.pathname}><Outlet context={{ search }} /></PageErrorBoundary>
        </div>
      </main>
    </div>
  )
}
