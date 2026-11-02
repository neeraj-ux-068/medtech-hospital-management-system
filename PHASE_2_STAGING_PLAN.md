# MedTech HMS — Phase 2 Staging Plan

## Objective

Create a non-production environment that permits safe role, authorization, migration-rehearsal, and runtime tests without modifying the local working PHP app or the existing `hms` database.

## Isolation model

- **Application:** separate staging checkout/path, never `C:\\xampp\\htdocs\\hospital`.
- **Database:** separately named database such as `hms_staging`, with a separately named least-privilege user; never reuse the local `hms` connection settings.
- **Data:** synthetic seeded records or formally anonymized data only. Do not export raw patient, clinical, contact, password, token, or payment data.
- **Runtime:** side-by-side PHP 8.4 test runtime; no in-place XAMPP upgrade.
- **Access:** local/private network only, no public deployment. Dedicated patient, doctor, and admin test accounts.
- **Backups:** baseline backup plus restore rehearsal for staging only. Do not modify original backup folders.

## Creation sequence

1. Project Owner approves canonical Git repository, staging host/path, and synthetic-data approach.
2. Administrator creates isolated database/user and records only redacted connection metadata in secure configuration.
3. Create a fresh staging checkout from immutable baseline tag; configure staging-only environment variables.
4. Seed synthetic role/test data via an approved, reviewable process. Do not run existing feature migrations automatically.
5. Run signed-out route checks, role/ownership tests, and PHP 8.4 compatibility checks.
6. Before any migration rehearsal, take a staging backup, record schema version/evidence, apply only a specifically approved migration, test, and restore rehearsal.

## Proposed changes

| Change | Purpose | Affected files/systems | Risk | Rollback | Verification |
| --- | --- | --- | --- | --- | --- |
| Staging checkout | Isolate code changes | New staging directory | Medium wrong source | Delete explicitly created staging checkout | Baseline hash matches approved tag |
| `hms_staging` database | Isolate data | New DB only | High wrong target | Drop only after explicit target validation | DB guard; no connection to `hms` |
| Synthetic test fixtures | Permit E2E/security testing | Test-only staging data | Medium accidental realism | Recreate staging DB from empty approved fixture | Data review and account inventory |
| Side-by-side PHP 8.4 | Compatibility test | Staging runtime | Medium | Remove separate runtime | Lint/smoke/log results |

## Hard stops

Stop and obtain technical review if a command targets `hms`, an existing backup folder, a live XAMPP application directory, or any production credential. Staging availability is a prerequisite; it is not assumed to exist today.

