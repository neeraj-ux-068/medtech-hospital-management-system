# MedTech HMS — Phase 2 Environment Audit

## Verified local facts

| Area | Finding | Status |
| --- | --- | --- |
| Working tree | `C:\\Users\\neera\\Downloads\\MedTech-Hospital-Management-System-main` | Verified |
| Entry point | `hospital/index.php` | Verified |
| Application path | `hospital/hms/`; shared includes in `hospital/hms/include/` | Verified |
| PHP executable | `C:\\xampp\\php\\php.exe` | Verified |
| PHP version | 8.0.30, CLI | Verified |
| PHP extensions | `mysqli`, `mysqlnd`, `PDO`, `pdo_mysql`, `mbstring`, `openssl`, `curl`, `dom`, `fileinfo`, `session`, `zip` among installed modules | Verified |
| MariaDB | 10.4.32 | Project Owner live evidence |
| Database | `hms` accessible through phpMyAdmin at 127.0.0.1 | Project Owner live evidence |
| Source control | No `.git` directory or current branch | Verified blocker |
| Dependency manager | No application-level Composer/Node package manifest found | Verified |
| CI/test runner | No project CI workflow or PHPUnit configuration found | Verified |
| Backups | Named source/XAMPP backup folders exist, including `stabilization-backup-20261016-095806` | Verified existence; restore validity not tested |

## Security controls observed in source/audit

- Prepared-query helper, output escaping, CSRF helpers, role guards, ownership checks, strict cookie session settings, login session-ID regeneration, password hashing/legacy migration, and production environment-variable database configuration.
- Production requires `HMS_DB_*` settings; local root/empty-password defaults are restricted to development/local/test modes.
- Existing authentication/ownership execution remains incompletely verified because no safe dedicated test credentials/session were available.

## Outstanding P0 readiness items

1. Canonical Git repository and protected Phase 2 branch.
2. Staging environment with synthetic/anonymized data and isolated credentials.
3. Production least-privilege database account and secret-management implementation.
4. Authenticated two-user/two-doctor authorization evidence.
5. PHP 8.4 compatibility assessment; do not alter XAMPP until that completes.

## Migrations and database protection

Source has six numbered migrations. Prior documentation indicates 001, 002, 004, 005, and 006 are reflected; 003 remains source-present but unapproved/unconfirmed. Application code does not auto-run migrations. No migration was executed in this assignment. Complete live trigger bodies/definers, index checks, and aggregate orphan checks remain outstanding.

## Environment readiness conclusion

The codebase is suitable for **planning and branch setup**, not yet for safe implementation. Do not use the local `hms` instance as staging and do not infer backup recoverability from folder presence alone.

