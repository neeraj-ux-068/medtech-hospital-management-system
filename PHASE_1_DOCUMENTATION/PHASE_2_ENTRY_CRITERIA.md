# MedTech HMS — Phase 2 Entry Criteria

Phase 2 is a controlled foundation phase for the approved parallel migration. It does not authorize PHP retirement, database replacement, Android implementation, or production deployment.

## Required before Phase 2 work is authorized

- [ ] Project Owner approves the final master report, open decisions, and risk treatment/ownership.
- [ ] A non-production staging environment and dedicated test roles/data exist; no real clinical or financial data is used for exploratory tests.
- [ ] Backup/restore and route-level rollback are documented and exercised for migration work.
- [ ] PHP runtime/support posture, `HMS_DB_*` production-secret model, and least-privilege DB-account approach are reviewed.
- [ ] CI baseline includes PHP lint, dependency/security checks, and a place for API/React tests.
- [ ] OpenAPI v1 scope, error format, role/ownership model, and idempotency policy are reviewed before endpoints.
- [ ] A notification producer matrix is approved. Live trigger bodies/definers are captured, or affected API notification writes remain out of scope pending capture.
- [ ] A clinical retention review owner/process is assigned for FK `CASCADE` paths.
- [ ] The first feature's acceptance criteria, feature flag/route switch, and legacy rollback are documented.

## Required before the first protected write is released

- [ ] Authentication, CSRF/cookie policy, token/session plan, rate limits, and object-level authorization are threat-model reviewed.
- [ ] Tests cover patient/doctor/admin separation, two-user/two-doctor ownership denial, invalid input, duplicate submissions, and transaction rollback.
- [ ] The first read/write feature passes desktop/mobile acceptance checks and preserves a working legacy fallback.

## Required before any production release

- [ ] No open P0/P1 security defect exists without formal risk acceptance.
- [ ] Migrations are additive, rehearsed on staging, backed up, and rollbacked.
- [ ] Authenticated E2E, authorization, accessibility, responsive, and security tests pass with recorded evidence.
- [ ] Restore drill, monitoring/alerting, redacted logs, incident process, and operations runbook are verified.
- [ ] Hosting/data region, vendor contracts, privacy/consent/retention policy, and legal review are approved.

## Current entry decision

**NOT YET AUTHORIZED TO START PHASE 2 IMPLEMENTATION.** The strategic direction is approved, but the unchecked gates above require explicit approval or evidence. This preserves the existing PHP application and avoids treating Phase 1 discovery findings as production proof.

