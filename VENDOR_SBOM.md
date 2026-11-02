# MedTech HMS — Vendor SBOM

## Scope and limitations

This inventory covers retained browser/vendor components in the three duplicated legacy vendor trees. It is not a package-manager lockfile or a software-composition-analysis result. Versions not recoverable from inspected headers are marked Unverified. No automatic upgrade/removal occurred.

## Versioned components recovered from file headers

| Component | Version | License evidence | Vulnerability finding |
| --- | --- | --- | --- |
| jQuery | 1.11.0 | Header links to jQuery license; MIT expected | No SCA result; legacy version requires CVE review |
| CKEditor | 4.4.7 | LICENSE.md retained; licensing terms require review | No SCA result; legacy version requires CVE review |
| DataTables | 1.10.5 | Header links to datatables.net/license | No SCA result; legacy version requires CVE review |
| Chart.js | 1.0.2 | Header identifies copyright; full license unverified | No SCA result; legacy version requires CVE review |
| Moment | 2.9.0 | Header states MIT | No SCA result; legacy version requires CVE review |
| FullCalendar | 2.3.1 | Header points to project license; exact terms unverified | No SCA result; legacy version requires CVE review |
| jQuery Validation | 1.13.1 | Header states MIT | No SCA result; legacy version requires CVE review |

## Retained components with version/license not yet verified

animate.css; autosize; Bootstrap; bootstrap-datepicker; bootstrap-datetimepicker; bootstrap-fileinput; bootstrap-progressbar; bootstrap-rating; bootstrap-timepicker; bootstrap-touchspin; Font Awesome; gmaps; javascript-Load-Image; Jcrop; jquery-cookie; jquery-file-upload client assets; jquery-smart-wizard; jQuery UI; jquery.sparkline; jstree; ladda-bootstrap; maskedinput; modernizr; nestable; perfect-scrollbar; Select2; selectFx; SweetAlert; Switchery; Themify Icons; Toastr; twbs-pagination.

## Notices and licensing

CKEditor license/readme files are retained in each role tree. Most other components did not expose a license file at the component root in this limited inspection. Required notices cannot yet be declared complete. Preserve all existing notices and perform a formal licence/SBOM review before public distribution.

## Vulnerability findings

No dependency/SCA vulnerability database scan was available in this work package, so no CVE is claimed as confirmed or absent. The recovered versions are old and should be treated as a risk requiring an approved SCA scan and controlled staging upgrade plan. The exact server demos for jquery-file-upload were excluded; client assets remain because non-vendor application assets reference the plugin family.

## Required follow-up

1. Generate an SPDX or CycloneDX SBOM from a reviewed inventory.
2. Run a vulnerability scanner with a current advisory database.
3. Map each retained vendor component to a version and license notice.
4. Consolidate duplicated vendor trees only in a separately tested work package.
5. Do not expose legacy upload endpoints or reintroduce excluded server demos.

