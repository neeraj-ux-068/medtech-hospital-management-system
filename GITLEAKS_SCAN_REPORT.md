# MedTech HMS — Gitleaks Scan Report

## Tool provenance and integrity

- Tool: Gitleaks 8.30.1, official gitleaks GitHub release.
- Distribution: Windows x64 release archive.
- Integrity: local SHA-256 matched the checksum published in the official release checksum file.
- Verified SHA-256: d29144deff3a68aa93ced33dddf84b7fdc26070add4aa0f4513094c8332afc4e.
- Scanner binary and raw JSON reports were held outside the repository; no scanner artifact remains in the clean copy.

## Scan commands and scope

1. Complete clean-copy content: Gitleaks detect with no Git history and redacted JSON reporting.
2. Exact staged baseline: Gitleaks protect with staged changes and redacted JSON reporting.

## Results

| Scan | Exit status | Findings |
| --- | ---: | ---: |
| Complete clean-copy directory | 0 | 0 |
| Exact staged baseline, before Work Package 1C reports | 0 | 0 |

No secret values were displayed. No potential secret finding requires investigation from these two scans.

## Limitation

The staged baseline scan predates the four Work Package 1C deliverables. After these documents are staged, the staged set must be scanned again; its result is recorded in FINAL_PUSH_READINESS.md. No commit or remote exists.

