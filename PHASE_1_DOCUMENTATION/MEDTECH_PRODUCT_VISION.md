# MedTech Product Vision

## Purpose

MedTech should evolve from a page-based hospital management application into a trustworthy healthcare operations platform for patients, doctors, and administrators. The product must improve care coordination without obscuring clinical accountability or exposing sensitive data.

## Evidence-based current position

The existing PHP/MySQL application has three role portals, registration and authentication, appointments/availability, medical records, prescriptions, laboratory, billing, reports, invoices, and in-app notifications. `AUDIT_REPORT.md` records 114 PHP files, PHP 8.0.30, five applied feature migrations, and a local XAMPP deployment. The audit also records that authenticated end-to-end workflows and broad responsive rendering were not proven. Project Owner phpMyAdmin evidence subsequently live-verified MariaDB 10.4.32, aggregate schema inventory, notification-trigger metadata, and a point-in-time notification aggregate; complete trigger definitions remain pending. See `MEDTECH_DATABASE_VERIFICATION.md`.

## Product outcomes

1. **Patient confidence:** self-service booking, understandable care timeline, results, prescriptions, invoices, and privacy controls.
2. **Doctor focus:** a single patient context, fast appointment actions, concise clinical documentation, orders, prescriptions, and follow-up cues.
3. **Operational control:** reliable scheduling, collection visibility, laboratory status, exceptions, and auditable administration.
4. **Platform readiness:** one protected API contract can serve a future React web client and Android client without duplicating clinical rules.

## Product principles

- Care-first, not dashboard-first.
- Show only role-appropriate minimum necessary data.
- Explain status, next action, and ownership everywhere.
- Preserve source-of-truth data and historical financial/clinical records.
- Progressive modernization: stabilize PHP behavior first, add a versioned API, then replace visual surfaces incrementally.

## Success measures

- Booking completion and cancellation/reschedule completion rate.
- Median time from appointment to completed clinical record/prescription/lab result.
- Billing balance reconciliation accuracy.
- Unauthorized-access test pass rate and security regression rate.
- Task completion at mobile and desktop breakpoints for each role.

## Non-goals for Phase 1

No React build, API implementation, Android implementation, schema change, migration execution, public launch, or claim of legal/regulatory compliance.

## Revision note — 2026-11-02

Added screen-level role deliverables, concrete technology decisions, a fresh database-availability constraint, and owner approval gates. See `MEDTECH_PHASE1_FINAL_REVIEW.md`.
