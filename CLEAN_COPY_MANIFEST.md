# MedTech HMS — Clean Copy Manifest

Clean copy location: C:\Users\neera\Documents\Codex\MedTech-HMS-clean-source-20261102  
State: no Git metadata, remote, commit, migration execution, or database action.

## Exact copied source baseline

The clean copy contained 2,781 files before these four review deliverables were added.

| Area | Files | Included content |
| --- | ---: | --- |
| hospital/ | 2,731 | PHP source, required CSS/JS, images/fonts, and retained role vendor assets |
| SQL File/migrations/ | 6 | 001 through 006 migration source files only |
| PHASE_1_DOCUMENTATION/ | 26 | Phase 1 discovery/approval documentation |
| Root documentation/config | 18 | Approved project, Phase 2, and repository review documentation plus .gitignore/.env.example |
| js/ | 0 | Directory exists but has no copied files |

## Exact source path rules

Included:
- hospital/** except the explicit demo/cache exclusions below.
- SQL File/migrations/001_appointment_workflow.sql through 006_notifications.sql.
- PHASE_1_DOCUMENTATION/**.
- Approved root Markdown documentation, .gitattributes, .gitignore generated from FINAL_GITIGNORE, and .env.example generated from SANITIZED_ENV_EXAMPLE.

Excluded:
- SQL File/hms.sql.
- Every original backup and XAMPP recovery folder.
- output/, local environment files, logs, caches, Thumbs.db, Sass cache, and runtime uploads/exports.
- Under every role vendor tree: CKEditor samples/, BootstrapCK sample/, Jcrop demo_files/, and jquery-file-upload server/.

## Asset inspection

The clean copy retains 102 non-vendor PHP files, 32 CSS files, 63 JavaScript files, and 70 image/font files. Representative image inspection found generic stock healthcare photography, generic illustration, and placeholder user artwork. No patient-specific uploaded image or profile image was observed. Asset licence/provenance remains a required pre-push review.

## Integrity boundaries

The original source directory was not modified. The clean copy contains no .git directory and has not accessed or exported database data. This document is an inventory of the copy, not proof of legal licensing or a production-readiness certification.

