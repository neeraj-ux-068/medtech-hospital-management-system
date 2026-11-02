# Database Verification

## Fresh verification outcome — 2026-11-02

### Live evidence provided by the Project Owner through phpMyAdmin

The Project Owner has subsequently verified that `hms` is accessible through phpMyAdmin at `127.0.0.1` and supplied the following aggregate-only live evidence:

| Check | Live result |
| --- | --- |
| Server | MariaDB 10.4.32 |
| Database access | `hms` accessible through phpMyAdmin at `127.0.0.1` |
| Tables | 25 total; all InnoDB |
| Primary keys | All 25 tables have one primary-key column |
| Foreign keys | 25 constraints across 13 tables |
| FK update action | All use `ON UPDATE RESTRICT` |
| FK delete actions | Mix of `RESTRICT`, `SET NULL`, and `CASCADE` |
| Notification triggers | Seven exist; event names and timings verified via `information_schema` |
| Notification aggregate | 12 total rows; 12 event keys; 12 distinct event keys; zero NULL event keys |

This supersedes the earlier **live-unavailable** conclusion for aggregate schema verification. It does **not** supersede the limitation on complete trigger-definition inspection below.

### Direct CLI limitation in this environment

The Codex process still could not connect to TCP `127.0.0.1:3306`; the read-only client returned `ERROR 2002 (HY000)` with Windows error `10013`. No service start/stop operation was attempted. The Project Owner’s phpMyAdmin evidence is recorded as live verification; the failed CLI connection is an environment-access limitation, not evidence that `hms` is unavailable.

## Safe Project Owner steps

1. In XAMPP Control Panel, confirm MySQL is running and port 3306 is the configured port.
2. If it cannot start, preserve the error log and resolve the service/port issue before retrying; do not overwrite data files.
3. Provide a read-only database account or run approved read-only queries locally.
4. Re-run this document’s inventory script against `hms`; do not use `UPDATE`, `INSERT`, `DELETE`, `ALTER`, `DROP`, `TRUNCATE`, or migrations.

## Previous audit and source evidence

`AUDIT_REPORT.md` recorded tables for appointments/events/reschedules, doctors/availability, users/tblpatient, medical records, prescription headers/items, laboratory requests/results/audit, billing invoices/items/payments, notifications, and legacy contact/history/log/page tables. It recorded migrations 001, 002, 004, 005, and 006 as reflected, with 003 operations present in source but not installed.

The complete business logic for the seven notification triggers was analyzed from source-controlled `SQL File/migrations/006_notifications.sql` in `TRIGGER_AUDIT.md`. It is **source evidence only**: complete live `SHOW CREATE TRIGGER` output, including `DEFINER`, SQL mode, and any drift from the migration, remains unverified.

## Pending read-only verification set

- Complete `SHOW CREATE TRIGGER` output for each of the seven notification triggers, including `DEFINER`, body, SQL mode, and character set/collation.
- `information_schema.statistics` confirmation of notification indexes and other production-critical indexes.
- Aggregate-only orphan checks for appointment→doctor/user, records→patient/doctor, prescription→header/patient/doctor, lab→request/patient/doctor, invoice/payment→parents.
- Compare source migration DDL to live schema; infer applied status only, since no migration ledger is currently evidenced.
- Clinical retention review of the confirmed `CASCADE` delete rules affecting laboratory results and audit records.

## Constraints

Do not display passwords, email addresses, names, phone numbers, note/result text, tokens, or raw patient records in reports. Report only schema and aggregate results.

## Phase 1 consolidation note — 2026-11-02

The Project Owner approved a gradual, parallel React + TypeScript and `/api/v1`
migration while retaining PHP and `hms` as the operational source of truth.
This approval changes no database finding and authorizes no schema work. Live
evidence remains limited to the aggregate facts above; trigger bodies and
definers remain outstanding verification, and the migration analysis remains
source-code evidence only.
