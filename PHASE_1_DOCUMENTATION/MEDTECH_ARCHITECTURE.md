# Target Architecture

## Current architecture

Browser → PHP page controllers/includes → MySQL `hms`. Shared helpers include `config.php`, `security.php`, appointment, clinical, billing, analytics, and notification modules. Role-specific headers/sidebar/footer are duplicated legacy presentation components. SQL migrations 001, 002, 004, 005, and 006 are recorded as applied by the audit; 003 operations is source-only and needs approval.

## Target transitional architecture

```text
React TypeScript web ─┐
Future React Native ──┼─ HTTPS → /api/v1 PHP boundary → domain services → MySQL hms
Legacy PHP views ─────┘                 │
                                      audit logs / jobs / notifications
```

## API boundaries

- `/api/v1/auth`, `/me`, `/patients`, `/appointments`, `/records`, `/prescriptions`, `/laboratory`, `/invoices`, `/payments`, `/notifications`, `/reports`.
- Resource authorization happens server-side for every identifier; clients never decide ownership.
- Uniform JSON error envelope, pagination, filtering, date format, request correlation ID, and optimistic/idempotency keys for writes.
- OpenAPI specification is source-controlled; API versions are additive until a published deprecation window ends.

## Authentication strategy

Keep browser sessions for legacy PHP temporarily. For API clients, prefer short-lived access tokens plus rotated refresh tokens stored using platform-appropriate secure storage; do not expose PHP session cookies to mobile clients. Introduce device/session revocation, login throttling, MFA for administrators, and audit events before public launch.

## Data and jobs

Use explicit domain transactions for appointment allocation, prescription/order creation, and payment posting. A scheduled worker should create reminders idempotently. Database triggers currently create some notification events; document and test interaction before adding API-side notification producers to avoid duplicates.

## Revision evidence — 2026-11-02

Project Owner live aggregate evidence confirms seven notification triggers and 12 existing notifications with 12 distinct non-null event keys. Complete trigger bodies remain source-only evidence. The API design must therefore preserve trigger-owned event semantics until live `SHOW CREATE TRIGGER` comparison is complete; appointment/laboratory return-to-prior-status duplicate-key behavior remains unresolved.

## Decisions required

Choose API token provider/model, background worker/queue, object storage provider, audit-log retention, and whether ABDM/FHIR interoperability is in scope.

## Revision note — 2026-11-02

Endpoint conventions, error format, ownership, and idempotency requirements are elaborated in `MEDTECH_BACKEND_API_DESIGN.md`.
