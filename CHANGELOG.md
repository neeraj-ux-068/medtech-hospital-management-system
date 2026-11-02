# Changelog

## Stage 3 — Modern UI/UX redesign (2026-10-07)

- Refreshed the public landing page with a healthcare SaaS hero, responsive navigation, appointment call-to-action, live specialty listings from the existing doctors table, service/about/contact sections, and a branded footer.
- Redesigned patient, doctor, and administrator portal foundations with a consistent color palette, typography, page surfaces, sidebar, top navigation, buttons, forms, tables, alerts, focus states, and mobile breakpoints.
- Added patient dashboard counts for upcoming appointments, appointment history, and available medical records, with shortcuts to the existing workflows.
- Added doctor dashboard counts for assigned patients, confirmed appointments and today's scheduled visits, plus a short upcoming-appointment list and record/profile shortcuts.
- Restyled the administrator dashboard with total users, doctors, appointments, patients, unread queries, and confirmed appointments. Its counts are fetched together, and the current schema does not distinguish pending from completed appointments, so those statistics were not fabricated.
- Improved public contact form labels, input types, autocomplete, accessible status/error messaging, login portal links, and image alternative text.
- Branded patient, doctor, and administrator sign-in pages plus patient registration and password-recovery screens consistently as MedTech.
- Updated shared portal branding and footer labels to MedTech and added active-page state to role navigation.
- Improved responsive layouts for navigation, forms, dashboards, and wide tables; added reduced-motion handling and stronger keyboard focus visibility.
- Added no libraries or external dependencies. Reused existing Bootstrap, jQuery and icon assets.
- No database schema changes and no existing user workflows were intentionally removed.

### Files created

- `hospital/assets/css/medtech-ui.css`
- `CHANGELOG.md`

### Existing files modified

- `hospital/index.php`
- `hospital/hms/dashboard.php`
- `hospital/hms/doctor/dashboard.php`
- `hospital/hms/admin/dashboard.php`
- `hospital/hms/user-login.php`, `hospital/hms/registration.php`, `hospital/hms/forgot-password.php`, `hospital/hms/reset-password.php`
- `hospital/hms/doctor/index.php`, `hospital/hms/doctor/forgot-password.php`, `hospital/hms/doctor/reset-password.php`
- `hospital/hms/admin/index.php`, `hospital/hms/admin/registration.php`
- `hospital/hms/include/header.php`, `hospital/hms/include/footer.php`
- `hospital/hms/doctor/include/header.php`, `hospital/hms/doctor/include/footer.php`
- `hospital/hms/admin/include/header.php`, `hospital/hms/admin/include/footer.php`
- `hospital/hms/assets/css/styles.css`
- `hospital/hms/doctor/assets/css/styles.css`
- `hospital/hms/admin/assets/css/styles.css`
- `hospital/hms/assets/css/themes/theme-1.css`
- `hospital/hms/doctor/assets/css/themes/theme-1.css`
- `hospital/hms/admin/assets/css/themes/theme-1.css`
- `README.md`

### Verification notes

- PHP syntax checks and static file/asset checks were run after the UI changes.
- Apache and MySQL were not listening in the development environment, so browser workflows and viewport screenshots were not available for this stage. Manual XAMPP verification is recommended before deployment.

### Known UI limitations

- The underlying application remains page-oriented and uses legacy Bootstrap/template markup. Some long legacy tables remain horizontally scrollable on small screens.
- Prescription, lab, and billing interfaces are not present in the current application and were not represented as available dashboard features.
# Stage 4 — Advanced appointment management

## Added

- Patient booking now selects a doctor, date, and server-generated available slot, and requires a visit reason. Requests are created as `pending` for doctor review.
- Doctor/admin availability controls support recurring ISO weekdays, working windows, slot minutes, per-slot capacity, and blocked dates. There are no implied default schedules.
- Appointment detail views show scoped patient/doctor information, reason, notes, status, and event history. Patients can request rescheduling; assigned doctors and admins can review requests.
- Doctors can confirm/reject pending visits and complete visits after their scheduled time. Patients can cancel eligible upcoming appointments. Admins can filter by doctor, patient name, date, and status and manage status from appointment details.
- Added additive migration `SQL File/migrations/001_appointment_workflow.sql`. It retains legacy appointment fields and rows, adds canonical status/reason/doctor notes, availability and unavailable-date tables, appointment event and reschedule tables, indexes, and foreign keys. It is not executed by the application.

## Security and compatibility

- Mutations use prepared statements, CSRF verification, role checks, and patient/doctor ownership scoping. Booking, reschedule requests, and approvals revalidate slots server-side and serialize slot contention with MySQL named locks.
- Existing appointment status flags remain synchronized for older pages. Existing rows map to confirmed only when both flags are 1; all other flag combinations map to cancelled.
- Booking requires the migration and at least one configured doctor availability rule. Back up first and import the migration once as described in README.

## Files created

- `hospital/hms/include/appointments.php`
- `hospital/hms/available-slots.php`
- `hospital/hms/appointment-action.php`
- `hospital/hms/appointment-details.php`
- `hospital/hms/manage-availability.php`
- `SQL File/migrations/001_appointment_workflow.sql`

## Files modified

- `hospital/hms/book-appointment.php`
- `hospital/hms/appointment-history.php`
- `hospital/hms/doctor/appointment-history.php`
- `hospital/hms/admin/appointment-history.php`
- `hospital/hms/dashboard.php`
- `hospital/hms/doctor/dashboard.php`
- `hospital/hms/admin/dashboard.php`
- `hospital/hms/include/sidebar.php`
- `hospital/hms/doctor/include/sidebar.php`
- `hospital/hms/admin/include/sidebar.php`
- `hospital/hms/assets/css/styles.css`
- `hospital/hms/doctor/assets/css/styles.css`
- `hospital/hms/admin/assets/css/styles.css`
- `hospital/hms/include/appointments.php`
- `README.md`

## Verification and known issues

- Static inspection was performed. PHP CLI, Apache, and MySQL were unavailable in the implementation environment, so no live database/browser workflow could be run and no migration was executed. Follow the manual Stage 4 workflow in the final report before relying on the feature.
- Slot capacity applies per generated slot; daily caps, overnight working windows, notifications, and a dedicated admin reschedule queue are not implemented.
# Stage 5 — Patient medical records and clinical history

## Added

- Added structured doctor-authored records for symptoms, diagnosis, clinical notes, treatment plan, follow-up, optional vital observations, record date, and optional appointment reference.
- Added role-aware clinical timelines: patients see their own modern records plus matching legacy history; doctors can see patients connected to their appointments/assigned legacy profiles and edit records they authored; admins can search/filter and view records read-only.
- Added patient demographic summary, appointment selector, responsive timeline cards, and admin filters for patient, doctor, date, and diagnosis.
- Added additive migration `SQL File/migrations/002_medical_records.sql` with foreign keys, indexes, and timestamps. It requires Stage 4 migration 001 and preserves `tblmedicalhistory` without copying or deleting data.

## Security

- Server-side role checks, ownership-scoped patient and record queries, CSRF protection, prepared statements, field/date/vital validation, and escaped output are used for clinical record operations.
- Fixed direct-ID exposure in legacy patient and doctor medical-history detail pages by verifying patient ownership/doctor assignment before reading history.
- Patients and admins cannot submit clinical record mutations; doctors can update only their own authored records for authorized patients.

## Files created

- `hospital/hms/medical-records.php`
- `SQL File/migrations/002_medical_records.sql`

## Files modified

- `hospital/hms/view-medhistory.php`
- `hospital/hms/doctor/view-patient.php`
- `hospital/hms/include/sidebar.php`
- `hospital/hms/doctor/include/sidebar.php`
- `hospital/hms/admin/include/sidebar.php`
- `README.md`

## Verification and limitations

- PHP syntax validation was performed on the new medical-record page and modified legacy detail pages. The database migration was not applied and the clinical CRUD/access workflows were not run against a live database in this environment.
- Legacy history rows are displayed when a matching registered `users.email` exists; records for standalone legacy patient profiles remain available through the existing doctor/admin legacy patient screens. No prescription catalog or medication ordering system was added.
