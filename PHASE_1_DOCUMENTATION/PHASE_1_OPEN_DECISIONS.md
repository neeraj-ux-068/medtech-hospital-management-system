# MedTech HMS — Phase 1 Open Decisions

The gradual, parallel migration strategy is approved. The following decisions remain open; none is assumed approved merely because it appears in a proposal.

| Decision | Why it matters | Required owner / evidence | Gate |
| --- | --- | --- | --- |
| API authentication and token model | Defines web/mobile session, revocation, and MFA boundaries. | Security/product threat-model approval. | Before protected API writes. |
| Privileged-user MFA | Required control before public launch. | Security/product owner; enrolment/recovery design. | Before launch. |
| Notification ownership transfer | Prevents API + trigger double sends. | Live trigger `SHOW CREATE` capture and ownership sign-off. | Before API notification writes. |
| Status-reversal event-key policy | Source design may collide on return to an earlier appointment/lab status. | Staging reproduction and approved design/migration if needed. | Before status-workflow changes. |
| Clinical/audit retention | `CASCADE` deletion may conflict with retention needs. | Clinical, legal/privacy, and operations review. | Before FK/delete-policy change. |
| Migration 003 scope | Operations migration is source-present but not confirmed as applied. | Business scope and live schema comparison. | Before migration plan. |
| Hosting/data region and vendors | Affects privacy, resilience, cost, support, and residency. | Owner budget and legal/operations decision. | Before production provisioning. |
| Queue/reminder, email/SMS, storage, observability | Each has privacy/security and failure-policy consequences. | Vendor, data-flow, cost, and failure-policy approval. | Before production use. |
| Audit-log policy | Defines clinical/financial access/change evidence and retention. | Privacy/legal/security policy. | Before production. |
| ABDM/FHIR/interoperability scope | Changes consent, data, and delivery scope materially. | Product/legal/program decision. | Before interoperability work. |
| Android start | Android is deferred pending secure API parity. | Owner approval after API v1 gates pass. | Before mobile implementation. |

## Evidence still required

- Complete live `SHOW CREATE TRIGGER` output for all seven triggers, including definers and bodies.
- Critical-index and aggregate orphan checks without retrieving private data.
- Dedicated staging accounts/data and a verified rollback process.
- Authenticated role, ownership, CSRF, and responsive test evidence.

These are decisions and verification needs, not claims that the current system has failed. No production-readiness claim follows until they are resolved.

