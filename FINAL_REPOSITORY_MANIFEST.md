# MedTech HMS — Final Proposed Repository Manifest

Status: proposed baseline only. It has not been copied into a Git working tree, committed, or pushed.

## Exact inclusion rules

Include these paths from a clean copy of the current snapshot, subject to the exclusions below.

| Path | Content represented | Evidence |
| --- | --- | --- |
| hospital/** | Existing PHP app, CSS, JavaScript, images, fonts, and required vendor assets | 102 non-vendor PHP, 32 CSS, 63 JS, and 70 image/font files found |
| js/** | Existing project JavaScript candidate | Confirm it is source during clean-copy review |
| SQL File/migrations/001_appointment_workflow.sql through 006_notifications.sql | Migration source | All six files present; never auto-run |
| README.md, CHANGELOG.md, AUDIT_REPORT.md, TRIGGER_AUDIT.md, DEPLOYMENT_CHECKLIST.md, .gitattributes | Project/audit documentation | Present |
| PHASE_1_DOCUMENTATION/** | Approved discovery package | Present |
| Phase 2 and repository review Markdown documents | Foundation/repository documentation | Present after review |
| .env.example | Sanitized variable-name template only | No real value allowed |
| .gitignore | Created from FINAL_GITIGNORE in clean Git copy only | Pending approval |

## Exact exclusions

- SQL File/hms.sql and any dump, archive, database-copy, or SQL backup artifact.
- stabilization-backup-20261016-095806/, stage3-font-change-backup/, stage3-xampp-backup-20261007/, and every xampp-before-*/ directory.
- output/, logs, caches, Sass cache files, Thumbs.db, local .env files, private keys, local uploads, and exports.
- Unreferenced bundled demos: CKEditor samples/ and BootstrapCK sample/, Jcrop demo_files/, and jquery-file-upload server/ under each role vendor tree.

## Explicitly retained assets

The inclusion rule retains required PHP, CSS, JS, image, font, and role-vendor assets. The non-vendor asset audit found 24 JPG, 14 PNG, 6 SVG, 1 WEBP, and TTF/EOT/WOFF/WOFF2 font files. Required static assets must not be omitted merely because they are binary.

## Runtime data conclusion

No directory named storage and no vendor/bin directory exists in the current snapshot. No non-vendor PHP reference to storage/, uploads/, exports/, file-upload APIs, or file-input handling was found. The final ignore file therefore has no broad storage pattern. Future runtime upload/export locations remain explicitly excluded and must be reviewed before inclusion.
