# MedTech HMS — Phase 2 CI and Automated Testing Plan

## Current state

No Git repository, CI workflow, Composer manifest, Node project manifest, or PHPUnit configuration was found. CI cannot be safely enabled until the Project Owner supplies/approves a canonical Git remote and a separate branch.

## Safest initial CI pipeline

1. **Repository hygiene:** protected branch, required review, no deploy credentials.
2. **Static PHP lane:** run the selected PHP binary against non-vendor PHP files using `php -l`; capture PHP 8.0 baseline and PHP 8.4 compatibility results separately.
3. **Secret/config lane:** scan tracked files for credentials; validate that production configuration has no fallbacks.
4. **Source checks:** verify state-changing route conventions/CSRF helper usage, role-guard imports, and unsafe SQL review list.
5. **Test lane (after staging):** PHPUnit or a lightweight dedicated test harness for helpers/services; integration tests against `hms_staging` only.
6. **Browser lane (after test accounts):** Playwright tests for public, patient, doctor, and admin paths at 360, 390, 768, 1024, 1366, and 1920 pixels.
7. **API lane (later work package):** OpenAPI lint, contract, authorization, idempotency, and negative BOLA/IDOR tests.
8. **No deployment job:** CI reports only until separately approved.

## Test priorities

| Priority | Test | Data | Pass condition |
| --- | --- | --- | --- |
| P0 | PHP lint and syntax | No DB | No syntax errors in selected non-vendor source |
| P0 | Auth/role/ownership denial | Dedicated staging roles | Cross-patient/cross-doctor and unsigned requests denied |
| P0 | CSRF/session/logout | Dedicated staging roles | Missing/invalid CSRF rejected; logout invalidates session |
| P1 | Appointment/lab status reversal | Synthetic staging records | Behavior recorded; no unapproved design conclusion |
| P1 | PHP 8.4 smoke/log comparison | Staging | No new fatal/deprecation blocker |
| P1 | Responsive role portals | Staging test accounts | No blocking overflow/inaccessible primary actions |
| P1 | Migration rehearsal | Staging copy only | Approved migration + rollback procedure succeeds |

## Proposed changes

| Change | Purpose | Files/systems affected | Risk | Rollback | Verification |
| --- | --- | --- | --- | --- | --- |
| CI workflow | Repeat read-only checks | New branch CI files | Low | Disable workflow | Job output reviewed |
| PHP test bootstrap | Enable helper/service unit tests | New test directory only | Medium test isolation | Remove branch files | Tests use staging/mock DB only |
| Browser E2E suite | Prove workflows/responsiveness | New test directory, staging only | Medium test data mutation | Reset staging from fixture | Run report/screenshots |
| API contract tests | Protect future `/api/v1` | Future API/test files | Low before API exists | Remove branch files | OpenAPI lint + negative tests |

## Approval required before implementation

Approve canonical Git hosting, CI provider, staging secrets/storage, test framework choice, and whether browser tests may create only synthetic appointment/clinical/financial records in staging. No CI or test framework is installed by this plan.

