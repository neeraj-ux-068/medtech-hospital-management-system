# MedTech HMS — Phase 2 Foundation Execution Plan

**Scope:** planning/readiness only. No migration, runtime upgrade, deployment, database change, or application replacement is authorized by this document.

## Audit baseline

| Item | Finding | Evidence status |
| --- | --- | --- |
| Active source path | `hospital/index.php`; application under `hospital/hms/` | Local filesystem verified |
| Runtime | XAMPP PHP 8.0.30 CLI; PHP is not on the normal PATH | Local executable verified |
| Database | MariaDB 10.4.32; `hms` at 127.0.0.1 | Project Owner phpMyAdmin evidence |
| Database model | 25 InnoDB tables, 25 FKs across 13 tables, seven notification triggers | Project Owner phpMyAdmin evidence |
| Dependency manifests | No root Composer, Node, PHPUnit, or CI manifest found | Local filesystem verified |
| Source control | No `.git` directory found in this workspace | Local filesystem verified |
| Backups | Multiple named backup directories exist; no fresh database backup was created or verified in this pass | Local filesystem verified / backup validity outstanding |

## Work sequence

1. **Establish source control and branch protection.** Project Owner supplies/approves the canonical remote repository, imports this exact source snapshot, records an immutable baseline tag, and creates `phase-2/foundation`. Do not initialise an unrelated repository or edit from this unversioned workspace.
2. **Create isolated staging.** Copy application files to a staging path and restore only anonymized/synthetic data into a separately named staging database. Do not copy or query patient data for staging.
3. **Baseline tests.** Capture PHP lint, signed-out route checks, database schema aggregates, and a small non-production role/ownership smoke suite.
4. **PHP compatibility lane.** Install a side-by-side PHP 8.4 test runtime; do not alter the XAMPP PHP binary. Run lint, deprecation scan, and staging smoke tests.
5. **CI foundation.** Add a minimal pipeline only after branch setup: lint, secret scan, dependency manifest checks, and future test jobs. No deploy job.
6. **API/frontend preparation.** Add OpenAPI and React scaffolding only in a later approved work package after CI/staging and first-slice acceptance criteria exist.

## Proposed changes

| Proposed change | Purpose | Files/systems affected | Risk | Rollback | Verification |
| --- | --- | --- | --- | --- | --- |
| Canonical Git repository + `phase-2/foundation` branch | Isolate every Phase 2 change and preserve baseline | Source-control metadata/remote only | Medium: wrong baseline import | Re-clone/tag baseline; no source edits before verification | Clean status, remote URL approved, baseline hash/tag recorded |
| Separate staging app/database | Protect local `hms` and real records | New staging path, new DB only | High if wrong DB target | Delete only explicitly created staging environment after validation | DB name guard, no live credentials, synthetic-data checklist |
| Side-by-side PHP 8.4 runtime | Reach supported runtime without breaking XAMPP | Separate runtime/container/VM | Medium compatibility regressions | Continue using PHP 8.0 XAMPP; discard staging runtime | Lint + staging smoke + error-log comparison |
| CI workflow | Repeat baseline security/quality checks | New CI files only | Low; false failures | Disable workflow; no deploy capability | CI lint/scan output reviewed |
| React/API skeleton | Establish parallel path, not replace PHP | New isolated directories only | Medium dependency/scope drift | Remove branch-only skeleton | Build/type checks; legacy routes unchanged |

## PHP upgrade path

PHP 8.0.30 is unsupported. Official PHP support information lists PHP 8.4 with security support through 31 December 2028; PHP 8.4 is the recommended target for a staged compatibility lane. [PHP supported versions](https://www.php.net/supported-versions.php)

1. Create a separate PHP 8.4 environment with required extensions: `mysqli`, `mysqlnd`, `mbstring`, `openssl`, `curl`, `dom`, `fileinfo`, `zip`, and `session`.
2. Run syntax lint across all non-vendor PHP and capture errors/deprecations.
3. Run only staging/synthetic-data smoke tests: sign-out guards, login using dedicated accounts, read flows, and approved write tests.
4. Compare Apache/PHP logs and resolve compatibility issues in the branch.
5. Change no local XAMPP binary or production configuration until technical review approves a tested cutover/rollback plan.

## Implementation gate

Phase 2 implementation is blocked until canonical Git/branch setup and isolated staging are approved. The current workspace must not be treated as a safe branchable repository.

