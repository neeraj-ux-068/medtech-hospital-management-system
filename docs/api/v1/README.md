# MedTech HMS /api/v1 contract — draft for review

Status: DESIGN ONLY, 2026-11-03. No endpoint, rewrite rule, authentication adapter,
or database integration is implemented by this work package. The existing PHP
application remains independent. Read this contract with AUTHENTICATION_REVIEW.md
and READ_ONLY_IMPLEMENTATION_PLAN.md. openapi.json is the machine-readable contract.

## Endpoint conventions and scope

The logical base URL is `/api/v1`, independent of the React base path. Use lowercase
plural resource nouns, explicit HTTP methods, UTF-8 JSON, camelCase JSON fields,
and stable identifiers. GET and HEAD must never modify hospital/account records.
HEAD returns the same status/security headers as GET without a response body.
An unsupported method returns 405 with `Allow: GET, HEAD`; unknown routes return 404.
No request bodies, writes, login/logout, recovery, registration, deletion, clinical,
billing, or payment endpoints are approved by this draft.

| Method | Resource | Initial permission | Purpose |
| --- | --- | --- | --- |
| GET | /auth/session | Existing authenticated patient, doctor, admin | Minimal principal; never a session identifier/token |
| GET | /dashboard/summary | Admin only | Account/doctor/appointment aggregate counts |
| GET | /patients | Admin only | Paginated registered-account demographics |
| GET | /patients/{patientId} | Admin only | One registered-account demographic profile |

No role is inferred from URL paths, client route guards, request parameters, or a
client-supplied identity. All patient collection/detail endpoints return 403 for
patient and doctor sessions in this first contract. This avoids expanding legacy
email-based clinical access while stable identity linkage is unresolved. Signed-out
requests receive 401. No endpoint is public merely because it returns aggregates.

## Authentication and sessions

Reuse the existing server-side PHP session after explicit session adapter approval.
The preferred arrangement serves React and PHP API through the same HTTPS origin.
Keep current PHP login pages; do not repost credentials through React or implement
another password store. Patient, doctor, and admin accounts have separate ID
namespaces. The principal is `(role, id)`, never the numeric ID alone.

The current default cookie name is PHPSESSID unless server PHP configuration changes
it. Verify the actual name, cookie path/domain, HTTPS detection, and session storage
before integration. HttpOnly/Secure/SameSite settings must remain intact. Do not
read the cookie from JavaScript, return its value, or store tokens/patient payloads
in localStorage/sessionStorage. No bearer/JWT authentication is introduced.

GET session inspection must not invoke the login handler: existing successful
logins can rehash passwords and write login logs. No login or recovery test against
real accounts was performed in this work package. Close the PHP session lock after
copying verified principal fields. A session response is not a replacement for
endpoint authorization: every request repeats server-side checks. Confirm the
account still exists; unsupported roles are denied. If account-disable semantics
are added later, revalidate them on every request under a separately reviewed design.

Current page guards redirect to HTML and current config errors can exit with text.
The future API needs JSON-specific error/auth handling without changing the legacy
page behavior. Until that adapter and contract are approved, live integration stops.
CSRF tokens are not needed in these read-only response bodies. Any later mutating
endpoint needs a separately approved CSRF/header and origin-validation contract;
SameSite alone must not be treated as its complete protection.

## Response envelopes

Success: `{ "data": ..., "meta": { "requestId": "...", "mode": "live" } }`.
Collections also contain `meta.pagination` with page, pageSize, total, totalPages.
Error: `{ "error": { "code": "FORBIDDEN", "message": "Access denied." },
"meta": { "requestId": "...", "mode": "live" } }`.
Validation errors can add `error.fields`, a map from public parameter name to a
safe explanation. Do not echo rejected values, SQL, stack traces, session values,
passwords, or patient content into an error or log message. Request IDs must be
server generated and must not include identifiers or clinical information.

JSON strings are plain text; render them as React text, never as trusted HTML.
Use integer counts, date-only YYYY-MM-DD values, and ISO 8601 timestamps with a
numeric offset or Z. `registeredAt` may be null when a source date is unavailable;
do not fabricate a DOB or clinical visit from an account registration timestamp.
Use actual server HMS_TIMEZONE (default Asia/Kolkata) for the dashboard day and
return that timezone and day explicitly. If source registration timezone cannot
be established, expose registeredDate only after agreeing its interpretation;
the proposed registeredAt mapping is a pre-implementation verification gate.

## Patients: identifiers, ownership and minimization

`patientId` is the positive decimal string for `users.id`, not `tblpatient.ID` and
not the demo `PT-...` display identifier. Treat the string as an identifier; use a
validated integer bind parameter internally. Route IDs are limited to the verified
source ID range (initial draft: 1..2147483647; confirm base DDL before coding).

The collection returns only id, fullName, city, gender, registeredAt. Detail also
permits address and email to the administrator. No passwords, medical history,
notes, allergies, laboratory results, prescriptions, balances, or credentials are
selected. Do not use SELECT *. Do not join legacy profiles by email for this API.
The demo DOB, blood group, department, status, phone, lastVisit, allergies, and
emergencyContact fields have no approved persistent mapping in this contract.
The live UI must show unavailable fields honestly, not backfill them from fixtures.

Before any later patient self-service endpoint, constrain `users.id` to the session
patient ID in the SELECT itself. Before any doctor access, approve a stable care
relationship policy and constrain both collection/count and detail queries by it.
Any future denied/not-found detail should use the same 404 to prevent enumeration
within that authorized role. Current doctor helpers allow access through any
appointment or a legacy profile matching email; do not silently expand that policy.
Admin access is explicit operational demographic access, not clinical-write access.

## Filtering and pagination

`page`: positive decimal integer, default 1, maximum 10000.
`pageSize`: integer 1..100, default 20. `sort`: id, fullName, registeredAt;
`direction`: asc or desc (default desc); default sort id. Append id as a stable
secondary sort when needed. Map the sort token to an internal allowlist; never
interpolate arbitrary input as SQL identifiers.

`registeredFrom` and `registeredTo`: optional inclusive YYYY-MM-DD calendar dates.
Reject invalid dates or a reversed range. Use next-day-exclusive timestamp bounds
for the upper date, with a verified timezone policy. Both the total-count and data
query must apply identical filters and authorization. Pages beyond the total return
200 with an empty data array. Empty results have totalPages 0.

Name, phone, and email search are deliberately absent from this initial live
contract: the current synthetic React q parameter is not an approved live API
parameter. Query strings can enter history/logs. Decide and test a privacy-reviewed
search contract before enabling live patient search. Department and Active/Inactive
filters are also fixture-only. Unsupported query parameters and repeated scalar
parameters return 400; PHP parsing must not silently collapse repeated keys.

## Dashboard definitions

`registeredPatientAccounts`: COUNT(users), not unique clinical people or COUNT(tblpatient).
`doctors`: COUNT(doctors), not an unverified active-doctor measure.
`appointmentsToday`: COUNT(appointment) for the reported hospital calendar day,
including all statuses. Counts are nonnegative integers. This contract exposes no
revenue, percentage growth, clinical content, recent-patient list, or schedule rows.
These remain explicitly synthetic/unavailable until separate contracts are reviewed.
Use consistent read-only aggregate queries; do not merge live figures with demo
figures under the same live-data label. Schema/config unavailable means 503, not a
successful response containing invented zero counts.

## HTTP/security/error policy

All responses, including errors: Content-Type application/json; charset=utf-8,
Cache-Control no-store, X-Content-Type-Options nosniff, Referrer-Policy no-referrer,
and X-Request-ID matching meta.requestId. Do not enable wildcard CORS or JSONP.
Credentialed cross-origin operation requires a separate origin/cookie review.
Do not make API/SPA fallback rules intercept legacy PHP routes or static assets.

| Status | Code | Meaning |
| --- | --- | --- |
| 400 | INVALID_REQUEST | Invalid/unknown/repeated query or malformed identifier |
| 401 | UNAUTHENTICATED | Missing, expired, invalid session or removed account |
| 403 | FORBIDDEN | Authenticated role not allowed |
| 404 | NOT_FOUND | Unknown route or missing allowed resource |
| 405 | METHOD_NOT_ALLOWED | Method outside the resource contract |
| 429 | RATE_LIMITED | Server limit exceeded; include Retry-After |
| 500 | INTERNAL_ERROR | Unexpected failure; safe message only |
| 503 | SERVICE_UNAVAILABLE | Required database/schema/configuration unavailable |

Perform authentication/role checks before querying patient existence. Validate
parameters after authorization. Rate-limit anonymous session probing and protected
requests by an approved policy; choose limits in staging, not by undocumented
client-side throttles. Return Retry-After as integer seconds for 429/503 when known.
No API consumer may supply a database/table name or execute SQL through the API.

## Versioning and implementation gate

Additive optional fields can evolve within v1; clients ignore unknown response
fields. Removing fields, changing meaning/types or widening permissions requires
contract review and usually a new major version. openapi.json describes intended
live responses only. Synthetic fixtures stay local and never silently replace a
failed live request. Review and approve the contract, demographic field mapping,
session adapter, and read-only negative-test plan before any API implementation.
