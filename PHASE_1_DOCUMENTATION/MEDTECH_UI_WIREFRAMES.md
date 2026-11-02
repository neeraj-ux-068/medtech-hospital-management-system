# MedTech UI Wireframes

## Shared desktop shell

```text
┌────────────── 244px navigation ─────────────┬──────────── 72px compact header ────────────┐
│ MEDTECH / ROLE                              │ Page context · notifications · profile menu  │
│ Dashboard                                   ├──────────────────────────────────────────────┤
│ Role tasks / active item                    │ Eyebrow                                      │
│ ...                                         │ Page title                     [Primary action]│
│                                              │ ┌───────── card / table / form workspace ───┐│
└──────────────────────────────────────────────┴─┴────────────────────────────────────────────┘
```

## Shared mobile shell

```text
┌──────────────────────────────────────────────┐
│ [☰] MedTech            [bell] [profile]      │
├──────────────────────────────────────────────┤
│ Page title                         [Action]   │
│ ┌──────────── stacked cards/forms ──────────┐ │
│ │ tables scroll horizontally inside wrapper │ │
│ └──────────────────────────────────────────┘ │
└──────────────────────────────────────────────┘
Drawer: role navigation; closes on route/action selection.
```

## Patient screens

| Screen | Desktop low-fidelity arrangement | Mobile adaptation / states / access |
| --- | --- | --- |
| Login + registration | Split trust panel + credential card; registration is grouped Personal / Address / Account. | One column; password guidance and inline validation. Public only. Loading submit; invalid/success/error alert. |
| Dashboard | Welcome/action banner; 3 care KPI cards; Upcoming visit; care shortcuts; notifications preview. | Cards stack; appointment action first. Patient-owned data only. |
| Profile | Two-column contact/account form + security card. | One column; save footer sticky only while dirty. Success toast; field errors. Patient only. |
| Book appointment | Stepper: specialty → doctor → availability → reason → review. | One step per screen; calendar/slots full width. Loading slots; unavailable/duplicate/error states. Patient only. |
| Appointment history/details | Filter bar, status table; detail drawer/timeline with cancel/reschedule when allowed. | Table scrolls; detail is dedicated route. Empty “no appointments”; patient owns record. |
| Medical records | Timeline left; record detail/vitals card right. | Chronological cards. Redacted permission-denied state. Patient own records only. |
| Prescriptions | Prescription cards/table, issued date, doctor, View/Print. | Cards; print link. Patient own prescriptions only. |
| Laboratory results | Result timeline, status pill, reference range, download/print when authorized. | Stacked result cards. Patient own results only. |
| Billing/invoices | Summary (outstanding/paid), invoice table, receipt links. | Summary first; scrollable table. Read-only patient access. |
| Notifications | Unread filter, cards with timestamp/action, mark read. | Full-width cards. Patient recipient only. |

## Doctor screens

| Screen | Desktop low-fidelity arrangement | Mobile adaptation / states / access |
| --- | --- | --- |
| Login | Shared trust/login screen; doctor label and recovery link. | One column. Public only. |
| Dashboard | Today list; KPI cards; patient tasks; alerts; trend. | Today list precedes charts. Doctor-scoped metrics only. |
| Appointment calendar | Date controls, calendar grid, right-day agenda. | Agenda/list mode; date picker. Loading/empty/error; doctor own appointments. |
| Appointment details | Patient summary, status/actions, reason, activity timeline, notes. | Sections stack; confirm/reject only when allowed. Doctor ownership enforced. |
| Patient list | Search/filter, paginated table, patient quick context. | Search then cards; no cross-doctor patient access. |
| Patient medical history | Patient identity strip; timeline/vitals; record navigation. | Identity strip collapses; timeline cards. Authorized linked patient only. |
| Clinical record creation | Patient context left; structured note/vitals/follow-up form right. | Context summary first; grouped form. Draft/loading/error/save success states. |
| Prescription creation | Recent prescriptions table + 350px New Prescription card; Add medicine repeatable group. | Card stacks after table; table scrolls. Existing design target. |
| Laboratory requests/results | Request table + request card, result status chips. | Table scrolls; form full width. Authorized patients only. |
| Availability | Recurring schedule table; create rule / block date cards. | Agenda-style rows; drawer navigation. Doctor manages self only. |
| Notifications | Recipient-scoped cards, read controls, deep links. | Same shared pattern. Doctor recipient only. |

## Administrator screens

| Screen | Desktop low-fidelity arrangement | Mobile adaptation / states / access |
| --- | --- | --- |
| Login | Shared trust/login screen with administrator portal label. | One column; rate-limit/error states. Public only. |
| Dashboard | KPI row; exception alerts; operational charts; pending action tables. | KPI grid 1–2 columns; charts width-safe. Admin only. |
| Patient management | Search/filter, patient table, profile/detail action. | Search + cards/table scroll. Admin access logged. |
| Doctor management | Doctor directory, status/specialty filters, add/edit action. | Cards and modal/route forms. Admin only. |
| User management | Account table, role/status/security actions. | Horizontal table wrapper. Confirm destructive actions. |
| Appointment management | Filters, status summary, table/detail panel. | Filter drawer, scrollable table. Admin actions audited. |
| Laboratory management | Request cards/table with result/status form. | Status cards + full-width form. Admin only; result audit visible. |
| Billing/payments | Search, invoice table, invoice-create card. | Summary/cards then table scroll. Financial writes require confirmation/CSRF. |
| Invoice management | Print-friendly document: hospital mark, patient block, items, totals, payment history, print action. | One-column invoice; item table scrolls. No full sidebar in print mode. |
| Analytics/reports | Date/search filters; KPI cards; charts; report tables; CSV export. | Filters stack; charts responsive; tables scroll. Admin only. |
| Notifications | Unread summary, cards, mark-all/read actions. | Same shared pattern; admin recipient only. |
| Hospital settings | Grouped settings: profile, policy, integrations, notification policy; audit view. | Accordion sections. Requires explicit authorization/audit. |

## Interaction states required on every screen

- Loading skeleton for list/chart/card retrieval.
- Purposeful empty state with next permitted action.
- Inline validation and server error summary; never raw SQL/PHP errors.
- Success toast plus persisted status/timeline evidence for writes.
- Permission-denied state that does not reveal another patient/doctor record.
- Unsaved form warning, accessible focus movement, retry for transient failures.
