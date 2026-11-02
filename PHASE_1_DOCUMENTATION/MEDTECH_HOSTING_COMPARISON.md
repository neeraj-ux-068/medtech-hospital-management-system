# Hosting Comparison

Research date: 2026-11-02. Prices vary by region, billing account, tax, bandwidth, and usage; validate quotations before purchase. INR conversions below use planning assumption **₹84/USD**, not an exchange-rate quote.

| Provider/approach | Best fit | Verified price signal | Strengths | Limits / healthcare note |
| --- | --- | --- | --- | --- |
| AWS Lightsail + RDS/S3 | India-region production | Lightsail confirms Mumbai-region transfer allowances differ from bundles. [Lightsail pricing](https://aws.amazon.com/lightsail/pricing/) | Mumbai region, broad security/backup services, future scale. | Requires careful architecture, billing controls, managed DB/SMS pricing review; not automatically compliant. |
| DigitalOcean Droplet + Managed MySQL/Spaces | Developer-friendly professional | Droplets start at $6; managed database plans page lists database pricing; egress above allowance is billed. [Droplets](https://www.digitalocean.com/products/droplets) [DB](https://www.digitalocean.com/pricing/managed-databases) | Predictable UI, firewall/VPC, managed DB option. | Region/residency and managed MySQL plan selection need confirmation; not a low-cost replacement for security process. |
| Hetzner VM + self-managed MySQL | Cost-controlled pilot/non-sensitive use | CAX21 $12.49/mo in cited 2026 US price table; regions are Europe/Finland. [Hetzner](https://docs.hetzner.com/general/infrastructure-and-availability/price-adjustment/) | Strong compute value. | No India region in cited source; self-managed backups/DB/security; unsuitable default for real healthcare data without governance review. |
| Render container + external MySQL | Fast preview/pilot | Web service starts $7/mo; persistent disk $0.25/GB/mo; pricing is mainly Postgres-oriented. [Render pricing](https://render.com/pricing) | Easy deploy/TLS/preview. | Existing MySQL requires external managed DB; healthcare/region/BAA-style requirements must be separately evaluated. |

## Provider recommendation

- **Budget/portfolio:** single VM only with synthetic data; no claim of healthcare production suitability.
- **Professional small clinic:** India-region cloud, separate managed MySQL, object storage, backups, WAF/CDN, monitoring, and documented security operations.
- **Scalable multi-site:** multi-AZ managed database, private network, worker/queue, object store, WAF, observability, disaster recovery; obtain provider/legal assurance for data residency and contracts.
