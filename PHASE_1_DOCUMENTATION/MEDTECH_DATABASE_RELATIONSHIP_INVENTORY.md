# Database Relationship Inventory

## Status

Project Owner phpMyAdmin evidence now verifies MariaDB 10.4.32, 25 InnoDB tables, one primary-key column per table, and 25 foreign keys across 13 tables. It also verifies that every foreign key uses `ON UPDATE RESTRICT` and that delete behavior mixes `RESTRICT`, `SET NULL`, and `CASCADE`. The individual FK-to-column mapping below remains prior-audit/source-derived until exported from live metadata.

```text
users ──< appointment >── doctors
              │                 │
              ├──< appointment_events
              └──< appointment_reschedule_requests

users ──< medical_records >── doctors
users ──< prescription_headers >── doctors ──< prescription_items
users ──< laboratory_requests >── doctors ──< laboratory_results ──< laboratory_result_audit
users ──< billing_invoices ──< billing_invoice_items
                    └─────────< billing_payments
admin ──< billing_invoices/payments/laboratory result activity
recipient role + recipient ID ──< notifications
doctors ──< doctor_availability / doctor_unavailable_dates
```

## Entity inventory

| Domain | Tables evidenced by audit | Integrity focus |
| --- | --- | --- |
| Identity | `users`, `admin`, `doctors`, `tblpatient`, logs | reconcile legacy `tblpatient` versus account ownership without exposing PII. |
| Appointments | `appointment`, events, reschedules, availability, unavailable dates | patient/doctor existence, status/event linkage, schedule collisions. |
| Clinical | `medical_records`, `tblmedicalhistory` | patient/doctor/appointment optional relationship and author/update audit. |
| Prescriptions | header/items | header ownership and item parent existence. |
| Laboratory | request/result/result_audit | request patient/doctor/appointment linkage and result/audit parent existence. |
| Billing | invoices/items/payments | invoice patient/admin/appointment linkage; sums, balance, payment receipts. |
| Notifications | notifications + seven triggers | recipient scoping, unique event keys, duplicate generation. |

## Migration comparison

| Migration | Prior audit interpretation | Approval |
| --- | --- | --- |
| 001 appointment workflow | Reflected | Existing applied state; do not re-run. |
| 002 medical records | Reflected | Existing applied state; do not re-run. |
| 003 hospital operations | Source present, operational tables absent | Requires explicit owner/business approval. |
| 004 prescription/laboratory | Reflected | Existing applied state; do not re-run. |
| 005 billing/payments | Reflected | Existing applied state; do not re-run. |
| 006 notifications | Reflected, seven triggers recorded | Existing applied state; do not re-run. |

## Recommendation

Add a migration ledger only in a future approved schema change; do not infer permanent migration history solely from table existence.

## Retention review required

Confirmed use of `CASCADE` deletion needs a clinical retention review, especially where laboratory results and their audit records can be removed through a parent relationship. Do not change a delete rule until retention, legal, recovery, and application behavior are reviewed and an approved migration is tested.
