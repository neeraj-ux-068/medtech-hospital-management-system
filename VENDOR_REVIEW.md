# MedTech HMS — Retained Vendor Review

## Inventory

Three role-specific vendor trees remain for legacy continuity:

| Path | File count |
| --- | ---: |
| hospital/hms/vendor/ | 776 |
| hospital/hms/admin/vendor/ | 776 |
| hospital/hms/doctor/vendor/ | 776 |

Top-level components include Bootstrap, jQuery, jQuery UI, CKEditor, DataTables, Chart.js, FullCalendar, Font Awesome, Select2, Moment, SweetAlert, Toastr, jstree, Jcrop, jquery-file-upload, and related UI plugins.

## Necessity

Non-vendor assets reference components including CKEditor, DataTables, Jcrop, jquery-file-upload, jstree, and validation/UI plugins. Because the legacy project has no root Composer/Node dependency manifest or lockfile, removing vendor directories now could break working pages. Retain them in the source baseline pending a controlled dependency modernization work package.

## Excluded bundled content

The clean copy excludes unreferenced CKEditor sample/demo content, BootstrapCK sample files, Jcrop demo_files, and jquery-file-upload server implementations. No non-vendor application reference to those excluded paths was found.

## Licence evidence

CKEditor licence/readme files are present in all three retained CKEditor trees. This is not a complete licence inventory. Before push, create a software bill of materials, identify versions/licenses for all retained libraries, and preserve required notices.

## Vulnerability posture

No approved vulnerability scanner/package manifest audit was available in this environment. Retained legacy vendor packages may be outdated. Before any public deployment or feature work that changes these dependencies:

1. run a software-composition/vulnerability scan;
2. record component versions and licence obligations;
3. identify whether duplicated role vendor trees can be consolidated safely;
4. keep jquery-file-upload server demos excluded and verify unused upload endpoints remain disabled;
5. test affected pages in staging after any dependency update.

## Review conclusion

Retaining the reviewed vendor assets is the least-disruptive baseline choice. It is not a security endorsement. Licence and vulnerability review remains a technical-coordinator gate before the first GitHub push or Phase 2 dependency change.

