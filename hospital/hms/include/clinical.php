<?php
require_once __DIR__ . '/security.php';
function hms_stage6_ready(mysqli $con): bool { $r=hms_db_query($con,"SELECT 1 FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='prescription_headers' LIMIT 1"); return $r && $r->num_rows===1; }
function hms_doctor_patient_allowed(mysqli $con,int $doctorId,int $patientId): bool { $r=hms_db_query($con,'SELECT 1 FROM users u WHERE u.id=? AND (EXISTS(SELECT 1 FROM appointment a WHERE a.userId=u.id AND a.doctorId=?) OR EXISTS(SELECT 1 FROM tblpatient p WHERE p.PatientEmail=u.email AND p.Docid=?)) LIMIT 1',[$patientId,$doctorId,$doctorId]); return $r && $r->num_rows===1; }
function hms_clinical_patients(mysqli $con,int $doctorId) { return hms_db_query($con,'SELECT DISTINCT u.id,u.fullName FROM users u WHERE EXISTS(SELECT 1 FROM appointment a WHERE a.userId=u.id AND a.doctorId=?) OR EXISTS(SELECT 1 FROM tblpatient p WHERE p.PatientEmail=u.email AND p.Docid=?) ORDER BY u.fullName',[$doctorId,$doctorId]); }
function hms_clinical_notice(): string { return 'Apply SQL File/migrations/004_prescription_laboratory.sql to enable this Stage 6 feature.'; }
