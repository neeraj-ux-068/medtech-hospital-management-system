# Component Catalog

| Component | Roles | Contract |
| --- | --- | --- |
| AppShell | All | Header, responsive drawer/sidebar, content offset, breadcrumb/title, skip link. |
| RoleNav | All | Configuration-driven role menus; active route; permission-gated items. |
| ProfileMenu | All | Avatar initials/image fallback, account/security/logout actions. |
| PageHeader | All | Eyebrow, title, help text, primary/secondary actions. |
| StatusBadge | All | Semantic status + text; never color alone. |
| DataTable | All | Column schema, pagination, sort/filter, loading/empty/error, mobile scroll cue. |
| FormField | All | Label, hint, control, error, required marker, accessible association. |
| FormCard | All | Section heading, grouped fields, error summary, action footer. |
| ConfirmDialog | Doctor/Admin | Explicit irreversible/financial/clinical confirmation. |
| Timeline | All | Dated author/action events; immutable/audited states. |
| PatientContext | Doctor/Admin | Identity, alerts, ownership guard, recent care summary. |
| AppointmentStatus | All | State/action policy, event history, reschedule/cancel rules. |
| MoneySummary | Patient/Admin | Currency, totals, paid, balance; server-authoritative values. |
| NotificationList | All | Recipient-scoped list, unread/read state, deep link authorization. |
| ChartCard | Doctor/Admin | Accessible summary/table alternative, loading/empty state, responsive SVG/canvas. |
| PrintDocument | Patient/Admin | Print media, document metadata, no portal navigation. |

## Component rules

Tokens are centralized; no page-specific arbitrary offsets. Components accept data and permissions, not raw SQL. Every write action surfaces pending/success/error states and carries an idempotency strategy at API level.
