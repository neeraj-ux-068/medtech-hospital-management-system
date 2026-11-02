# Security Strategy

## Current evidence

The audit records prepared query helper usage, output escaping, CSRF helpers, strict cookie sessions, session regeneration on login, role checks, password hashing/migration, role/ownership checks, and disabled bundled upload demos. It also records incomplete production configuration and missing authenticated testing. These are strengths, not a compliance certification.

## Priority risks

| Priority | Risk | Remediation |
| --- | --- | --- |
| P0 | Production secrets/DB account | Use secret-managed `HMS_DB_*`, least privilege, rotation, no root fallback in production. |
| P0 | Unverified ownership and role paths | Two-user/two-doctor automated authorization tests for every object endpoint/page. |
| P0 | Public API introduction | API gateway/rate limits, object-level authorization, scoped tokens, schema allow-lists, contract tests. |
| P1 | Clinical/financial auditability | Append-only audit events for view/export/modify, correlation IDs, retention policy. |
| P1 | Uploads/attachments | Dedicated object storage, content allow-list, size limit, malware scan, signed download authorization. |
| P1 | Availability/resilience | Encrypted backups, restore drills, monitoring, incident runbook, least-privilege jobs. |

## Standards and legal context

Use OWASP ASVS 5.0 as the engineering verification baseline and OWASP API Security guidance for the future API. [OWASP ASVS](https://asvs.owasp.org/) [OWASP API Security](https://api-security.owasp.org/). India’s Digital Personal Data Protection Act, 2023 establishes a framework for processing digital personal data; effective dates and healthcare-specific obligations require qualified legal review. [DPDP Act](https://www.meity.gov.in/writereaddata/files/Digital%20Personal%20Data%20Protection%20Act%202023.pdf). ABDM health-data policy materials emphasize consent-oriented health data handling; scope/integration must be validated with legal and program guidance. [ABDM policy](https://abdm.gov.in/static/media/health_management_policy_bac9429a79.80f74bc3e039c00acd4f.pdf).

## Required controls before launch

HTTPS/HSTS, security headers/CSP, supported runtime patching, MFA for privileged roles, password reset abuse controls, rate limits, encrypted backups, centralized redacted logs, vulnerability patch process, penetration test, data-processing/retention policy, consent and breach-response process. Do not claim DPDP, ABDM, HIPAA, or any certification compliance without qualified assessment.

## Revision evidence — 2026-11-02

Live aggregate evidence confirms seven notification triggers and no duplicate or null event keys among the 12 notification rows present at verification time. This is a point-in-time data observation, not proof that every trigger path is idempotent. The source trigger design can still reject appointment/laboratory updates that return to a previously notified status because its unique event key is status-based and the trigger insert lacks a conflict handler. This requires staging reproduction and approved remediation before production.

## Revision note — 2026-11-02

API/mobile token, audit, and staging-security requirements are now cross-referenced by the API and Android plans.
