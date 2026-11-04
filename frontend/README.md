# MedTech React foundation checkpoint

This package is a read-only, synthetic-data preview. It does not authenticate users,
call PHP APIs, or read or write the hospital database. The displayed administrator
identity is presentation only and is not an authenticated session.

## Local checks

Use the installed dependencies, or run `npm ci` for a clean dependency installation.
The route-test harness uses Node 24 synchronous module hooks; use Node 24.15 or newer
within the Node 24 release line for the documented checks.

```sh
cd frontend
npm run dev
npm run lint
npm test
npm run build
```

`npm test` uses Node's test runner, the installed TypeScript compiler, React server
rendering, and memory-router navigation. It adds no test dependencies. It covers
route resolution, profile deep links, filter preservation, invalid identifiers,
pagination boundaries, back/forward history, shared controls, and API contract
consistency. TypeScript strict mode is enabled for both app and Vite configuration.
It does not execute browser focus effects or measure layout.

## Routes

- `/` redirects to `/dashboard` with replacement history.
- `/dashboard` displays the existing synthetic dashboard.
- `/patients` displays the read-only fixture directory.
- `/patients/:patientId` displays a fixture profile or a patient-not-found state.
- `/doctors`, `/appointments`, `/departments`, `/billing`, `/reports`, `/settings`
  are explicit future-module placeholders.
- Unknown addresses show a page-not-found state within the application shell.

Directory query parameters are `q`, `department`, `status`, and `page`. Filters and
page are retained in profile links and the return link. Filtering replaces the
current history entry; pagination and profile navigation create history entries.
Only synthetic data may be used in these URLs. Before live integration, review
whether patient-search terms may enter history, referrer headers, or server logs.
Do not enter real patient information in this preview.

BrowserRouter uses Vite's `BASE_URL` as its basename. For a future subdirectory
installation, configure Vite's `base` accordingly. A future host must serve the SPA
entry point for frontend routes and leave `/api/v1`, existing PHP routes, static
assets, and missing asset responses outside that fallback. No server routing or
hosting configuration is changed by this work package.

## Shared components

- `src/app/AppLayout.tsx`: route-aware navigation, shell, title/focus updates, and
  responsive drawer with focus containment, Escape, focus return, and scroll lock.
- `src/components/ui.tsx`: Button, LinkButton, Panel, PageHeader, TableRegion,
  StatusBadge, PageFooter, and EmptyState.
- `src/components/Sidebar.tsx`, `AppHeader.tsx`, `Breadcrumbs.tsx`: reusable shell UI.
- `src/components/FormControls.tsx`, `Pagination.tsx`: shared filter and paging controls.
- `src/components/Feedback.tsx`: loading/error states and a page error boundary.
- `src/app/navigation.ts`: shared route labels and title resolution.
- `src/components/PlaceholderPage.tsx`: planned-module and unknown-route views.
- `src/dashboard/DashboardPage.tsx`: dashboard extracted from the application shell.
- `src/patients/PatientsModule.tsx`: URL-driven fixture directory and profiles.

Existing CSS breakpoints remain at 1180, 900, 600 (Patients), and 390 pixels.
Closed mobile navigation is hidden from keyboard focus. Appointment status stays
visible on mobile, header search remains usable, and tables scroll horizontally.
The previous demo registration form is absent from the active UI; this package
supports no patient mutation.

## Review boundary and security

Stop here for owner review. The API contract and PHP authentication inspection are
documented at `../docs/api/v1/`; no API endpoints are implemented. PHP authentication
integration, schema changes, and patient/clinical/financial writes remain outside
this checkpoint. PHP and SQL files remain untouched. Client routes are not an
authorization boundary. All future live endpoints require server-side session,
role, and patient-ownership enforcement following the separately reviewed contract.
Previously identified recovery, legacy identity linkage, session-expiry, and
notification-trigger concerns remain unresolved by this frontend work.

ESLint, production build (strict TypeScript), and 15 automated tests passed.
Dashboard, Patients directory, and profile layouts were checked in the browser at
375, 430, 768, 1366, and 1920px. No page-level horizontal overflow was measured;
wide tables scroll independently. Drawer focus/Escape, tablet Settings navigation,
profile refresh/filter return, pagination/history, and keyboard table scrolling
were exercised. Browser evidence is in `review/`; the full file inventory and
remaining limitations are in `FOUNDATION_REVIEW.md`.

These are browser-emulated checks, not physical-device or full accessibility
certification. Confirmation dialogs are deferred until a permitted workflow needs
one. No patient mutation is available to confirm in this milestone.
