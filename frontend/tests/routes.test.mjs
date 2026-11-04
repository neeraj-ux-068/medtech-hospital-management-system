import test from 'node:test'
import assert from 'node:assert/strict'
import { createElement } from 'react'
import { renderToStaticMarkup } from 'react-dom/server'
import { createMemoryRouter, MemoryRouter, RouterProvider } from 'react-router-dom'
import App from '../src/App.tsx'
import { Button, TableRegion } from '../src/components/ui.tsx'
import Pagination from '../src/components/Pagination.tsx'
import Breadcrumbs from '../src/components/Breadcrumbs.tsx'
import { SearchField, SelectField } from '../src/components/FormControls.tsx'
import { ErrorState, LoadingState } from '../src/components/Feedback.tsx'
import { pageTitle } from '../src/app/navigation.ts'

const render = (path) => renderToStaticMarkup(createElement(MemoryRouter, { initialEntries: [path] }, createElement(App)))

test('dashboard links directly to synthetic patient profiles', () => {
  const html = render('/dashboard')
  assert.match(html, /Hospital dashboard/)
  assert.match(html, /href="\/patients\/PT-2026-0841"/)
  assert.match(html, /Synthetic data/)
})

test('direct patient profile URL renders the selected fixture and active navigation', () => {
  const html = render('/patients/PT-2026-0841')
  assert.match(html, /Patient profile/)
  assert.match(html, /PT-2026-0841/)
  assert.match(html, /aria-current="page"[^>]*href="\/patients"/)
  assert.doesNotMatch(html, /<form|Register patient/)
})

test('directory URL filters survive direct entry and are retained in profile links', () => {
  const html = render('/patients?department=Cardiology&status=Active')
  assert.match(html, /href="\/patients\/PT-2026-0841\?department=Cardiology&amp;status=Active"/)
  assert.match(html, /<option selected="">Cardiology<\/option>/)
})

test('profile return link retains directory filters and page', () => {
  assert.match(render('/patients/PT-2026-0841?q=Aarav&page=2'), /href="\/patients\?q=Aarav&amp;page=2"/)
})

test('pagination has safe defaults and clamps out-of-range values', () => {
  for (const value of ['NaN', 'Infinity', '-5', '1.5']) {
    assert.match(render(`/patients?page=${value}`), /Showing 1–6 of 18/)
  }
  assert.match(render('/patients?page=999'), /Showing 13–18 of 18/)
})

test('empty filters and invalid patient identifiers show explicit states', () => {
  assert.match(render('/patients?q=nonexistent-fixture'), /No patients found/)
  const html = render('/patients/unknown')
  assert.match(html, /Patient not found/)
  assert.doesNotMatch(html, /Personal &amp; contact details/)
})

test('all planned module URLs resolve and unknown paths show a fallback', () => {
  const moduleTitles = { doctors: 'Doctors management', appointments: 'Appointments management', departments: 'Departments management', billing: 'Billing management', reports: 'Reports &amp; analytics', settings: 'Settings' }
  for (const [path, title] of Object.entries(moduleTitles)) assert.ok(render(`/${path}`).includes(title), `${path} route should render ${title}`)
  assert.match(render('/missing/path'), /Page not found/)
})

test('memory history supports forward, back, and direct route rendering', async () => {
  const router = createMemoryRouter([{ path: '*', element: createElement(App) }], { initialEntries: ['/dashboard'] })
  try {
    await router.navigate('/patients?page=2')
    await router.navigate('/patients/PT-2026-0841?page=2')
    assert.match(renderToStaticMarkup(createElement(RouterProvider, { router })), /Patient profile/)
    await router.navigate(-1)
    assert.equal(router.state.location.pathname, '/patients')
    assert.equal(router.state.location.search, '?page=2')
    assert.match(renderToStaticMarkup(createElement(RouterProvider, { router })), /Showing 7–12 of 18/)
    await router.navigate(1)
    assert.equal(router.state.location.pathname, '/patients/PT-2026-0841')
  } finally { router.dispose() }
})

test('shared controls default to non-submitting buttons and keyboard-scrollable tables', () => {
  assert.match(renderToStaticMarkup(createElement(Button, null, 'Reset')), /type="button"/)
  assert.match(renderToStaticMarkup(createElement(TableRegion, { label: 'Patients' }, 'Table')), /tabindex="0" role="region" aria-label="Patients"/)
})

test('shared pagination bounds visible controls for large directories', () => {
  const html = renderToStaticMarkup(createElement(Pagination, { page: 50, pageSize: 20, total: 20000, onChange() {} }))
  assert.match(html, /Showing 981–1000 of 20000/)
  assert.match(html, /aria-label="First page"/)
  assert.match(html, /aria-label="Last page"/)
  assert.equal((html.match(/<button/g) ?? []).length, 9)
  assert.match(renderToStaticMarkup(createElement(Pagination, { page: 1, pageSize: 20, total: 0, onChange() {} })), /Showing 0–0 of 0/)
})

test('shared breadcrumbs identify the current page without a misleading link', () => {
  const html = renderToStaticMarkup(createElement(MemoryRouter, null, createElement(Breadcrumbs, { items: [
    { label: 'Patients', to: '/patients' }, { label: 'Profile' },
  ] })))
  assert.match(html, /aria-label="Breadcrumb"/)
  assert.match(html, /href="\/patients"/)
  assert.match(html, /aria-current="page">Profile/)
})

test('form controls have accessible labels and feedback does not expose raw errors', () => {
  const search = renderToStaticMarkup(createElement(SearchField, { label: 'Search directory', placeholder: 'Name' }))
  assert.match(search, /Search directory<\/span>/)
  assert.match(search, /type="search"/)
  assert.match(renderToStaticMarkup(createElement(SelectField, { label: 'Department' }, createElement('option', null, 'All'))), /Department<select/)
  assert.match(renderToStaticMarkup(createElement(LoadingState)), /aria-busy="true"/)
  assert.match(renderToStaticMarkup(createElement(ErrorState)), /role="alert"/)
})

test('page titles resolve exact routes and valid patient profiles', () => {
  assert.equal(pageTitle('/patients/PT-2026-0841'), 'Patients')
  assert.equal(pageTitle('/dashboard/'), 'Dashboard')
  assert.equal(pageTitle('/doctors/unknown'), 'Page not found')
  assert.equal(pageTitle('/patients/unknown/extra'), 'Page not found')
})
