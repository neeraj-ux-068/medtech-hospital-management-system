# MedTech HMS — Clean Copy Exclusion Verification

## Verified absent from clean copy

| Exclusion | Result |
| --- | --- |
| SQL File/hms.sql | Absent |
| output/ | Absent |
| stabilization-backup-20261016-095806/ | Absent |
| stage3-font-change-backup/ | Absent |
| stage3-xampp-backup-20261007/ | Absent |
| xampp-before-* recovery folders | Absent |
| .git/ | Absent |
| .env and private-key filename artifacts | Absent |
| Thumbs.db, log, dump, PEM/KEY/PFX/P12 files | Absent |
| CKEditor samples and BootstrapCK sample paths | Absent |
| Jcrop demo_files paths | Absent |
| jquery-file-upload server paths | Absent |

## Storage and vendor/bin correction

No directory named storage exists in either inspected source scope or the clean copy. No vendor/bin directory exists. The final ignore policy intentionally does not exclude either pattern broadly, preventing accidental omission of future required source files.

## Retained dependency decision

All remaining vendor components are retained to avoid breaking the legacy PHP interface, which lacks a root dependency manifest/lockfile. They are not implicitly trusted: the vendor review records licence, vulnerability, and necessity follow-up before push.

## Verification method

Path existence checks and recursive prohibited-filename checks were run against the clean-copy directory. No database, migration, or application runtime action was performed.

