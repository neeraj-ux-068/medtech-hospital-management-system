# Screen Inventory

## Route-to-screen inventory

| Role | Screen group | Existing evidence | Redesign disposition |
| --- | --- | --- | --- |
| Public | Landing, contact, role login, registration, recovery | `hospital/index.php`, `hms/*login*`, `registration.php` | Rebuild React presentation after API/auth plan; preserve flows. |
| Patient | Dashboard, profile, appointment booking/history/details, records, prescriptions, labs, billing, notifications | Root `hms/*.php`, audit module inventory | 10 screens specified in wireframes. |
| Doctor | Dashboard, history, patient search/manage/view/add/edit, records, prescriptions, laboratory, availability, notifications, profile/password | `hms/doctor/*.php`, root records/availability | 11 primary screens; consolidate patient context. |
| Admin | Dashboard, patients/users/doctors, appointments, laboratory, billing/invoice/receipt, reports, notifications, availability, contact/logs/settings-like pages | `hms/admin/*.php` | 12 primary screens; use operations workspace pattern. |

## Screen ownership

- Patient routes expose only patient-owned appointments, records, prescriptions, laboratory results, invoices, receipts, and notifications.
- Doctor routes expose doctor-owned appointments and clinically authorized patients.
- Administrator routes require administrative role and auditable writes.
- Print documents use a document shell, not the application sidebar.

## Responsive inventory

All screens must implement desktop 1366/1920, tablet 768/1024, and mobile 360/390 variants. A screen is not “responsive complete” until rendered tests prove navigation, forms, action buttons, charts, tables, and overflow behavior.

## Screen release order

1. Auth, shell, navigation, patient dashboard/booking, doctor dashboard/appointments, admin dashboard.
2. Patient/doctor clinical context, laboratory, prescriptions, billing/invoices.
3. Reports, notifications, settings, logs, print refinement.
