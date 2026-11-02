# MedTech HMS — Phase 2 Security P0 Checklist

## Existing controls to preserve

- [x] Prepared-query helper and output escaping in shared security layer.
- [x] CSRF token generation/verification for state-changing forms (source/audit evidence).
- [x] Role guards, record-ownership checks, strict cookie session settings, and login session regeneration (source/audit evidence).
- [x] Password hashing for new/reset passwords and legacy-password migration on successful login (source/audit evidence).
- [x] Local-only XAMPP default DB credentials; production requires `HMS_DB_*` (source/audit evidence).

## P0 gates before protected API or staging write work

| Control | Required action | Evidence / test | Approval |
| --- | --- | --- | --- |
| Secrets/DB privilege | Define staging and production secret injection plus least-privilege accounts; never commit credentials | Configuration review with redacted values | DevOps/security |
| Authorization | Test patient-to-patient, doctor-to-doctor, and role-crossing denial with dedicated accounts | Automated negative tests | Security/QA |
| CSRF/session | Rendered-form rejection test, logout invalidation, session fixation and expiry tests | Staging results | Security/QA |
| API security | Threat model, allow-list schemas, auth strategy, rate limit/idempotency plan | Approved OpenAPI/security review | Security/product |
| Notification integrity | Capture live trigger definitions before API duplicates trigger-owned events | Read-only trigger evidence | DBA/security |
| Runtime | PHP 8.4 compatibility lane, current patch level, extension parity | Staging lint/smoke/log comparison | Technical coordinator |
| Logs/errors | Redacted logs; no raw DB errors/credentials/clinical content | Log review | Security/operations |

## Proposed changes and safeguards

| Change | Purpose | Affected files/systems | Risk | Rollback | Verification |
| --- | --- | --- | --- | --- | --- |
| CI secret scan and PHP lint | Detect accidental secret/code regressions | New CI workflow only | Low false positives | Disable branch workflow | Review scan/lint output |
| API request/authorization test harness | Prevent BOLA/IDOR regressions | New test-only code | Medium incomplete coverage | Remove branch-only harness | Negative ownership tests |
| Environment template without values | Document required variables without secrets | New example documentation only | Low | Revert file | Secret scanner and review |
| Side-by-side runtime test | Identify PHP 8.4 regressions | Staging runtime only | Medium | Retain PHP 8.0 XAMPP | Lint, logs, smoke tests |

No existing security control may be disabled to pass a test. No real clinical, financial, or patient data may be used in the test plan.

