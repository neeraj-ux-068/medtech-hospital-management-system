# Read-only API preparation and stop point

Status: plan only. Owner frontend review and API contract approval are required
before implementation. No backend endpoints or schema/data changes are made.

## Dependencies and decisions

- Confirm source schema via metadata-only inspection using approved access. The
  baseline SQL dump is absent from this checkout. Existing audit documents record
  aggregate verification and optional operations migration 003 as absent; this is
  historical evidence, not a fresh database inspection.
- Approve users.id as the initial account-directory identifier and labels that
  distinguish registered accounts from legacy patient profiles/unique people.
- Approve the minimal demographics contract and registeredAt timezone/null mapping.
  Do not invent DOB, active status, phone, department or clinical history fields.
- Approve admin-only Dashboard/Patients access; defer doctor/patient patient-data
  reads until stable ownership and care-relationship rules are reviewed.
- Approve same-origin session reuse, JSON bootstrap/error handling and synthetic
  negative-test accounts. Existing guards redirect and config failures emit text.
  Those patterns cannot simply be exposed as the documented JSON API unchanged.

## Proposed implementation sequence after approval

1. Introduce isolated API routing under /api/v1 with JSON status/header handling.
   Keep all existing PHP routes callable independently. No shared behavior change
   unless separately approved. Reject unknown paths/methods/query parameters.
2. Add session inspection plus deny-by-default API role middleware; revalidate the
   account without password upgrades or login-log writes. Release the session lock.
3. Add SELECT-only services for summary, account list/count, and demographic detail.
   Bind validated IDs/filters, allowlist sort mappings, cap pages/page size. No DDL,
   triggers, schema bootstrap, registration, clinical/billing queries or DML.
4. Test against an isolated disposable synthetic dataset, never production records.
   Preserve snapshots of the existing PHP behavior. Prefer a SELECT-only DB account
   for API reads if the environment supports a separately approved configuration.
5. Add typed frontend response adapters, request cancellation, loading/error/session
   states. Do not reuse fixture Patient as an assumed live DB model. Clear all
   in-memory patient data on session/role changes and 401. Never cache PHI in browser
   persistent storage. Keep fixtures explicitly separate from live mode.
6. Compare authorized counts and pagination on the synthetic database, then perform
   mobile/tablet/desktop and legacy portal regression checks. Stop before deployment.

## Required negative tests

- Signed-out/invalid/expired session: 401 JSON; no HTML redirect or protected data.
- Doctor/patient sessions: 403 for all initial dashboard and patient data endpoints.
- Removed account or unsupported role: deny; principal cannot be spoofed by client IDs.
- Admin patient ID changed to missing/invalid: 404/400 as specified; never a legacy
  profile lookup, password field, clinical join, or SELECT * output.
- Invalid dates, duplicate/unknown query keys, unsupported filters/sorts, oversized
  pages and SQL-injection-shaped values: safe validation; no SQL interpolation.
- GET/HEAD never enter login/write handlers; unsupported mutations return 405.
- All responses/errors are no-store JSON with safe request IDs; 429 has Retry-After.
- No count/list authorization divergence; no clinical/billing fields in serializers.
- Missing schema/DB failure: 503; no implicit migrations and no silent synthetic fallback.
- Existing patient/doctor/admin page routes, session settings, and CSRF behavior
  retain their existing behavior after any approved adapter implementation.

## Why live implementation stops here

The contract has not yet been reviewed, live schema/cookie behavior has not been
verified in this work package, and the existing page-oriented auth/config behavior
requires an approved JSON adapter. It is not safe to claim that dropping new routes
around those includes alone satisfies the contract. Frontend fixtures remain the
only data source until these gates are resolved.
