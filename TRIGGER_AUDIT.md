# MedTech HMS Notification Trigger Audit

Audit date: 2026-11-02  
Scope: read-only trigger inspection only. No trigger, table, application file, or database data was modified.

## Verification status

### Live metadata verified by the Project Owner; complete definitions pending

The Project Owner has verified the following through phpMyAdmin against `hms` at
`127.0.0.1` (MariaDB 10.4.32), using aggregate/metadata inspection only:

- All seven named notification triggers exist.
- Their trigger names, associated event names, and execution timings are present
  in `information_schema`.
- The notification table currently has 12 rows: all 12 have an `event_key`, all
  12 keys are distinct, and none is `NULL`.

These are **live database findings at the time of verification**. They confirm
trigger presence and the stated point-in-time aggregate, but do not expose or
establish the trigger bodies, `DEFINER`, SQL mode, or any other `SHOW CREATE
TRIGGER` attributes.

### Direct live-definition inspection unavailable to Codex

The required live `SHOW CREATE TRIGGER` inspection could not run from this environment. Every read-only MariaDB client request to `127.0.0.1:3306` failed with:

```text
ERROR 2002 (HY000): Can't connect to MySQL server on '127.0.0.1' (10013)
```

Therefore, the business-logic analysis below is based on the source-controlled
migration `SQL File/migrations/006_notifications.sql`. It does **not** prove
that the live trigger definitions, bodies, definers, or SQL mode match the
migration. Timing/event metadata is live-verified as noted above. The Project
Owner can obtain complete live evidence in phpMyAdmin/CLI with `SHOW CREATE
TRIGGER hms.<trigger_name>` for each named trigger, without modifying anything.

No patient names, contacts, credentials, medical content, or transaction content is included in this report.

## Shared notification dependencies

- Target table: `notifications`.
- Dedupe mechanism: nullable `event_key` with unique key `uq_notification_event`.
- Query/display helper: `hms/include/notifications.php`; it uses `ON DUPLICATE KEY UPDATE id=id` for application-created notifications. The SQL triggers themselves do **not** use this clause.
- Recipient identifiers are numeric IDs from the triggering row or the `admin` table; recipient IDs are not foreign-key constrained in the source migration.
- Notification links are canonicalized by PHP based on `related_type` and recipient role; stored `link_path` should be treated as metadata rather than authorization.

## Trigger inventory and logic

### 1. `trg_notification_appointment_insert`

| Property | Source migration definition |
| --- | --- |
| Associated table | `appointment` |
| Timing/event | `AFTER INSERT`, for each row |
| Recipients | Patient `NEW.userId`; every current `admin.id` |
| Related record | `appointment`, `NEW.id` |
| Condition | Every successfully inserted appointment row |

Plain-English logic: after an appointment is created, insert one patient notification saying the request awaits confirmation. Then select all administrator IDs and insert one review notification per administrator. The links point to the appointment detail route. The patient event key is `appointment-booked:<appointment-id>`; each administrator key additionally includes the admin ID.

Duplicate analysis: a normal repeated application submission produces a different appointment row/ID, so it creates a separate legitimate notification set unless appointment creation itself is idempotent. For the same appointment ID this trigger does not fire twice. The unique event key prevents an identical key from being inserted, but because the trigger lacks `ON DUPLICATE KEY`, an unexpected duplicate-key attempt would fail the originating insert rather than quietly deduplicating it.

Dependencies: `appointment`, `notifications`, and `admin`. It assumes `NEW.userId` identifies the intended patient and `admin.id` identifies active recipients; no active/disabled-admin filter exists.

### 2. `trg_notification_appointment_update`

| Property | Source migration definition |
| --- | --- |
| Associated table | `appointment` |
| Timing/event | `AFTER UPDATE`, for each row |
| Recipient | Patient `NEW.userId` |
| Related record | `appointment`, `NEW.id` |
| Condition | `NEW.status <> OLD.status` |

Plain-English logic: when an appointment status changes, create one patient notification whose type and message include the new status. The event key is `appointment-status:<appointment-id>:<new-status>`.

Duplicate analysis: an update that changes unrelated columns makes no notification. A status sequence that returns to a previously used status (for example, pending → confirmed → pending) attempts the same event key again. The source trigger has no duplicate handler, so the unique key can cause that appointment update to fail. This is a production concern: either status transitions must be strictly one-way, or notification insertion must be made idempotent/transition-specific through an approved migration.

Dependencies: `appointment`, `notifications`. It does not notify doctors or administrators of a status change.

### 3. `trg_notification_invoice`

| Property | Source migration definition |
| --- | --- |
| Associated table | `billing_invoices` |
| Timing/event | `AFTER INSERT`, for each row |
| Recipient | Patient `NEW.patient_user_id` |
| Related record | `invoice`, `NEW.id` |
| Condition | Every successfully inserted invoice row |

Plain-English logic: after invoice creation, insert a patient notification that an invoice is available, with an invoice detail route. The key is `invoice-created:<invoice-id>`.

Duplicate analysis: each invoice ID is unique, so this trigger normally creates one notification per invoice. A failed/retried app request that creates a second invoice row would create a second notification and is an invoice-idempotency concern outside this trigger. A duplicate same-key insert would fail the invoice insert because no trigger-level conflict handler exists.

Dependencies: `billing_invoices`, `notifications`; patient identity is supplied by `NEW.patient_user_id`.

### 4. `trg_notification_payment`

| Property | Source migration definition |
| --- | --- |
| Associated table | `billing_payments` |
| Timing/event | `AFTER INSERT`, for each row |
| Recipient | Patient selected as `billing_invoices.patient_user_id` for `NEW.invoice_id` |
| Related record | `invoice`, matching invoice ID |
| Condition | Every payment insert that joins to an existing invoice |

Plain-English logic: after a payment is recorded, select the invoice’s patient and create a payment-recorded notification linked to that invoice. The event key is `payment-recorded:<payment-id>`.

Duplicate analysis: each payment ID creates one unique key, so a legitimate partial-payment sequence produces separate notifications. A duplicated payment row is a financial workflow/idempotency issue that will produce a separate notification. A missing invoice join produces no notification; the expected invoice foreign key should prevent this, but live verification is unavailable.

Dependencies: `billing_payments`, `billing_invoices`, `notifications`.

### 5. `trg_notification_lab_request`

| Property | Source migration definition |
| --- | --- |
| Associated table | `laboratory_requests` |
| Timing/event | `AFTER INSERT`, for each row |
| Recipients | Patient `NEW.patient_user_id`; every current `admin.id` |
| Related record | `laboratory_request`, `NEW.id` |
| Condition | Every successfully inserted laboratory request |

Plain-English logic: notify the patient that a test was requested and every administrator that processing is needed. The patient key is `laboratory-requested:<request-id>` and per-admin keys append `:a<admin-id>`.

Duplicate analysis: normal request retries that make another request row produce another notification set. The same row cannot normally fire twice; a same-key conflict would fail the original laboratory request because trigger SQL is not conflict-tolerant. No active-admin filter is applied.

Dependencies: `laboratory_requests`, `notifications`, `admin`.

### 6. `trg_notification_lab_update`

| Property | Source migration definition |
| --- | --- |
| Associated table | `laboratory_requests` |
| Timing/event | `AFTER UPDATE`, for each row |
| Recipient | Patient `NEW.patient_user_id` |
| Related record | `laboratory_request`, `NEW.id` |
| Condition | `NEW.status <> OLD.status` |

Plain-English logic: when laboratory request status changes, notify the patient of the new status. The key is `laboratory-status:<request-id>:<new-status>`.

Duplicate analysis: unrelated updates do not notify. Returning to a prior status attempts a previously used event key and can fail the laboratory request update due to the unique key. This is the same material production concern as appointment status updates.

Dependencies: `laboratory_requests`, `notifications`. Result entry/revision alone does not create a notification unless it also changes request status.

### 7. `trg_notification_prescription`

| Property | Source migration definition |
| --- | --- |
| Associated table | `prescription_headers` |
| Timing/event | `AFTER INSERT`, for each row |
| Recipient | Patient `NEW.patient_user_id` |
| Related record | `prescription`, `NEW.id` |
| Condition | Every successfully inserted prescription header |

Plain-English logic: after a prescription header is inserted, notify the patient that a prescription is available and link to its details. The key is `prescription-created:<prescription-id>`.

Duplicate analysis: one unique header ID yields one notification. If a request retry creates a second header, it creates a second notification; transaction/idempotency protection in application/API logic is required. Same-key conflict would fail the prescription header insert.

Dependencies: `prescription_headers`, `notifications`.

## Production concerns

1. **Live definer unknown:** source migration does not specify `DEFINER`. MariaDB assigns the executing account as definer at creation time. The previously reported possible `root`@`localhost` definer cannot be confirmed without live `SHOW CREATE TRIGGER`. If it is present, it is a deployment portability and least-privilege concern: import/replication may fail when that account is absent, and production should use a reviewed dedicated migration/definer strategy. Do not alter it without a tested migration and approval.
2. **Duplicate-key failure mode:** the unique `event_key` is useful, but source trigger inserts do not use conflict-safe syntax. Reversal to a previously notified appointment/lab status can abort the business update. Verify live behavior in staging before broad status-transition changes.
3. **Trigger/API double producers:** application helper `hms_notify()` also writes notifications with duplicate-safe SQL. Future API code must map trigger-owned event keys and avoid producing the same event twice.
4. **Recipient governance:** triggers select all rows in `admin`; source logic does not model active administrators, notification preferences, escalation, or role changes.
5. **Data leakage:** messages in the source are intentionally generic. Keep future messages generic; re-authorize the linked resource at open time, as the PHP link helper is designed to do.
6. **Observability:** trigger failures can roll back their originating DML. Production monitoring should alert on database errors and failed transactions, without logging sensitive contents.

## Recommended next read-only live checks

Once MariaDB is reachable, capture each `SHOW CREATE TRIGGER` output, comparing: associated table, timing/event, full body, `DEFINER`, character set/collation, and SQL mode. Then run aggregate-only checks for duplicate event keys, notifications with missing related records, and notification counts by type/role—without selecting private message text or recipient identity data.
