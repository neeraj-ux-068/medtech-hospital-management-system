# MedTech HMS — Phase 1 Final Master Report

**Recommendation:** READY FOR PROJECT OWNER APPROVAL

This discovery package does not claim production readiness, completed authenticated testing, legal compliance, or live trigger-body verification. No application, database, migration, trigger, or deployment configuration was changed while preparing it.

## Approved direction

Use a gradual, parallel migration: preserve the PHP portals; build React + TypeScript alongside them; introduce a PHP REST API at `/api/v1`; keep MySQL `hms` as source of truth; defer React Native + Expo Android; migrate one feature at a time with tests and rollback; and retire no PHP route until its replacement is proven.

## Evidence boundary

| Label | Meaning |
| --- | --- |
| **Live-verified** | Project Owner phpMyAdmin aggregate/metadata evidence. |
| **Source-code finding** | Repository, PHP audit, or migration SQL evidence only. |
| **Proposal** | Target design/estimate requiring delivery validation. |
| **Outstanding** | Not verified; never treated as passed. |

## Current product and application inventory

**Source-code finding:** Patient, doctor, and administrator portals cover authentication, appointments/availability, clinical records, prescriptions, laboratory, billing, reports, invoices, and in-app notifications. The audit records 114 PHP files, local XAMPP, shared security/database helpers, and five feature migrations reflected in prior audit evidence. Authenticated E2E and the full responsive matrix remain outstanding.

Product goals are patient confidence and self-service, doctor-centred clinical context, and auditable operational control. Phase 1 neither builds React/API/Android nor certifies existing workflows for production.

## Database and notifications

### Live-verified

- MariaDB 10.4.32; `hms` accessible in phpMyAdmin at `127.0.0.1`.
- 25 InnoDB tables, each with one primary-key column.
- 25 FK constraints across 13 tables; all use `ON UPDATE RESTRICT`; delete actions include `RESTRICT`, `SET NULL`, and `CASCADE`.
- Seven notification triggers exist, with names, event names, and timings verified via `information_schema`.
- At the verification point: 12 notifications, 12 event keys, 12 distinct keys, and zero NULL event keys.

### Source-code findings and open verification

Migration `006_notifications.sql` describes appointment insert/update, invoice, payment, laboratory request/update, and prescription notification triggers. It uses a unique event key. The application notification helper is conflict-safe; the migration trigger inserts are not. Complete live trigger bodies, SQL modes, and definers remain unverified. The source design may reject an appointment/laboratory status reversal that reuses a prior status event key; this is an open design risk, not a live-proven failure. `CASCADE` paths affecting laboratory results/audit records require clinical retention review.

## Target architecture

```text
Legacy PHP portals ───────────────┐
React + TypeScript web (parallel) ├── HTTPS → PHP /api/v1 → domain services → hms
Future React Native + Expo ───────┘                         │
                                                    jobs / audit / notifications
```

The API is additive, versioned, and server-authoritative: it covers auth, identity, appointments, availability, records, prescriptions, laboratory, invoices, payments, notifications, and reports. Each migrated route has a legacy fallback, acceptance evidence, and rollback path.

## Frontend, API, and Android proposals

React + TypeScript, Vite, React Router, Tailwind tokens, shadcn/ui, TanStack Query, React Hook Form, Zod, Recharts, and a typed OpenAPI client are proposed. Maintain a responsive blue-and-white MedTech design with accessible navigation, tables, forms, and chart text alternatives.

API writes require authentication, allow-listed validation, object-level role and ownership checks, CSRF for cookie flows, idempotency keys, transactions, rate limits, and redacted audit metadata. Existing trigger-owned notifications must not be duplicated by API code. Android is later: React Native + Expo only after API v1 role/ownership parity, using secure token storage and privacy-minimal push payloads.

## Notification responsibility matrix

| Event | Current source responsibility | API rule |
| --- | --- | --- |
| Appointment created/status changed | DB trigger | Do not duplicate until live trigger bodies are compared. |
| Laboratory requested/status changed | DB trigger | Preserve ownership; stage-test status reversal. |
| Prescription, invoice, payment created | DB trigger | Keep source trigger ownership until explicitly transferred. |
| Reminders/preferences/new channels | Proposed worker/API | Add only with tested idempotency and trigger interaction. |

## QA, hosting, and Phase 2 boundary

Use unit, integration, API contract, E2E, security, accessibility, performance, and migration/rollback testing with dedicated non-production accounts/data. Responsive targets are 360, 390, 768, 1024, 1366, and 1920 pixels. Planning infrastructure estimates are ₹2,940–7,560/month (100 MAU), ₹19,320–54,600 (500 MAU), and ₹88,200–310,800 (2,000 MAU), excluding significant labour and compliance variability. Production needs a hosting/region choice, managed backup and monitoring posture, secure secrets, and operational ownership.

Phase 2 starts only when `PHASE_2_ENTRY_CRITERIA.md` is approved and met. It is not a wholesale rewrite, database replacement, Android build, or PHP retirement.

## Final recommendation

**READY FOR PROJECT OWNER APPROVAL** because the approved migration direction, current evidence, unknowns, risks, and delivery gates are consolidated. This is **not** a production-readiness recommendation: trigger definitions, retention, security verification, authenticated E2E, and operational decisions remain open.

