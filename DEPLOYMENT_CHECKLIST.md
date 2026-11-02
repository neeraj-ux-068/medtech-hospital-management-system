# MedTech deployment checklist

Use this checklist before deploying MedTech Hospital Management System outside a local XAMPP environment.

## Platform

- [ ] Use PHP 8.0 or later with `mysqli`, `mysqlnd`, `mbstring`, and `openssl` enabled.
- [ ] Use MySQL or MariaDB with a dedicated database and a least-privilege application account. Do not use the XAMPP `root` account in production.
- [ ] Point the web server document root at the `hospital` directory and disable directory listing.
- [ ] Serve the application only over HTTPS. Confirm the HTTPS request makes PHP set the `Secure` session-cookie attribute.

## Configuration and secrets

- [ ] Set database host, name, user, and password through the supported environment configuration; do not commit production credentials.
- [ ] Keep `display_errors=Off` and configure a writable, protected PHP error log.
- [ ] Set an application-appropriate timezone in PHP and MySQL.
- [ ] Rotate any development credentials and remove sample accounts before launch.

## Database

- [ ] Take and verify a database backup before every schema change.
- [ ] Create/import the base `hms` schema from `SQL File/hms.sql` only for a new environment. Never re-import it over an existing deployment.
- [ ] Review and import applicable migrations once, in numeric order: `001_appointment_workflow.sql`, `002_medical_records.sql`, `003_hospital_operations.sql`, `004_prescription_laboratory.sql`, `005_billing_payments.sql`, then `006_notifications.sql`.
- [ ] Confirm the required foreign keys, invoice/payment constraints, notification indexes, and seven notification triggers are present after migration.
- [ ] Record the migration date, operator, backup location, and result in the deployment log.

## File-system and web-server hardening

- [ ] Grant the PHP/web-server account only the file permissions it needs. Code files should not be web-server writable.
- [ ] Keep the bundled jQuery upload demo endpoints disabled. Each `vendor/jquery-file-upload/server/php/index.php` must return HTTP 404.
- [ ] Deny public access to backups, SQL files, logs, `.env` files, and version-control metadata.
- [ ] Configure routine off-host database backups and test restoration to a non-production database.

## Application checks

- [ ] Verify login, logout, session expiry, and role restrictions for patient, doctor, and administrator accounts.
- [ ] Verify appointment booking and status updates, medical records, prescriptions, laboratory requests, invoices, payments, reports, and notifications applicable to the deployment.
- [ ] Confirm a patient cannot access another patient's clinical records, invoices, receipts, or notifications by changing a URL identifier.
- [ ] Confirm payment updates preserve invoice totals and cannot overpay or create duplicate submissions.
- [ ] Confirm state-changing forms reject missing or invalid CSRF tokens.
- [ ] Confirm notifications links resolve within the recipient's permitted portal.
- [ ] Review browser console errors and responsive layouts on supported desktop and mobile browsers.

## Release and recovery

- [ ] Run PHP syntax validation before release: `C:\xampp\php\php.exe -l` for every PHP file.
- [ ] Deploy a reviewed copy of the application, then verify file hashes or a versioned release artifact.
- [ ] Run the smoke tests above using non-production test accounts.
- [ ] Keep a rollback copy of the prior application release and a verified database backup.
- [ ] Document the deployment owner, release version, migration status, and rollback decision point.
