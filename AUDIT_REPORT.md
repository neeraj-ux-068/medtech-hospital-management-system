# MedTech web application audit

Audit date: 2026-11-01. Scope: existing PHP/MySQL application only. No mobile work, public deployment, data reset, or migration execution was performed during this audit.

## Inventory

- **Runtime:** PHP 8.0.30 with `mysqli`, `mysqlnd`, `mbstring`, `openssl`, `json`, and session support.
- **Source/deployment:** `hospital` is the project web root; `C:\xampp\htdocs\hospital` is the local XAMPP deployment.
- **Code:** 114 PHP files, 362 CSS files, and 1,572 JavaScript files.
- **Roles:** patient, doctor, administrator.
- **Shared components:** `hms/include/config.php`, `security.php`, `appointments.php`, `clinical.php`, `billing.php`, `analytics.php`, `notifications.php`, plus role-specific headers, sidebars, and footers.
- **Migrations reflected in the database:** appointment workflow, medical records, prescription/laboratory, billing/payments, and notifications. `003_hospital_operations.sql` is present in source but its operational tables are not installed.

## Implemented modules

- Patient registration, login, profile, appointments, medical history/records, prescriptions, laboratory results, invoices, receipts, and notifications.
- Doctor login, dashboard, appointments, patient management, clinical records, prescriptions, laboratory requests, notifications, and availability management.
- Administrator login, dashboards, doctors/users/patients, appointments, operations, laboratory, billing, reports, notifications, and availability management.

## Automated results

| Check | Status | Result |
| --- | --- | --- |
| PHP syntax | PASSED | All 114 PHP files pass `php -l`. |
| Source/deployment parity | PASSED | Checked core security, UI, landing, and doctor laboratory files match by SHA-256. |
| Protected routes | PASSED | 60 routes redirect signed-out users; sensitive detail/action routes deny access appropriately. |
| Database health | PASSED | `hms.doctors` is healthy after recovery; no orphaned appointment, lab, prescription, result, or payment records were found. |
| Operations migration | NEEDS APPROVAL | `003_hospital_operations.sql` has not been applied; its department, bed, pharmacy, and legacy operations tables are absent. |
| Billing reconciliation | PASSED | Current invoice payments equal recorded paid amount and balance. |
| Notifications | PASSED | Table, recipient/related indexes, unique event key, and seven triggers exist. |
| Upload demo endpoints | PASSED | The three jQuery upload sample endpoints return HTTP 404. |
| Authenticated portal workflows | NEEDS MANUAL VERIFICATION | No separate test credentials or accessible authenticated browser session were available. |
| Responsive visual testing | NEEDS MANUAL VERIFICATION | The earlier rendered 390 px landing/sign-in checks passed without horizontal overflow. The browser controller became unavailable before the requested 360, 768, 1024, 1366, and 1920 px checks could be rendered. |
| Public UI responsiveness | PASSED | Landing page and patient sign-in page were exercised at a 390 px viewport with no horizontal overflow; the collapsed navigation is available. |
| Core public routes and assets | PASSED | Landing, patient login/registration/recovery, doctor login, and administrator login return HTTP 200; shared CSS assets return HTTP 200. |
| Additional protected routes | PASSED | Patient, doctor, and administrator dashboards, laboratory, billing, and notifications redirect signed-out requests. |
| CSRF coverage scan | PASSED | No state-changing non-vendor PHP endpoint was found without `hms_verify_csrf`. |
| Schema relationship review | PASSED | Live schema contains the expected foreign keys across appointment, clinical, laboratory, prescription, billing, and payment data. |

## Fixes made during the stabilization pass

1. Recovered the unreadable `doctors` InnoDB table from the approved October 10 backup. The corrupted physical files and a logical backup of the remaining schema were retained in `stabilization-backup-20261016-095806`.
2. Updated the landing page so optional doctor statistics do not take down the public homepage.
3. Fixed unset session-message warnings on patient, doctor, and administrator pages.
4. Blocked direct execution of shared header include files with HTTP 404.
5. Added a blue MedTech fallback layer to the shared legacy stylesheet.
6. Rebuilt the doctor laboratory page around the normal doctor layout and assets while preserving its authorization, CSRF, queries, and request behavior.
7. Restricted XAMPP `root`/empty-password defaults to local development environments. Production now requires all database settings via `HMS_DB_*` environment variables.
8. Replaced all 45 legacy HTTP Google Fonts references with HTTPS.
9. Migrated the ten fixed read-only legacy list queries from `mysqli_query()` to `hms_db_query()` without changing their SQL results.
10. Aligned the Availability page with the blue MedTech portal shell, role-specific navigation, responsive mobile navigation, styled forms, and responsive tables.

## Remaining findings

| Severity | Finding | Recommended action |
| --- | --- | --- |
| High | A production least-privilege database account has not yet been created in the deployment environment. | Before production deployment, create the account and set `HMS_ENV=production` plus all `HMS_DB_*` variables in Apache. |
| Low | Availability's authenticated appearance requires browser confirmation. | Verify its sidebar, active state, and forms after signing in as doctor and administrator. |
| Low | The Apache log contains historic corruption errors from before the doctor-table recovery. | Rotate/archive the development log after confirming current authenticated workflows create no new errors. |

## Follow-up automated verification

The follow-up pass did not alter application or database data. It confirmed that the homepage and patient sign-in page render without horizontal overflow at a 390 px mobile viewport. It also confirmed public landing/patient/doctor/admin sign-in route rendering, protected Availability access control, zero remaining HTTP Google Font references, zero remaining non-vendor `mysqli_query()` calls, and no PHP lint failures after deployment. The browser controller became unavailable before the full six-viewport visual run; those remaining render checks are accurately retained as not tested.

The authenticated workflow rows above remain unverified by automation because every current live account password differs from the documented sample credentials. The audit intentionally did not reset passwords, inspect browser-stored credentials, create patient records, make appointments, or create clinical/financial test data merely to obtain access.

## Files modified in the final stabilization pass

- `hospital/hms/include/config.php` — production environment validation while preserving local XAMPP defaults.
- `hospital/hms/manage-availability.php` — MedTech portal layout and responsive navigation only; business logic is unchanged.
- `hospital/hms/admin/add-doctor.php`, `edit-doctor.php`, `doctor-logs.php`, `manage-patient.php`, `manage-users.php`, `manage-doctors.php`, `read-query.php`, `unread-queries.php`, and `user-logs.php` — fixed list queries now use `hms_db_query()`.
- `hospital/hms/doctor/edit-profile.php` — specialization list query now uses `hms_db_query()`.
- 45 existing PHP/CSS files — Google Fonts URLs changed mechanically from HTTP to HTTPS.
- `README.md` — XAMPP/production configuration and least-privilege account instructions.
- `AUDIT_REPORT.md` — final findings and evidence.

## Manual verification checklist

1. Sign in with separate patient, doctor, and administrator test accounts; confirm invalid login rejection and logout.
2. As patient: book an available slot, open history/details, cancel/reschedule where permitted, then view prescriptions, laboratory, medical records, billing, receipts, and notifications.
3. As doctor: confirm the appointment, create a record, prescription, and laboratory request for an authorized patient only; review availability and notifications.
4. As administrator: review dashboard counts, appointment management, users/doctors, laboratory status updates, invoice/payment totals, reports, notifications, and availability.
5. Attempt cross-patient and cross-doctor record URLs; access must redirect or deny access.
6. Check the listed workflows at 360, 390, 768, 1024, 1366, and 1920 px; check tables, sidebar, header, forms, and charts for overflow.
7. After testing, review `C:\xampp\apache\logs\error.log` for fresh PHP warnings or errors.

## Authenticated end-to-end verification matrix — 2026-11-01

**Evidence constraint:** local browser automation is disabled and no authorized patient, doctor, or administrator session/test credentials were available. No passwords were reset and no user, medical, appointment, laboratory, invoice, payment, or notification data was created or changed for this test pass. A status of **NOT TESTED** therefore means exactly that; it is not a failure inference.

### Patient portal

| Test | Status | Evidence |
| --- | --- | --- |
| Login / invalid login / logout | NOT TESTED | No authorized patient account session available; no credentials were reset or browser-stored credentials inspected. |
| Profile | NOT TESTED | Requires authenticated patient session. |
| Booking, cancellation, and rescheduling | NOT TESTED | Requires authenticated patient session and would create/change appointment data. |
| Medical history and records | NOT TESTED | Requires authenticated patient session. |
| Prescriptions, laboratory, billing, invoices, receipts, notifications | NOT TESTED | Requires authenticated patient session. |
| Signed-out dashboard, appointments, billing, notifications | PASSED | Direct requests redirect to `user-login.php`; direct medical-record route returns HTTP 403. |

### Doctor portal

| Test | Status | Evidence |
| --- | --- | --- |
| Login / logout, dashboard, appointments | NOT TESTED | No authorized doctor session or credentials available. |
| Appointment confirmation, clinical records, prescriptions, laboratory requests | NOT TESTED | Requires authenticated doctor session and changes clinical/appointment data. |
| Patient access restrictions, availability, notifications | NOT TESTED | Full ownership behavior requires authenticated doctor session. |
| Signed-out dashboard, appointments, laboratory | PASSED | Direct requests redirect to `doctor/index.php`. |

### Administrator portal

| Test | Status | Evidence |
| --- | --- | --- |
| Login / logout and dashboard statistics | NOT TESTED | No authorized administrator session or credentials available. |
| Patient/doctor management, appointments, laboratory | NOT TESTED | Requires authenticated administrator session; management actions may change data. |
| Billing/payments, reports, notifications, availability | NOT TESTED | Requires authenticated administrator session. |
| Signed-out dashboard, billing, reports | PASSED | Direct requests redirect to `admin/index.php`. |

### Authorization, session, and CSRF

| Test | Status | Evidence |
| --- | --- | --- |
| Signed-out protected-route enforcement | PASSED | Patient, doctor, and admin portal endpoints redirect to their own login pages; `medical-records.php` and `manage-availability.php` return HTTP 403. |
| Cross-patient ownership enforcement | NOT TESTED | Requires two authorized patient sessions. |
| Cross-doctor ownership enforcement | NOT TESTED | Requires two authorized doctor sessions. |
| Session expiration and logout invalidation | NOT TESTED | Requires an authenticated session. |
| CSRF rejection through rendered forms | NOT TESTED | Requires authorized sessions and state-changing forms. Static review previously confirmed no state-changing non-vendor endpoint without `hms_verify_csrf`. |

### Responsive verification

| Viewport | Status | Evidence |
| --- | --- | --- |
| 360 px | NOT TESTED | Browser automation unavailable. |
| 390 px | PASSED (public only) | Landing and patient sign-in were rendered previously with no horizontal overflow. Authenticated portals not available. |
| 768 px | NOT TESTED | Browser automation unavailable. |
| 1024 px | NOT TESTED | Browser automation unavailable. |
| 1366 px | NOT TESTED | Browser automation unavailable. |
| 1920 px | NOT TESTED | Browser automation unavailable. |

## Fresh log and direct-route evidence

On 2026-11-01, direct signed-out route requests for patient dashboard/history/billing/notifications, doctor dashboard/history/laboratory, and administrator dashboard/billing/reports were rechecked. Each redirected to the appropriate role login. `medical-records.php` and `manage-availability.php` returned HTTP 403 without a session. The latest Apache log review found only historical warnings/fatals timestamped before the deployed fixes; no new PHP warning, parse error, or fatal appeared from this pass.

## Doctor prescriptions UI repair — 2026-11-01

**Root cause:** `doctor/prescriptions.php` rendered a bare `<main>` beside the fixed 244px doctor sidebar. It therefore missed the shared `.main-content` desktop offset defined by the MedTech enterprise stylesheet. The page also omitted the standard doctor portal CSS and JavaScript assets, leaving header/profile sizing and mobile sidebar behavior to incomplete legacy defaults.

**Change made:** Rebuilt only the page presentation shell in `hospital/hms/doctor/prescriptions.php`. The page now loads the standard doctor assets, uses `.main-content` and `.wrap-content`, has a responsive two-column card layout, a scrollable prescriptions table, compact heading/header treatment, constrained profile image styling through the shared MedTech CSS, and a structured New Prescription form. On mobile the form stacks and the table scrolls inside its responsive wrapper.

**Business logic preserved:** the existing doctor role check, patient ownership check, CSRF validation, transaction, prescription/header-item inserts, maximum 12 medicine validation, redirect, and Add Medicine behavior were retained. No schema or database data changed.

| Test | Status | Evidence |
| --- | --- | --- |
| PHP syntax | PASSED | `C:\xampp\php\php.exe -l` reports no syntax errors. |
| Live deployment parity | PASSED | Source and `C:\xampp\htdocs\hospital\hms\doctor\prescriptions.php` SHA-256 hashes match. |
| Signed-out route protection | PASSED | Live route returns HTTP 302 to `doctor/index.php`. |
| CSRF/authorization/transaction code retention | PASSED | Source contains `hms_verify_csrf`, `hms_doctor_patient_allowed`, and transaction handling. |
| Prescription table/form visual rendering | NOT TESTED | Requires an authenticated doctor browser session; browser automation is unavailable. |
| Add Medicine and Save submission | NOT TESTED | Requires an authenticated doctor session and would create clinical data. |
| Six viewport visual checks and doctor-page regression | NOT TESTED | Browser automation is unavailable; no safe authenticated session exists. |

## Administrator and doctor portal UI standardization — 2026-11-01

**Confirmed shared root cause:** the portal sidebar is fixed at 244px and the enterprise design system offsets only `.main-content` on desktop. Several newer role pages bypassed that shell through an unstyled `main.container`, or omitted the role asset stack (`styles.css`, `plugins.css`, `medtech-app.css`, and `medtech-enterprise.css`). That produced sidebar overlap, default list/header styles, oversized profile images, and blank/disconnected content regions. This was a layout-asset integration issue, not a database or workflow defect.

**Shared components standardized:** administrator Laboratory and Billing now load the complete role asset stack; Analytics now loads the enterprise layout layer; root Notifications loads the enterprise layer for its administrator and doctor wrappers; and the legacy doctor pages that already use the standard shell now load the enterprise layer too. The feature pages retain their current PHP logic, queries, role checks, and CSRF behavior.

**Individual presentation work:**

- `admin/laboratory.php` — restored role assets and standardized responsive status/form panel behavior.
- `admin/billing.php` — restored role assets and standardized search, line-item grid, invoice creation form, buttons, and responsive wrapping.
- `admin/invoice.php` — added a consistent blue-and-white invoice document layout plus mobile and print media rules; the intentionally sidebar-free print flow remains intact.
- `admin/reports.php` — now uses the enterprise shell consistently with the dashboard.
- `notifications.php`, used by `admin/notifications.php` and `doctor/notifications.php` — now uses the enterprise shell consistently with the role header/sidebar.
- Doctor dashboard, appointment, patient, profile, search, and password pages — normalized to load the same enterprise design layer as the repaired doctor Laboratory and Prescriptions pages.

| Test | Status | Evidence |
| --- | --- | --- |
| Full PHP syntax suite | PASSED | All project PHP files passed `php -l` after the UI changes. |
| XAMPP deployment parity | PASSED | 14 changed administrator/doctor/notification files were copied to XAMPP; SHA-256 source/live hashes match. |
| Protected affected routes | PASSED | Administrator Laboratory, Billing, Invoice, Reports, Notifications and Doctor Notifications, Dashboard, Prescriptions return HTTP 302 when signed out. |
| Business-logic preservation review | PASSED | No SQL schema, MySQL record, authentication, authorization, CSRF, prepared-query, calculation, or workflow code was changed in this standardization pass. |
| Authenticated administrator/doctor visual regression | NOT TESTED | No authorized session and browser automation is unavailable. |
| Action-level laboratory, billing, invoice payment, report export, notification, prescription, and patient workflow tests | NOT TESTED | Require authenticated sessions and would risk modifying existing clinical/financial data. |
| Responsive rendered checks at 360, 390, 768, 1024, 1366, and 1920 px | NOT TESTED | Browser automation is unavailable. Existing public-only 390 px result remains recorded above. |

## Readiness

The database-backed application is stable for continued local verification, and its shared security and data-integrity controls are in place. It is **not yet production-ready** until a production least-privilege account is configured and authenticated/responsive workflow checks are completed. HTTPS font references and the reviewed legacy query conversions are complete.
