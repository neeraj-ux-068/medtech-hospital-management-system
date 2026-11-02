# MedTech Master Roadmap

## Phase 1 — Discovery (this deliverable)

Document current state, risks, target design, architecture, gaps, QA, and deployment. **Exit:** Project Owner approves scope and decisions.

## Approved architectural direction

The Project Owner approved a **gradual, parallel migration**: keep the legacy
PHP portals operational; build a React + TypeScript web client alongside them;
introduce a versioned PHP REST API at `/api/v1`; retain MySQL `hms` as the
source of truth; and defer React Native + Expo Android work until later. Each
feature migrates independently with a rollback path. No legacy portal is
retired until its replacement is proven by feature-level testing.

## Phase 2 — Foundation (4–6 weeks)

Create staging/test accounts/data; stabilize supported PHP runtime; introduce CI; resolve P0 security gaps; create API/OpenAPI contract; establish canonical design tokens/component library. The legacy PHP application remains the fallback.

## Phase 3 — Core web experience (8–12 weeks)

React shell/auth/profile, patient dashboard/appointments, doctor schedule/patient context/clinical workflow, admin operations dashboard. Replace routes gradually behind feature flags with acceptance tests and documented rollback to the preserved legacy route.

## Phase 4 — Clinical and financial depth (6–10 weeks)

Records, prescriptions, lab lifecycle, invoices/payments, notification preferences, print/documents, reporting. Add audit trail and full role/ownership coverage.

## Phase 5 — API hardening and mobile readiness (4–6 weeks)

Finish API parity, token/session strategy, rate limits, mobile contract tests, documentation, observability. Decide React Native + Expo scope only after API v1 parity, mobile governance approval, and a separate delivery decision.

## Phase 6 — Launch readiness (4–8 weeks)

Accessibility/security/performance testing, legal/privacy review, backup restore drill, pen test, production rehearsal, support/runbooks, controlled launch.

## Dependencies and approvals

- Product: scope of operations migration, reminders/channels, billing/insurance, inventory, interoperability/ABDM.
- Technical: hosting, secrets, queue, email/SMS provider, object storage, observability.
- Governance: privacy notice/consent, retention, access audit policy, incident response, legal review.
- Estimated effort assumes a cross-functional team; confirm estimates after workflow mapping and staging access.

## Revision note — 2026-11-02

The architectural direction is approved. Phase 2 must still satisfy
`PHASE_2_ENTRY_CRITERIA.md`; this discovery task does not authorize Phase 2 implementation.
