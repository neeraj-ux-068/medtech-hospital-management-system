# MedTech HMS — Phase 1 Risk Register

| ID | Risk | Evidence classification | Likelihood | Impact | Mitigation / next action | Owner |
| --- | --- | --- | --- | --- | --- | --- |
| R-01 | Trigger body/definer drift from migration | Live metadata exists; complete live definitions outstanding. | Medium | High | Capture read-only `SHOW CREATE TRIGGER`; compare with migration before API notification work. | DBA / security owner |
| R-02 | Appointment/lab status reversal aborts update | Source-code finding; not live-reproduced. | Medium | High | Reproduce in staging; adopt conflict-safe design only through approved migration. | Backend / product |
| R-03 | `CASCADE` removal conflicts with clinical/audit retention | Delete actions live-verified; clinical impact unreviewed. | Medium | High | Retention/legal/recovery review; test approved alternative on staging. | Clinical / legal / DBA |
| R-04 | API and triggers duplicate notifications | Source-code finding; API not implemented. | Medium | Medium | Maintain producer matrix and idempotency tests. | Backend |
| R-05 | Broken role/ownership path leaks data | Existing controls are source/audit findings; E2E proof outstanding. | Medium | Critical | Two-user/two-doctor authorization suite before release. | Security / QA |
| R-06 | Production secrets or excessive DB privileges | Production configuration unverified. | Medium | Critical | Environment secrets, least privilege, rotation, no production root fallback. | DevOps / security |
| R-07 | Migration damages existing data | Proposed approach; no rehearsal evidence. | Medium | High | Staging copy, backup, rehearsal, verified rollback, additive migrations only. | DBA / release owner |
| R-08 | Legacy/React parity regression | Target design proposal. | Medium | High | Feature flags, acceptance suite, observability, legacy-route rollback. | Product / frontend / QA |
| R-09 | Hosting/residency/operations inadequate | Planning estimates only. | Medium | High | Select region/vendor, backups, monitoring, WAF, runbooks, legal review. | Project Owner |
| R-10 | Android handles health data insecurely | Future proposal; mobile not started. | Low | High | Delay until API parity; secure storage, minimal payloads, revocation tests. | Mobile / security |

Likelihood and impact are discovery assessments, not measured incident rates. No risk is closed without documented evidence and owner sign-off.

