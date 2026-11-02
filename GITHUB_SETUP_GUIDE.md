# MedTech HMS — Safe GitHub Private Repository Setup

## Scope and hard rules

This is a procedure for a **new private GitHub repository** named `medtech-hospital-management-system`. It does not create the repository, initialise Git in the existing workspace, upload files, enable deployment, or push a commit.

The current directory has no `.git` metadata. Do **not** run `git init` there. Do not use the local `hms` database or any backup folder as a source for Git.

## Exact snapshot to preserve

Prepare a new, clean staging copy from the current working snapshot with this allowlist:

- `hospital/` application source and required static assets, after the asset/vendor review in the inventory.
- `js/` only after confirming it contains project source rather than generated output.
- `SQL File/migrations/` only; **exclude** `SQL File/hms.sql`.
- Project documentation: `README.md`, `CHANGELOG.md`, `AUDIT_REPORT.md`, `TRIGGER_AUDIT.md`, `DEPLOYMENT_CHECKLIST.md`, `PHASE_1_DOCUMENTATION/`, and approved Phase 2 planning documents.
- `.gitattributes` after review.

Never copy: backups, XAMPP deployment copies, database dumps, `output/`, cache files, local environment files, private keys, logs, uploads, or data exports.

## Review and setup procedure

1. Project Owner creates the private GitHub repository with no README, license, gitignore, deployment environment, Actions secrets, Pages, or public visibility.
2. Create a new empty folder **outside** the existing project and copy only the approved allowlist into it. Preserve the original workspace untouched.
3. Add the reviewed `PROPOSED_GITIGNORE` content as `.gitignore` and use the reviewed `.env.example`; do not create a real `.env`.
4. Run file-name and secret scans in the clean copy. Manually review all image/upload-like assets and all vendor content selected for inclusion.
5. In the clean copy only: initialise Git, set the approved remote, create `main`, make the reviewed baseline commit, then create `phase-2/foundation`.
6. Before any push, run `git status --ignored`, `git check-ignore -v SQL File/hms.sql`, and inspect `git diff --cached --name-only`. Confirm no backup, dump, environment, secret, output, log, or patient-data file is staged.
7. Technical coordinator reviews the staged file manifest, secret-scan result, and repository privacy setting. Only then may an authorized owner push.
8. Configure branch protection after the initial reviewed push: PR review for `main`, required CI checks when CI exists, and no direct production deployment integration.

## Required branch model

- `main`: reviewed baseline and approved releases only.
- `phase-2/foundation`: all Foundation work through pull requests; no direct work against `main`.

## Stop conditions

Stop and obtain review if a file contains personal, clinical, billing, authentication, database, or API data; if the target is not private; if a scan identifies a likely credential; or if a command would touch the original workspace, local `hms`, or a backup folder.

