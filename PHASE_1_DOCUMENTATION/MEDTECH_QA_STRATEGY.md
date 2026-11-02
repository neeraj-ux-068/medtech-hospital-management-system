# QA Strategy

## Existing evidence

Audit evidence: PHP lint passed for 114 files; protected route redirects/denials, migration/table checks, billing reconciliation, notification triggers, and selected mobile public rendering passed. Authenticated workflows, full responsive range, cross-role ownership, action submission, printing, and browser compatibility are not tested.

## Test layers

1. **Unit:** validation, money/status calculations, permission predicates, CSV formula escaping.
2. **Integration:** PHP service + MySQL transactions, migrations on copy of production-like data, trigger/idempotency behavior.
3. **API contract:** OpenAPI schema, authentication/authorization, pagination/filter boundary tests, negative cases.
4. **E2E:** dedicated non-production patient, doctor, administrator accounts; no production clinical/financial data.
5. **Security:** OWASP ASVS/API test plan, CSRF, session fixation/logout, brute-force/rate limit, IDOR/BOLA, XSS, SQL injection, file uploads.
6. **Accessibility:** keyboard, screen reader, contrast, zoom, form errors, table behavior.
7. **Performance:** appointment availability concurrency, 95th percentile endpoint/load budgets, reporting query plans.

## Mandatory matrix

For each role test desktop 1366px and mobile 360/390px plus 768, 1024, and 1920px: navigation/drawer, tables, forms, error states, print where relevant, and no horizontal page overflow. Test current Chrome, Edge, Firefox, and Android Chrome before launch.

## Data policy

Use anonymized, seeded staging data with distinct roles and records. Snapshot/restore staging before test suites. Never use real patient data for exploratory testing.

## Release gates

No P0/P1 security defect open; migrations rehearsed and rollbacked; backup restore passed; E2E core patient/doctor/admin path passed; authorization suite passed; accessibility smoke passed; logs/alerts tested; release and rollback approval recorded.

## Revision note — 2026-11-02

Wireframe breakpoints and role-screen inventory now define the responsive/E2E test matrix. Current authenticated results remain unverified, not failed.
