# MedTech HMS — Security Pre-Push Checklist

**Repository target:** private GitHub repository only.  
**Current status:** review package prepared; no Git repository, commit, remote, or push has been created.

## Required checks before first commit

- [ ] Work occurs in a clean copy, never the original unversioned workspace.
- [ ] GitHub repository visibility is confirmed **Private**.
- [ ] `SQL File/hms.sql` is excluded and not staged.
- [ ] Every original backup/XAMPP backup path is ignored and not staged.
- [ ] `output/`, caches, logs, `Thumbs.db`, local uploads/exports, and generated artifacts are ignored.
- [ ] `.env`, private keys, credential files, database client configs, and deployment secrets are ignored.
- [ ] Only sanitized `.env.example` is staged; it contains no real passwords, tokens, hostnames, or user data.
- [ ] `git status --ignored` and `git check-ignore -v` prove the key exclusions.
- [ ] A secret scanner and manual redacted review pass against staged files.
- [ ] Image and upload-like assets are manually checked for personal/patient content.
- [ ] Third-party vendor folders are reviewed for licensing, known vulnerabilities, test/sample upload endpoints, and necessity.
- [ ] No workflow, Pages, hosting, deployment secret, or public repository setting is enabled.

## Required checks before first push

- [ ] Technical coordinator approves the exact staged file list.
- [ ] Project Owner confirms the private repository owner/organization and remote URL.
- [ ] Initial branch is `main`; the `phase-2/foundation` branch is created immediately after the reviewed baseline.
- [ ] The initial commit message identifies this as a sanitized source baseline.
- [ ] Branch protection is planned for `main`; no deployment is configured.
- [ ] Push uses an authorized account and does not print access tokens in console/logs.

## Automatic rejection conditions

Reject the push if a database dump, backup, real `.env`, private key, credential, log, test export, patient data, uploaded file, or public visibility is detected. Escalate suspected sensitive content rather than deleting or editing the original workspace.

