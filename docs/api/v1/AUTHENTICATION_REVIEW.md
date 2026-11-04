# Existing PHP authentication inspection

Read-only source review, 2026-11-03. No live account login, password change,
session manipulation, database query, or backend modification was performed.

## Evidence and behavior

| Portal | Handler | Account lookup | Session principal |
| --- | --- | --- | --- |
| Patient | hospital/hms/user-login.php | users.email | role=patient; id=users.id; login=email |
| Doctor | hospital/hms/doctor/index.php | doctors.docEmail | role=doctor; id=doctors.id; dlogin=email |
| Administrator | hospital/hms/admin/index.php | admin.username | role=admin; id=admin.id; login=username |

All three use shared hms_verify_legacy_password and hms_login in
hospital/hms/include/security.php. Prepared lookups are used. Successful login
regenerates the session ID, clears prior session fields, stores role/id, and creates
a random CSRF token. A login replaces the prior role in the same browser session;
these are not three independent simultaneous role sessions.

Existing plaintext/MD5 password compatibility upgrades to password_hash on login;
already-hashed passwords can also be rehashed when needed. Patient and doctor login
handlers write success/failure log rows. Thus even logging in to test a read-only
API can modify account/log records. No such test was executed against the database.

hms_start_session enables strict cookie-only sessions, disables URL session IDs,
sets HttpOnly and SameSite=Lax, and derives Secure from SERVER[HTTPS]. Cookie name,
path/domain, lifetime defaults, session storage and proxy HTTPS correctness depend
partly on server configuration; they were not live-verified. The helper returns
early when a session is already active, so API bootstrap ordering matters.

hms_require_role checks exact role and positive numeric ID, then redirects failures
to portal login pages. It is page-oriented and does not revalidate account existence.
hms_logout clears session data, expires the cookie, destroys the session, and
redirects. Patient/doctor logout handlers also update their latest log row.
The existing logout routes are GET-accessible; the future API should not duplicate
that behavior into a new mutating GET. A separately approved logout design should
use POST with CSRF while preserving compatibility of the existing application.

Admin/doctor include/config.php files delegate to shared include/config.php. It
sets server timezone, opens MySQLi with utf8mb4, and has environment-backed production
DB configuration. Its failure exits and exception handler emit plain text. The
prepared query helper can be reused by a future service; including whole page
handlers would execute unrelated behavior and is not an integration strategy.

## Security concerns

- Patient recovery grants a reset session after name/email matching; doctor recovery
  uses email/contact matching. Neither proves control of a recovery channel.
  Expiry and CSRF do not solve that account-ownership problem.
- No explicit idle/absolute session expiry, revocation registry, MFA, or login
  throttling is evidenced in the shared helper/login handlers. Server-level controls
  could exist; they have not been verified here.
- Numeric IDs overlap among account tables. Every authorization decision must use
  both role and ID. Never accept the acting principal from React or query parameters.
- Existing clinical helpers use appointment existence or mutable email linkage to
  legacy profiles. They need policy/identity review before new doctor/patient APIs.
- Exceptions can currently log stack/query-error details. New API logs must avoid
  patient values and use request IDs and safe operation codes.
- An existing browser session alone is insufficient to expose the entire patient
  directory; the initial proposed collection and dashboard are admin-only.

## Integration recommendation (proposal, not implemented)

1. Retain existing PHP login/password verification and legacy pages unchanged.
2. Serve frontend and /api/v1 through one reviewed HTTPS origin; configure only the
   frontend SPA fallback, leaving PHP/API/assets independent. Verify actual cookie
   and server session behavior using dedicated synthetic test accounts.
3. Add an API-specific JSON session/role adapter after contract approval. Inspect
   sessions without calling login pages; revalidate the principal's account and
   release the session lock before longer reads. No JWT/localStorage replacement.
4. Reuse prepared DB access behind explicit read-only services; provide JSON-safe
   connection and exception handling. Do not alter shared page guards to return JSON
   to existing page users. Any shared bootstrap extraction needs separate approval.
5. Return JSON 401/403 and let React display an expired/forbidden state with a fixed,
   allowlisted login destination. Never trust a caller-provided redirect URL.
6. Review recovery, timeout/revocation, proxy/cookie configuration and negative
   authorization tests before production exposure. No authentication change is
   authorized or included in this work package.

The current frontend remains an unauthenticated synthetic preview. Its administrator
label is explicitly a demo identity. Client routing is not security enforcement.
