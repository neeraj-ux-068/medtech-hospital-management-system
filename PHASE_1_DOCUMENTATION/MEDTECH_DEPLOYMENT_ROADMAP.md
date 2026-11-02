# Deployment Roadmap

## Environments

- **Local:** XAMPP only; local sample database; never production credentials.
- **Staging:** isolated MySQL, anonymized seed data, production-like HTTPS/config, automated migrations only after review.
- **Production:** managed/server MySQL, restricted network access, secret manager, backups, monitoring, WAF/rate controls, documented on-call owner.

## Deployment sequence

1. Establish source control protections, CI lint/test/security checks, and an artifact build.
2. Upgrade PHP 8.0 on staging to a supported tested release; inventory compatibility.
3. Configure `HMS_ENV=production` and every `HMS_DB_*` secret using a least-privilege account; verify root fallback is impossible.
4. Back up and restore-test the database; record recovery point/time objectives.
5. Review each numbered migration and apply only approved migrations in order. Do not apply migration 003 merely because it exists.
6. Deploy with maintenance/health check and rollback artifact; run smoke tests.
7. Monitor error rates, authentication failures, transaction discrepancies, job failures, disk/DB health, and backups.

## Required production controls

TLS certificate with correct hostname, secure cookies, production error display disabled, redacted logs, file permissions, database firewall, dependency patching, CSP/security headers, and backups encrypted at rest and in transit.

## Rollback

Application rollback must not reverse an irreversible schema migration automatically. Every migration needs a reviewed forward/rollback or compensating plan, tested on staging. Financial and clinical corrections must be audited, not deleted.

## Revision note — 2026-11-02

Current provider comparison, scenarios, assumptions, and cost ranges are in `MEDTECH_HOSTING_COMPARISON.md` and `MEDTECH_DEPLOYMENT_COST_ANALYSIS.md`.
