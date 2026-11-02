# Deployment Cost Analysis

## Assumptions

Research date 2026-11-02; ₹84/USD planning conversion; taxes excluded. 100/500/2,000 MAU means low/moderate/growing clinic use, 2/8/30 GB monthly documents, 20/100/400 GB retained object storage, and 1/5/20k OTP/SMS events. SMS/OTP, domain, email, Android fee, labor, and compliance quotes vary materially and are shown as estimates/TBD—not provider facts.

## Monthly infrastructure planning ranges

| Scenario / MAU | Fixed infrastructure | Usage-based allowance | Monthly INR | Annual INR | Initial setup |
| --- | ---:| ---:| ---:| ---:| ---:|
| A Budget pilot / 100 | $25–55 | $10–35 | ₹2,940–7,560 | ₹35k–91k | ₹25k–75k labor/domain/config; synthetic/controlled data only |
| B Professional / 500 | $180–450 | $50–200 | ₹19,320–54,600 | ₹232k–655k | ₹150k–500k security, staging, migration rehearsal, monitoring setup |
| C Scalable / 2,000 | $800–2,500 | $250–1,200 | ₹88,200–310,800 | ₹1.06m–3.73m | ₹600k–2m architecture/DR/observability/security work |

## What is included

| Cost item | A | B | C | Notes |
| --- | --- | --- | --- | --- |
| Domain + TLS | domain estimate + free ACME TLS | same | same | Domain registrar quote required; TLS certificates can be free but operations are not. |
| Compute/PHP | 1 VM | app + worker/staging | redundant app/worker | DO $6 starting VM signal; Render $7 service signal; actual size must be load-tested. |
| MySQL | same VM only (risk) | managed DB | HA managed DB | DO managed DB pricing signals start about $15.15/mo for cited 1GiB PostgreSQL; MySQL must be quoted/configured separately. |
| Storage/backups | snapshots/object store | versioned object + PITR | cross-region/immutable copy | Include restore drills, not only storage. |
| WAF/CDN | basic/free tier possible | paid/security tier | enterprise | Provider/traffic dependent. |
| Email/SMS | low test allowance | transactional provider | negotiated volume | SMS/OTP often dominates India usage; obtain vendor quote. |
| Monitoring/error tracking | free/basic | paid team | enterprise/on-call | redact health/financial data. |
| CI/CD | free allowance possible | paid runner/storage possible | dedicated runners | GitHub Actions usage varies. |
| Android publishing | N/A until release | Google Play fee/TBD | same | Verify current Play policy/fee at purchase. |

## Important caveats

Lowest-cost shared hosting is inappropriate for real healthcare production because secrets, patching, backups, network isolation, incident response, and observability remain unfunded. These estimates exclude engineering maintenance: budget 0.25–0.5 FTE pilot, 1–2 FTE professional, and an on-call/security/operations team at scale. Use provider calculators and signed regional quotes before approval.
