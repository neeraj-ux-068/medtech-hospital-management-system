# Backend API Design

## API contract

Base path `/api/v1`; JSON UTF-8; ISO 8601 timestamps; money as decimal strings; cursor/page metadata; `application/problem+json`-style error code/message/field errors/correlation ID. Publish OpenAPI 3.1 before implementing endpoints.

## Resources

`auth`, `me`, `patients`, `doctors`, `appointments`, `availability`, `medical-records`, `prescriptions`, `laboratory-requests`, `laboratory-results`, `invoices`, `payments`, `notifications`, `reports`.

## Safety rules

- Authenticate every protected request.
- Perform role and record ownership checks in PHP domain services for every resource ID (BOLA/IDOR prevention).
- Allow-list writable fields; never bind request payloads directly to DB columns.
- CSRF applies to cookie-based web flows. Native token clients use authorization headers and refresh-token rotation.
- Write endpoints validate idempotency key/request fingerprint; transactionally create related clinical/financial records.
- Log audit event metadata, not secrets/clinical payloads beyond policy.

## Auth transition

Legacy PHP sessions remain for old pages. New API supports short access token + rotated refresh token/device session model after threat model approval. Administrators require MFA before public launch. Rate limits apply to login, reset, search, exports, and writes.

## Jobs

Reminder and notification jobs run through an idempotent worker. Existing database notification triggers must be mapped before API producers are added to prevent duplicate alerts.

## Laravel decision

Do not migrate to Laravel in Phase 2. First characterize PHP rules and API contract. Re-evaluate when tests cover critical behavior and the expected benefits (routing, validation, queues, policy support) exceed migration cost.
