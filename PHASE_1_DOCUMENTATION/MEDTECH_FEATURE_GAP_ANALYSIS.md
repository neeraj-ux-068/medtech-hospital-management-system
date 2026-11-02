# Feature Gap Analysis

## Database-verification revision — 2026-11-02

Live phpMyAdmin metadata supplied by the Project Owner confirms the `hms`
database is accessible, has seven notification triggers, and currently has 12
notifications with 12 distinct, non-NULL event keys. This is not evidence that
the source-controlled trigger bodies match the live definitions or that the
deduplication design is safe on appointment/laboratory status reversals. Keep
those items open until `SHOW CREATE TRIGGER` has been captured for all seven
triggers and the retention impact of `CASCADE` delete rules is reviewed.

| Module | Roles | Evidence/current behavior | Test status | Gap and recommendation | Priority |
| --- | --- | --- | --- | --- |
| Identity | All | Login, password migration, sessions, roles in `security.php` | Static/audit checks passed; full role login not tested | Add MFA/admin recovery, throttling, device session management | P0 |
| Patient portal | Patient | Registration, profile, booking/history, records, Rx, lab, billing, notifications | Public route checks passed; authenticated flow not tested | Consolidate into care timeline; accessibility and ownership E2E tests | P0 |
| Appointments | All | Availability, statuses, reschedule/events migration 001 | Server checks/audit passed; workflow UI not tested | Calendar UX, waitlist/no-show, reminders, idempotent APIs | P0 |
| Clinical records | Doctor/Patient/Admin | `medical_records`, legacy history, migration 002 | Schema/audit evidence; UI not tested | Encounter model, allergies, vitals trends, attachments, immutable audit | P0 |
| Prescriptions | Doctor/Patient | Header/items, print view, recent UI repair | Syntax/route passed; save not tested | Formulary, drug/allergy checks, refill/dispense workflow | P1 |
| Laboratory | Doctor/Admin/Patient | Requests/results/audit, migration 004 | Integrity checks passed; writes not tested | Sample lifecycle, abnormal flags, result sign-off, integrations | P1 |
| Billing | Admin/Patient | Invoices/items/payments, migration 005 | Reconciliation passed; UI actions not tested | Tax configuration, refunds/credits, insurance/claims only after requirements | P1 |
| Reporting | Admin/Doctor | Aggregate reports and CSV export | Query/static checks; visual/filter use not tested | governed metrics catalog, scheduled reports, performance indexes | P1 |
| Notifications | All | Table, indexes, seven triggers, migration 006 | schema/trigger checks passed | channel preferences, delivery provider, reminder job, quiet hours | P1 |
| Operations | Admin | `operations.php` exists but migration 003 not applied | Not ready | Require owner approval/business model before installing | P2 |

No row should be promoted from “implemented” to “production-ready” without authenticated functional, security, and responsive evidence.

## Revision note — 2026-11-02

The live DB could not be freshly queried because MySQL port 3306 is unavailable; prior evidence is now explicitly separated from fresh verification.
