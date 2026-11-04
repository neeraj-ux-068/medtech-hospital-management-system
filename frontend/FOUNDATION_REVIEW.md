# Frontend foundation review — 2026-11-03

Branch: phase-2/foundation. This completes the frontend foundation checkpoint and
specified API/authentication design work. Stop before API implementation.

## Implementation outcome

React Router provides Dashboard, Patients, Doctors, Appointments, Departments,
Billing, Reports and Settings routes, patient profile deep links, an index redirect
and a route-not-found fallback. The existing synthetic dashboard retains its cards,
revenue illustration, schedule and recent-patient table. Future modules remain
explicit placeholders. Patient directory and profiles remain read-only fixtures;
no registration, deletion, billing or clinical write functionality is introduced.

The application shell is separated into layout, sidebar, header and breadcrumbs.
Reusable controls include buttons/links, panels/cards, search, selects, scrollable
table regions, bounded pagination, status badges, empty/loading/error states and a
page error boundary. Confirmation dialogs are intentionally deferred: there is no
approved mutation requiring confirmation, and unused modal infrastructure would
add unnecessary behavior. No dependency was added; React Router and Lucide were
already installed. TypeScript strict mode is enabled for application and Vite code.

The first checkpoint already introduced the routing/core components. The follow-up
approval added strict mode, further extraction, responsive browser verification,
expanded tests, and API/authentication design. The inventories below distinguish
the two passes rather than describing the whole untracked frontend as newly created.

## Folder structure

```text
frontend/
  src/
    app/           AppLayout.tsx, navigation.ts
    components/    Sidebar, AppHeader, Breadcrumbs, FormControls, Pagination,
                   Feedback, PlaceholderPage, ui
    dashboard/     DashboardPage.tsx
    patients/      PatientsModule.tsx, PatientsModule.css, patientData.ts
    App.tsx        route configuration
    main.tsx       BrowserRouter bootstrap
    App.css        shell/dashboard responsive styles
    index.css      global styles, keyboard focus and skip link
  tests/           register.mjs, routes.test.mjs, api-contract.test.mjs
  review/          screenshots and browser-checks.json
  README.md
  FOUNDATION_REVIEW.md
  tsconfig.app.json, tsconfig.node.json

docs/api/v1/
  README.md
  openapi.json
  AUTHENTICATION_REVIEW.md
  READ_ONLY_IMPLEMENTATION_PLAN.md
```

## Files created in this follow-up pass

- [frontend/src/app/navigation.ts](/C:/Dev/MedTech-HMS-clean-source-20261102/frontend/src/app/navigation.ts)
- [frontend/src/components/Sidebar.tsx](/C:/Dev/MedTech-HMS-clean-source-20261102/frontend/src/components/Sidebar.tsx)
- [frontend/src/components/AppHeader.tsx](/C:/Dev/MedTech-HMS-clean-source-20261102/frontend/src/components/AppHeader.tsx)
- [frontend/src/components/Breadcrumbs.tsx](/C:/Dev/MedTech-HMS-clean-source-20261102/frontend/src/components/Breadcrumbs.tsx)
- [frontend/src/components/FormControls.tsx](/C:/Dev/MedTech-HMS-clean-source-20261102/frontend/src/components/FormControls.tsx)
- [frontend/src/components/Pagination.tsx](/C:/Dev/MedTech-HMS-clean-source-20261102/frontend/src/components/Pagination.tsx)
- [frontend/src/components/Feedback.tsx](/C:/Dev/MedTech-HMS-clean-source-20261102/frontend/src/components/Feedback.tsx)
- [frontend/tests/api-contract.test.mjs](/C:/Dev/MedTech-HMS-clean-source-20261102/frontend/tests/api-contract.test.mjs)
- [docs/api/v1/README.md](/C:/Dev/MedTech-HMS-clean-source-20261102/docs/api/v1/README.md)
- [docs/api/v1/openapi.json](/C:/Dev/MedTech-HMS-clean-source-20261102/docs/api/v1/openapi.json)
- [docs/api/v1/AUTHENTICATION_REVIEW.md](/C:/Dev/MedTech-HMS-clean-source-20261102/docs/api/v1/AUTHENTICATION_REVIEW.md)
- [docs/api/v1/READ_ONLY_IMPLEMENTATION_PLAN.md](/C:/Dev/MedTech-HMS-clean-source-20261102/docs/api/v1/READ_ONLY_IMPLEMENTATION_PLAN.md)
- [frontend/review/dashboard-1366.jpg](/C:/Dev/MedTech-HMS-clean-source-20261102/frontend/review/dashboard-1366.jpg)
- [frontend/review/patients-430.jpg](/C:/Dev/MedTech-HMS-clean-source-20261102/frontend/review/patients-430.jpg)
- [frontend/review/browser-checks.json](/C:/Dev/MedTech-HMS-clean-source-20261102/frontend/review/browser-checks.json)
- [frontend/FOUNDATION_REVIEW.md](/C:/Dev/MedTech-HMS-clean-source-20261102/frontend/FOUNDATION_REVIEW.md)

## Files modified in this follow-up pass

- [frontend/src/app/AppLayout.tsx](/C:/Dev/MedTech-HMS-clean-source-20261102/frontend/src/app/AppLayout.tsx)
- [frontend/src/patients/PatientsModule.tsx](/C:/Dev/MedTech-HMS-clean-source-20261102/frontend/src/patients/PatientsModule.tsx)
- [frontend/src/App.css](/C:/Dev/MedTech-HMS-clean-source-20261102/frontend/src/App.css)
- [frontend/src/index.css](/C:/Dev/MedTech-HMS-clean-source-20261102/frontend/src/index.css)
- [frontend/src/patients/PatientsModule.css](/C:/Dev/MedTech-HMS-clean-source-20261102/frontend/src/patients/PatientsModule.css)
- [frontend/tsconfig.app.json](/C:/Dev/MedTech-HMS-clean-source-20261102/frontend/tsconfig.app.json)
- [frontend/tsconfig.node.json](/C:/Dev/MedTech-HMS-clean-source-20261102/frontend/tsconfig.node.json)
- [frontend/tests/routes.test.mjs](/C:/Dev/MedTech-HMS-clean-source-20261102/frontend/tests/routes.test.mjs)
- [frontend/README.md](/C:/Dev/MedTech-HMS-clean-source-20261102/frontend/README.md)

## Files created in the preceding foundation checkpoint

- [frontend/src/app/AppLayout.tsx](/C:/Dev/MedTech-HMS-clean-source-20261102/frontend/src/app/AppLayout.tsx)
- [frontend/src/components/ui.tsx](/C:/Dev/MedTech-HMS-clean-source-20261102/frontend/src/components/ui.tsx)
- [frontend/src/components/PlaceholderPage.tsx](/C:/Dev/MedTech-HMS-clean-source-20261102/frontend/src/components/PlaceholderPage.tsx)
- [frontend/src/dashboard/DashboardPage.tsx](/C:/Dev/MedTech-HMS-clean-source-20261102/frontend/src/dashboard/DashboardPage.tsx)
- [frontend/tests/register.mjs](/C:/Dev/MedTech-HMS-clean-source-20261102/frontend/tests/register.mjs)
- [frontend/tests/routes.test.mjs](/C:/Dev/MedTech-HMS-clean-source-20261102/frontend/tests/routes.test.mjs)

## Existing files modified in the preceding foundation checkpoint

- [frontend/src/App.tsx](/C:/Dev/MedTech-HMS-clean-source-20261102/frontend/src/App.tsx)
- [frontend/src/main.tsx](/C:/Dev/MedTech-HMS-clean-source-20261102/frontend/src/main.tsx)
- [frontend/src/patients/PatientsModule.tsx](/C:/Dev/MedTech-HMS-clean-source-20261102/frontend/src/patients/PatientsModule.tsx)
- [frontend/src/App.css](/C:/Dev/MedTech-HMS-clean-source-20261102/frontend/src/App.css)
- [frontend/src/index.css](/C:/Dev/MedTech-HMS-clean-source-20261102/frontend/src/index.css)
- [frontend/package.json](/C:/Dev/MedTech-HMS-clean-source-20261102/frontend/package.json)
- [frontend/README.md](/C:/Dev/MedTech-HMS-clean-source-20261102/frontend/README.md)

## Generated tooling output

The explicitly requested build regenerates frontend/dist/index.html, copied public
favicon.svg/icons.svg, and hashed JS/CSS assets under frontend/dist/assets/. It also
updates frontend/node_modules/.tmp/tsconfig.app.tsbuildinfo and
tsconfig.node.tsbuildinfo. Starting Vite may regenerate its dependency cache under
frontend/node_modules/.vite/. These are ignored build/tool artifacts, not source
changes. No lockfile, PHP, SQL migration, or existing root report was edited.
The repository still reports frontend/ as untracked from before this work. No git
add, commit, checkout, push, deployment or database action was performed.

## Validation actually executed

- npm run lint: PASS.
- npm test: PASS, 15 tests (route/render/shared-control tests and contract consistency).
- npm run build: PASS, including strict TypeScript checking and Vite bundling.
- OpenAPI document parses as JSON and all local schema references resolve in tests.
  This is not a claim of external OpenAPI-validator or live-endpoint conformance.
- Browser root redirect, dashboard search empty state, profile refresh and direct
  entry, preserved directory filter, pagination, back history and tablet Settings
  navigation were exercised using synthetic fixtures.
- Mobile drawer: focus enters close button; Shift+Tab/Tab wrap; Escape closes and
  returns focus; background is inert; body scrolling is locked only while open.
- Patient table ArrowRight keys moved its own scrollLeft to 120 while page width
  stayed within the viewport. Main filter, profile action and pagination controls
  were measured at 44px minimum height; pagination now measures 44x44px.
- No console warnings/errors were observed in the inspected local preview log.

## Responsive results

Viewport height was 900px. All three page types (dashboard, directory, profile)
were checked at each requested width. The table below reports dashboard/directory
client width versus document scroll width. Scrollbars account for the difference
between requested viewport width and client width.

| Viewport | Client / scroll width | Dashboard stats | Directory filters | Result |
| --- | --- | --- | --- | --- |
| 375 | 360 / 360 | 1 column | 1 column | No page horizontal overflow |
| 430 | 415 / 415 | 2 columns | 1 column | No page horizontal overflow |
| 768 | 753 / 753 | 2 columns | 2 columns | No page horizontal overflow |
| 1366 | 1351 / 1351 | 4 columns | 4 columns | No page horizontal overflow |
| 1920 | 1905 / 1905 | 4 columns | 4 columns | No page horizontal overflow |

Profiles also had document scroll width equal to client width at all five sizes.
The 375px patient table measured 296px visible width versus 655px scroll width;
it remains independently scrollable. The sidebar is collapsed through 900px and
visible above that breakpoint. Browser sizing returned 902px for a requested
901px boundary check, so the observed boundary evidence is 900px/902px.
These are desktop browser viewport emulations, not physical mobile device tests.
Screenshots are review artifacts; browser-checks.json preserves numeric evidence.
Full contrast/screen-reader certification, physical touch testing and 200% zoom
remain broader accessibility acceptance tasks.

## API and authentication design outcome

The draft /api/v1 contract proposes GET/HEAD session inspection, an admin-only
summary, an admin-only registered-patient-account collection, and an admin-only
account demographic detail resource. It defines JSON envelopes/errors, no-store
headers, bounded pagination, stable allowlisted sorts, date filters and explicit
role/identifier rules. The API account ID is users.id; it is not tblpatient.ID or
the synthetic PT display identifier. Clinical/financial payloads and patient data
writes are absent. Live name/email/phone search and unsupported demo filters are
deferred for privacy/model review. No endpoint exists as a result of this work.

All three PHP login handlers and shared session/security/database helpers were
inspected. The recommendation preserves PHP server sessions and login pages,
requires same-origin HTTPS/cookie verification, and adds a separately approved
JSON adapter rather than changing existing page guards. Endpoint implementation
stops because the contract has not been reviewed, live metadata/cookies have not
been verified in this package, and page redirects/plain-text configuration errors
need explicit API handling. No live DB/account authentication test was performed.

## Security observations and remaining issues

- Frontend is an unauthenticated synthetic preview. Demo administrator identity is
  labeled as such; frontend routes do not authorize backend data access.
- Patient/doctor recovery checks personal account details without verified channel
  ownership. This is a production concern, unchanged by this package.
- Idle/absolute session expiry, revocation, MFA and login throttling are not evidenced
  in the reviewed helpers. Server-level controls remain unverified.
- Legacy patient linkage can depend on mutable email addresses. Do not expose new
  doctor/patient API reads until a stable identity/ownership policy is approved.
- Existing login and logout can write hashes/logs. They were not executed for tests.
- Current PHP session guards redirect; config failures emit text. A JSON/session
  adapter is a required reviewed change, not permission to weaken the page guards.
- Secure cookie behavior behind proxies, actual session cookie settings, required
  source schema and timestamp semantics need verification before live integration.
- Synthetic q filters enter URL history. They must not silently become live patient
  search. No PHI or tokens enter persistent browser storage in this package.
- Dashboard figures/schedule/date remain illustrative. They are not live metrics.
- External Google Fonts remain an existing external dependency; self-hosting can
  be considered separately. Future host SPA rewrites are documented, not deployed.

## Recommended next milestone

Owner review of this frontend checkpoint and /api/v1 design. Then approve metadata-
only schema/session verification and the isolated JSON/session adapter plan before
implementing SELECT-only services on a synthetic test database. Preserve the legacy
PHP portals and require authorization negative tests before any live-data rollout.
