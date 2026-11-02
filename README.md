# MedTech Hospital Management System

For production deployment preparation, follow [DEPLOYMENT_CHECKLIST.md](DEPLOYMENT_CHECKLIST.md).

MedTech is a PHP and MySQL hospital management application with separate patient, doctor, and administrator workflows. This repository preserves the existing page-based application and incrementally strengthens its security and setup documentation.

The public site and patient, doctor, and administrator portals have also received a responsive MedTech visual refresh. See [CHANGELOG.md](CHANGELOG.md) for the Stage 3 UI summary. Stage 4 adds canonical appointment statuses through an explicit database migration.

## Appointment management (Stage 4)

Appointments now use a reviewed workflow: a patient submits a **pending** request from published doctor availability; the assigned doctor can confirm or reject it; completed/cancelled appointments remain in the history. Patients can cancel eligible upcoming appointments and request a reschedule. Doctors and administrators review reschedule requests, and actions are recorded in an appointment event history. Administrators can filter appointments by doctor, patient name, date, and status. Doctors and administrators manage recurring weekday windows, slot duration/capacity, and unavailable dates at `hospital/hms/manage-availability.php`.

### Apply the Stage 4 database migration

Stage 4 requires the new appointment status, visit reason and doctor notes fields plus availability, event and reschedule tables. Existing appointment rows are retained; legacy rows with both status flags active are mapped to `confirmed`, and other legacy combinations are mapped to `cancelled`. The legacy flags remain for compatibility. **Back up the `hms` database first**, then import `SQL File/migrations/001_appointment_workflow.sql` once into `hms` using phpMyAdmin. The application does not run schema changes automatically. Set at least one doctor's availability before testing patient booking; no schedule is assumed by default. A fresh existing install remains unchanged until this explicit migration is applied.

The slot endpoint rechecks availability on the server, and a MySQL named lock serializes concurrent reservations for a doctor/time. CSRF tokens protect appointment mutations, role and ownership checks scope appointment access, and event notes/reasons are HTML-escaped in views. Slot capacity is configurable per time slot; daily caps, cross-midnight shifts, email/SMS, and automatic reminder delivery are not included.

## Patient medical records (Stage 5)

The `medical-records.php` page adds a structured clinical timeline for diagnosis, symptoms, notes, treatment, follow-up instructions, and optional observations (blood pressure, temperature, pulse, weight, and height). Doctors can create/update their own records for patients linked through their appointments or assigned legacy patient profiles. Patients can view only records tied to their account; administrators can search/filter records and are read-only. Existing `tblmedicalhistory` rows are preserved and displayed in the timeline when their legacy patient email matches a registered patient account. Existing `tblpatient`, `tblmedicalhistory`, doctors, users, and appointments remain in place; legacy medical-history pages remain available.

### Stage 5 migration

After backing up `hms`, apply `SQL File/migrations/001_appointment_workflow.sql` first if it has not already been applied, then apply `SQL File/migrations/002_medical_records.sql` once. Stage 5 creates the structured `medical_records` table with patient/doctor/optional appointment relationships, indexes, vitals, and created/updated timestamps. It does not modify or copy/delete legacy history rows. The treating doctor ID identifies the author; `updatedByDoctorId` and `updatedAt` capture later edits. The application does not execute migrations automatically.

### Manual appointment workflow check

After importing the migration, start Apache and MySQL, sign in as admin, and add a doctor availability rule with a future weekday window. Sign in as a patient, choose the matching doctor/date/time, enter a visit reason, and submit; confirm the row appears as Pending and opens in details. Sign in as that doctor, open the appointment list/details, confirm the request, then verify the patient sees Confirmed. Submit a patient reschedule request, approve it as the assigned doctor, and confirm the new date/time plus activity entries. Verify patient cancellation updates status; verify doctor reject and completed transitions with a past appointment as appropriate. In admin appointments, filter by each supported field and change a permitted status. Try changing an appointment ID to another patient's ID and another doctor's ID in a POST; the ownership-scoped server handler should reject it. Open appointment routes in a signed-out browser and verify they redirect/deny access. For double booking, submit two patient requests for the same doctor/slot in separate sessions; only requests within configured slot capacity should be accepted.

## Technology stack

- PHP 8.0+ with MySQLi and `mysqlnd`
- MySQL or MariaDB (the included dump was exported from MariaDB)
- Apache via XAMPP for local development
- HTML, CSS, JavaScript, Bootstrap, jQuery, and Sass assets

No Composer or Node package installation is required for the current application.

## Requirements

- XAMPP with Apache, PHP 8.0+, and MySQL/MariaDB enabled
- PHP extensions `mysqli` and `mysqlnd`
- A database named `hms`
- A browser with JavaScript enabled for the existing dashboard widgets and AJAX forms

## XAMPP installation

1. Copy the project folder so the public entry point is `C:\xampp\htdocs\hospital\index.php`.
2. Start **Apache** and **MySQL** from the XAMPP Control Panel.
3. Open phpMyAdmin at `http://localhost/phpmyadmin/` and create a database named `hms` using `utf8mb4` as its default collation.
4. Select `hms`, choose **Import**, and import `SQL File/hms.sql`.
5. The local configuration defaults to `localhost`, user `root`, an empty password, and database `hms` only while `HMS_ENV` is `development`, `local`, or `test`. These defaults are for a local XAMPP installation only. For production, set `HMS_ENV=production` and provide all of `HMS_DB_HOST`, `HMS_DB_USER`, `HMS_DB_PASSWORD`, and `HMS_DB_NAME` in Apache’s environment. Use a dedicated least-privilege database account; the application now refuses to start with incomplete production database configuration.
6. Browse to [http://localhost/hospital/](http://localhost/hospital/).

### Production database account

Create the database and application account through your production database administrator, not in application source. The account should have access only to the `hms` database and only the data privileges the running application needs. For a typical deployed application, the administrator can use a generated secret in place of the placeholder below:

```sql
CREATE USER 'medtech_app'@'127.0.0.1' IDENTIFIED BY '<generated-long-secret>';
GRANT SELECT, INSERT, UPDATE, DELETE ON hms.* TO 'medtech_app'@'127.0.0.1';
FLUSH PRIVILEGES;
```

Run reviewed schema migrations using a separate, temporary migration account that has only the required DDL privileges; do not grant `DROP`, `GRANT OPTION`, or global privileges to the application account. Configure the generated secret and account name only in Apache/server environment variables: `HMS_ENV=production`, `HMS_DB_HOST`, `HMS_DB_USER`, `HMS_DB_PASSWORD`, and `HMS_DB_NAME`.

The dump contains a seeded administrator account with a legacy plaintext password. Use that account only for a local demo, then change its password immediately. The first successful login upgrades a legacy password to PHP’s `password_hash()` format. User and doctor MD5 passwords are also upgraded after successful login. Do not deploy the sample database or its personal and medical data to a public server.

## Project structure

```text
hospital/
  index.php                 Public landing page and contact form
  assets/                   Public-site CSS, JavaScript, fonts, and images
  hms/
    include/                Shared database, security, and patient UI includes
    admin/                  Administrator workflows and dashboard assets
    doctor/                 Doctor workflows and dashboard assets
    vendor/                 Bundled third-party browser libraries and demos
    *.php                   Patient registration, dashboard, appointments, and profile pages
SQL File/hms.sql            Existing database schema and local sample data
```

## Existing roles and features

- **Patient:** registration and login, profile/password changes, appointment booking/history, and linked medical-history pages.
- **Doctor:** login, profile/password changes, patient search and management, appointment history, medical-record entry, and prescriptions for patients with an assigned appointment or legacy doctor assignment.
- **Administrator:** doctor and specialization management, patient/user lists, appointments, contact queries, login logs, date reports, and editable hospital pages.
- **Public visitor:** hospital information, gallery, role login links, and a contact form.

## Security and quality improvements in this stage

- Centralized the three repeated DB connection files and added UTF-8 connection handling, environment overrides, and user-safe connection errors.
- Added shared prepared-query, output-escaping, input, session, authorization, password migration, and CSRF helpers.
- Added role-specific patient, doctor, and administrator session checks; fixed the malformed guards that rejected ordinary nonzero IDs.
- Regenerated the session ID after authentication, set HttpOnly/SameSite cookie options, enabled strict cookie-only sessions, and destroy sessions on logout.
- Replaced login SQL interpolation with prepared queries. Existing plaintext/MD5 passwords remain usable for migration and are rehashed on successful login; new registrations, password changes, and resets use `password_hash()`.
- Enforced a 10–72 byte length range for new passwords to avoid bcrypt’s 72-byte truncation behavior while retaining longer legacy passwords for login migration.
- Added CSRF fields to POST forms and validation to their POST handlers. Appointment cancellation and admin delete actions now use POST and check the acting role or record relationships.
- Scoped doctor patient searches and patient views to the signed-in doctor; scoped patient medical-history access to the signed-in patient account.
- Replaced remaining user-controlled SQL query parameters in the reviewed request handlers with prepared statements and added basic server-side validation.
- Escaped database-backed output and sanitized rich text used on the public About page.
- Disabled the bundled sample upload endpoints, which were not part of the hospital workflows.
- Kept the current schema and added small mobile table/form and focus-state CSS adjustments without redesigning the interface.

## Error logs

PHP errors are not rendered in the browser. Check the PHP/Apache error log from the XAMPP Control Panel or the configured `error_log` path when debugging locally. Database connection details and SQL errors are logged server-side rather than shown to users.

## Important limitations

- This remains a legacy page-oriented application; the stage did not replace its architecture or redesign its interface.
- Database changes are delivered only as explicit, additive SQL migrations. The application does not apply schema changes automatically; take a backup before importing any migration.
- Password migration is performed when an existing account logs in. Accounts that never log in retain their old database value until their password is changed or reset.
- Password recovery is still a local session workflow based on account details; it does not send a one-time link by email. Configure a verified email delivery flow before public deployment.
- The bundled sample SQL includes credentials and personal/medical demonstration records. Replace or remove them before any public deployment, and use least-privilege database credentials.
- XAMPP is a local development setup, not a production deployment recipe. Production deployment also requires HTTPS, protected secrets, backups, and a server-level review of Apache/PHP configuration.

## Hospital operations workspace

The administrator portal now uses a shared medical-blue interface and an operations dashboard backed by existing patient, doctor, and appointment data. The dashboard includes appointment status summaries, recent activity, department distribution, patient trends, and operational alerts. Existing patient, doctor, appointment, and clinical-record routes remain part of the original page-based application.

Administrator modules also include departments, beds and rooms, pharmacy inventory, laboratory orders, prescriptions, and invoices. They share a prepared-query, CSRF-protected form and table workflow. Each screen supports searching, creating records, and updating supported operational statuses or stock. These modules are gated to the existing administrator role. The installed application currently supports administrator, doctor, and patient authentication; additional staff roles need corresponding account and permission workflows before they can be enabled.

### Add the operational tables

The original `hms` dump does not contain the new operational data tables. Back up `hms`, then import [`SQL File/migrations/003_hospital_operations.sql`](SQL%20File/migrations/003_hospital_operations.sql) once into phpMyAdmin. The migration only creates new tables for departments, rooms, beds, pharmacy inventory, lab orders, invoices, and prescriptions; it leaves existing tables and records intact. The pages explain which migration is needed when their tables are missing. No schema changes run automatically.

The admin menu links to the new operations screens. To verify locally after the migration, sign in as an administrator, create a department and a room/bed, add a pharmacy item, create a lab order and prescription for an existing patient, and create an invoice. Then update a bed status, medicine quantity, lab result, and invoice status, and confirm the updated values appear in their tables and the dashboard alerts.

This remains a local portfolio application rather than a certified clinical or financial system. Add audit trails, comprehensive permission scopes for additional hospital staff, invoice printing/export, and review by qualified clinical/security stakeholders before production use.
