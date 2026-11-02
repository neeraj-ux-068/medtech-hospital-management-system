# MedTech HMS — Proposed GitHub Repository File Inventory

## Inspection status

This inventory is based on local filename/metadata and targeted pattern scans. No secret, password, patient, clinical, billing, or database-record values are reproduced here. A final staged-file review is still mandatory.

## Include after review

| Path | Reason | Review requirement |
| --- | --- | --- |
| `hospital/` | Existing PHP application and web assets | Review image/upload-like assets; do not include runtime uploads if discovered. |
| `hospital/hms/include/` | Shared security, config, domain helpers | Keep; config is environment-based and contains no deployed secret value. |
| `hospital/hms/*/vendor/` | Existing application relies on vendored browser/PHP assets and has no root package manifest | Include only after license/vulnerability/sample-file review; do not remove blindly. |
| `SQL File/migrations/` | Versioned, additive schema history | Include; do not execute automatically. |
| `js/` | Candidate project JavaScript | Coordinator confirms this is source, not generated output. |
| Documentation files/directories | Project history, audits, phase plans | Include after checking references do not reproduce sensitive data. |

## Exclude absolutely

| Path/pattern | Reason |
| --- | --- |
| `SQL File/hms.sql`, `*.sql.bak`, `*.dump`, `*.dump.gz` | Database dump may contain accounts, patient, clinical, financial, and seeded credentials. |
| `stabilization-backup-*/`, `stage3-*-backup/`, `xampp-before-*/` | Original and recovery backups must never be uploaded. |
| `output/` | Generated image output; not source-of-truth. |
| `.env`, `.env.*` except `.env.example` | Local/private configuration. |
| `*.pem`, `*.key`, `id_rsa`, `id_ed25519` | Private key material. |
| `*.log`, `*.bak`, `*.tmp`, `*.cache`, `Thumbs.db`, `.sass-cache/` | Local/generated artifacts. |
| Runtime uploads/exports | Potential patient/personal data; no such custom directory was confirmed in this scan, so retain this exclusion defensively. |

## Findings requiring review

- No `.env`, private-key filename, Composer manifest, root Node manifest, PHPUnit config, or Git metadata was found.
- `SQL File/hms.sql` exists and must not enter the repository.
- Multiple original backup directories exist and must remain excluded.
- `output/` contains generated image assets and is excluded.
- Multiple `Thumbs.db` files and Sass caches exist and are excluded.
- Password-related patterns occur in expected authentication, password-reset, configuration, and documentation files. This is not proof of exposed credentials; perform a staged secret scan before upload.
- Vendor trees include third-party samples/demos. Preserve dependencies needed by the legacy app, but inventory and vulnerability/license-review them before the baseline push.

## Proposed pre-push acceptance

The clean Git copy may proceed only when its staged list equals the allowlist, `SQL File/hms.sql` and all backup paths are ignored, no real environment file is staged, and the technical coordinator signs the redacted secret/data scan result.

