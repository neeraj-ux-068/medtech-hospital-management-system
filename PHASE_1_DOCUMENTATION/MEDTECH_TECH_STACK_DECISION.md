# Technology Stack Decision

## Recommendation

Adopt React + TypeScript for the new web experience, retain the existing PHP/MySQL system as the protected system of record initially, and introduce a versioned PHP REST API behind it. Evaluate Laravel only as a separately funded backend migration after the API contract and test suite stabilize.

## Why this fits

- React/TypeScript supports reusable role shells, accessible components, typed API clients, and gradual route-by-route replacement.
- PHP preserves working workflows, SQL knowledge, and the existing MySQL data model while reducing immediate migration risk.
- A REST API establishes one boundary for React and future React Native/Expo Android clients.
- MySQL remains authoritative; no data-copy platform is required at this stage.

## Gradual path

1. Freeze and characterize existing PHP workflows with tests.
2. Define OpenAPI v1 resources and ownership rules.
3. Extract read endpoints first (profile, appointments, records, invoices).
4. Add write endpoints with idempotency, validation, audit events, and transaction tests.
5. Deliver React portal slices behind feature flags; keep legacy routes as fallback.
6. Only then evaluate Laravel migration based on operational benefit, team skill, and API test coverage.

## Decisions

| Retain now | Replace gradually | Do not do yet |
| --- | --- | --- |
| MySQL data, PHP business rules, existing migrations, role concepts | Legacy view templates/CSS, direct form endpoints behind API equivalents | Big-bang rewrite, database replacement, mobile client, microservices |

## Costs and risks

Dual UI operation adds temporary maintenance cost. API extraction can reveal undocumented differences across pages. PHP 8.0 is end-of-life and must be upgraded on a tested compatibility path before public production. React adds build, dependency, CSP, monitoring, and deployment responsibilities.

## Deployment needs

HTTPS, supported PHP runtime, Node build pipeline, CI, secret manager/environment variables, least-privilege DB user, backups/restore tests, logs/metrics, staging, and rollback.

## Revision note — 2026-11-02

The exact selected/deferred libraries, costs/licenses, alternatives, and phase timing are in `MEDTECH_TECHNICAL_LIBRARY_MATRIX.md`.
