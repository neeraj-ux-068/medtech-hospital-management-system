<?php
require_once __DIR__ . '/include/config.php';
hms_start_session();
$role = (string)($_SESSION['role'] ?? '');
if (!in_array($role, ['patient', 'doctor', 'admin'], true)) { http_response_code(403); exit('Sign in required.'); }
hms_require_role($role);

function hms_doctor_can_access_record_patient(mysqli $db, int $doctorId, int $userId): bool
{
    $result = hms_db_query($db, 'SELECT u.id FROM users u WHERE u.id = ? AND (EXISTS (SELECT 1 FROM appointment a WHERE a.userId = u.id AND a.doctorId = ?) OR EXISTS (SELECT 1 FROM tblpatient p WHERE p.PatientEmail = u.email AND p.Docid = ?)) LIMIT 1', [$userId, $doctorId, $doctorId]);
    return $result && $result->num_rows === 1;
}

$actorId = (int)($_SESSION['id'] ?? 0);
$patientId = $role === 'patient' ? $actorId : (filter_input(INPUT_GET, 'patient_id', FILTER_VALIDATE_INT) ?: (filter_input(INPUT_POST, 'patient_id', FILTER_VALIDATE_INT) ?: 0));
$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT) ?: 0;
$message = '';
$error = '';
$returnUrl = 'medical-records.php' . ($patientId ? '?patient_id=' . $patientId : '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    hms_verify_csrf();
    if ($role !== 'doctor') { http_response_code(403); exit('Clinical records are read-only for this role.'); }
    $patientId = filter_var($_POST['patient_id'] ?? null, FILTER_VALIDATE_INT) ?: 0;
    $recordId = filter_var($_POST['record_id'] ?? null, FILTER_VALIDATE_INT) ?: 0;
    $appointmentId = filter_var($_POST['appointment_id'] ?? null, FILTER_VALIDATE_INT) ?: 0;
    $recordDate = hms_post_string('record_date', 10);
    $symptoms = hms_post_string('symptoms', 4000);
    $diagnosis = hms_post_string('diagnosis', 255);
    $clinicalNotes = hms_post_string('clinical_notes', 8000);
    $treatment = hms_post_string('treatment', 8000);
    $followUp = hms_post_string('follow_up', 4000);
    $bp = hms_post_string('blood_pressure', 20);
    $temperature = hms_post_string('temperature', 10);
    $pulse = hms_post_string('pulse', 4);
    $weight = hms_post_string('weight', 10);
    $height = hms_post_string('height', 10);
    $validDate = DateTimeImmutable::createFromFormat('!Y-m-d', $recordDate);
    $valid = $patientId > 0 && $diagnosis !== '' && $validDate && $validDate->format('Y-m-d') === $recordDate && $recordDate <= date('Y-m-d') && strlen($symptoms) <= 4000 && strlen($clinicalNotes) <= 8000 && strlen($treatment) <= 8000 && strlen($followUp) <= 4000;
    $valid = $valid && ($bp === '' || preg_match('/^\d{2,3}\/\d{2,3}$/', $bp));
    $valid = $valid && ($temperature === '' || (is_numeric($temperature) && (float)$temperature >= 30 && (float)$temperature <= 45));
    $valid = $valid && ($pulse === '' || (ctype_digit($pulse) && (int)$pulse >= 20 && (int)$pulse <= 250));
    $valid = $valid && ($weight === '' || (is_numeric($weight) && (float)$weight >= 0.5 && (float)$weight <= 500));
    $valid = $valid && ($height === '' || (is_numeric($height) && (float)$height >= 30 && (float)$height <= 250));

    if (!$valid || !hms_doctor_can_access_record_patient($con, $actorId, $patientId)) {
        $error = 'Check the patient, date, diagnosis, and clinical values. You may only record care for an authorized patient.';
    } else {
        $patientCheck = hms_db_query($con, 'SELECT id FROM users WHERE id = ? LIMIT 1', [$patientId]);
        if (!$patientCheck || $patientCheck->num_rows !== 1) {
            $error = 'Patient account was not found.';
        } elseif ($appointmentId > 0) {
            $appointmentCheck = hms_db_query($con, "SELECT id FROM appointment WHERE id = ? AND userId = ? AND doctorId = ? AND status NOT IN ('cancelled','rejected') LIMIT 1", [$appointmentId, $patientId, $actorId]);
            if (!$appointmentCheck || $appointmentCheck->num_rows !== 1) $error = 'The selected appointment is not assigned to you and this patient.';
        }
        if ($error === '') {
            $vitals = [$bp !== '' ? $bp : null, $temperature !== '' ? (float)$temperature : null, $pulse !== '' ? (int)$pulse : null, $weight !== '' ? (float)$weight : null, $height !== '' ? (float)$height : null];
            if ($recordId > 0) {
                $owned = hms_db_query($con, 'SELECT id FROM medical_records WHERE id = ? AND doctorId = ? AND patientUserId = ? LIMIT 1', [$recordId, $actorId, $patientId]);
                if (!$owned || $owned->num_rows !== 1) $error = 'You cannot update this clinical record.';
                else {
                    hms_db_query($con, 'UPDATE medical_records SET appointmentId = ?, recordDate = ?, symptoms = ?, diagnosis = ?, clinicalNotes = ?, treatmentPlan = ?, followUpInstructions = ?, bloodPressure = ?, temperature = ?, pulse = ?, weight = ?, height = ?, updatedByDoctorId = ? WHERE id = ? AND doctorId = ? AND patientUserId = ?', [$appointmentId ?: null, $recordDate, $symptoms ?: null, $diagnosis, $clinicalNotes ?: null, $treatment ?: null, $followUp ?: null, ...$vitals, $actorId, $recordId, $actorId, $patientId]);
                    $message = 'Clinical record updated.';
                }
            } else {
                hms_db_query($con, 'INSERT INTO medical_records (patientUserId, doctorId, appointmentId, recordDate, symptoms, diagnosis, clinicalNotes, treatmentPlan, followUpInstructions, bloodPressure, temperature, pulse, weight, height) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [$patientId, $actorId, $appointmentId ?: null, $recordDate, $symptoms ?: null, $diagnosis, $clinicalNotes ?: null, $treatment ?: null, $followUp ?: null, ...$vitals]);
                $message = 'Clinical record created.';
            }
        }
    }
    if ($error === '') { $_SESSION['msg'] = $message; header('Location: medical-records.php?patient_id=' . $patientId); exit; }
}

$patient = null;
if ($patientId > 0 && $role === 'patient') {
    $result = hms_db_query($con, 'SELECT id, fullName, email, gender, address, city, regDate FROM users WHERE id = ?', [$actorId]);
    $patient = $result ? $result->fetch_assoc() : null;
} elseif ($patientId > 0 && $role === 'doctor' && hms_doctor_can_access_record_patient($con, $actorId, $patientId)) {
    $result = hms_db_query($con, 'SELECT id, fullName, email, gender, address, city, regDate FROM users WHERE id = ?', [$patientId]);
    $patient = $result ? $result->fetch_assoc() : null;
} elseif ($patientId > 0 && $role === 'admin') {
    $result = hms_db_query($con, 'SELECT id, fullName, email, gender, address, city, regDate FROM users WHERE id = ?', [$patientId]);
    $patient = $result ? $result->fetch_assoc() : null;
}
if ($role !== 'patient' && $patientId > 0 && !$patient) { http_response_code(404); exit('Patient not found or access is not permitted.'); }

$recordToEdit = null;
if ($editId && $role === 'doctor' && $patient) {
    $editResult = hms_db_query($con, 'SELECT * FROM medical_records WHERE id = ? AND patientUserId = ? AND doctorId = ? LIMIT 1', [$editId, $patientId, $actorId]);
    $recordToEdit = $editResult ? $editResult->fetch_assoc() : null;
    if (!$recordToEdit) { http_response_code(404); exit('Clinical record not found or access is not permitted.'); }
}

$records = null;
$legacyRecords = null;
$doctorOptions = null;
$patientOptions = null;
$appointmentOptions = null;
if ($patient) {
    $records = hms_db_query($con, 'SELECT m.*, d.doctorName FROM medical_records m JOIN doctors d ON d.id = m.doctorId WHERE m.patientUserId = ? ORDER BY m.recordDate DESC, m.id DESC', [$patientId]);
    $legacyRecords = hms_db_query($con, 'SELECT h.*, d.doctorName FROM tblmedicalhistory h JOIN tblpatient p ON p.ID = h.PatientID LEFT JOIN doctors d ON d.id = p.Docid JOIN users u ON u.email = p.PatientEmail WHERE u.id = ? ORDER BY h.CreationDate DESC', [$patientId]);
    if ($role === 'doctor') $appointmentOptions = hms_db_query($con, 'SELECT id, appointmentDate, appointmentTime, status FROM appointment WHERE userId = ? AND doctorId = ? ORDER BY appointmentDate DESC', [$patientId, $actorId]);
}
if ($role === 'doctor') {
    $patientOptions = hms_db_query($con, 'SELECT DISTINCT u.id, u.fullName, u.email FROM users u WHERE EXISTS (SELECT 1 FROM appointment a WHERE a.userId = u.id AND a.doctorId = ?) OR EXISTS (SELECT 1 FROM tblpatient p WHERE p.PatientEmail = u.email AND p.Docid = ?) ORDER BY u.fullName', [$actorId, $actorId]);
} elseif ($role === 'admin') {
    $patientOptions = hms_db_query($con, 'SELECT id, fullName, email FROM users ORDER BY fullName');
    $doctorOptions = hms_db_query($con, 'SELECT id, doctorName FROM doctors ORDER BY doctorName');
}
$adminPatientHistoryOptions = $role === 'admin' ? hms_db_query($con, 'SELECT id, fullName FROM users ORDER BY fullName') : null;

$filteredRecords = null;
if ($role !== 'patient') {
    $where = [];
    $params = [];
    if ($role === 'doctor') { $where[] = 'm.doctorId = ?'; $params[] = $actorId; }
    if ($role === 'admin' && ($filterPatient = filter_input(INPUT_GET, 'filter_patient', FILTER_VALIDATE_INT))) { $where[] = 'm.patientUserId = ?'; $params[] = $filterPatient; }
    if ($role === 'admin' && ($filterDoctor = filter_input(INPUT_GET, 'filter_doctor', FILTER_VALIDATE_INT))) { $where[] = 'm.doctorId = ?'; $params[] = $filterDoctor; }
    $filterDate = (string)($_GET['date'] ?? '');
    if ($filterDate !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $filterDate)) { $where[] = 'm.recordDate = ?'; $params[] = $filterDate; }
    $diagnosis = substr(trim((string)($_GET['diagnosis'] ?? '')), 0, 100);
    if ($diagnosis !== '') { $where[] = 'm.diagnosis LIKE ?'; $params[] = '%' . $diagnosis . '%'; }
    $filteredRecords = hms_db_query($con, 'SELECT m.*, u.fullName AS patientName, d.doctorName FROM medical_records m JOIN users u ON u.id=m.patientUserId JOIN doctors d ON d.id=m.doctorId' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY m.recordDate DESC, m.id DESC LIMIT 250', $params);
}
$portal = hms_base_url() . '/hms/';
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Medical records | MedTech</title><link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css"><link rel="stylesheet" href="assets/css/styles.css"><link rel="stylesheet" href="assets/css/plugins.css"><link rel="stylesheet" href="assets/css/themes/theme-1.css" /><link rel="stylesheet" href="assets/css/medtech-app.css"><style>
.mr-wrap{max-width:1180px;margin:24px auto}.mr-panel{background:#fff;border-radius:12px;padding:24px;margin:18px 0;box-shadow:0 4px 18px rgba(16,24,40,.06)}.mr-timeline{border-left:3px solid #cfe5f4;margin-left:10px;padding-left:22px}.mr-card{position:relative;background:#f8fafc;border-radius:9px;padding:18px;margin:0 0 18px}.mr-card:before{content:'';position:absolute;left:-31px;top:20px;width:14px;height:14px;border-radius:50%;background:#1683c4;border:3px solid #fff;box-shadow:0 0 0 2px #cfe5f4}.mr-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px}.mr-vitals{display:flex;flex-wrap:wrap;gap:8px}.mr-vitals span{background:#eaf3f9;padding:6px 10px;border-radius:20px;font-size:13px}.mr-label{font-size:12px;text-transform:uppercase;letter-spacing:.06em;color:#667085}.mr-title{margin-top:4px}.mr-nav{padding:16px 0}.mr-nav a{margin-right:16px}@media(max-width:600px){.mr-panel{padding:16px}.mr-wrap{margin:12px auto}}
</style></head><body><nav class="container mr-nav"><strong>MedTech Clinical Records</strong> · <a href="<?=$portal . ($role==='patient'?'dashboard.php':($role==='doctor'?'doctor/dashboard.php':'admin/dashboard.php'))?>">Dashboard</a><?php if($role==='patient'):?><a href="<?=$portal?>book-appointment.php">Appointments</a><?php endif;?></nav><main class="container mr-wrap">
<?php if(!empty($_SESSION['msg'])):?><div class="alert alert-success"><?=hms_h($_SESSION['msg'])?></div><?php unset($_SESSION['msg']);endif;?>
<h1><?=($role==='patient'?'Your medical history':($role==='doctor'?'Patient clinical records':'Medical records'))?></h1>
<?php if($role==='doctor'&&$patientOptions):?><div class="mr-panel"><form method="get" class="form-inline"><label for="patient_id">Patient</label> <select class="form-control" name="patient_id" id="patient_id" required><option value="">Choose an authorized patient</option><?php while($option=$patientOptions->fetch_assoc()):?><option value="<?=hms_h($option['id'])?>" <?=($patientId==(int)$option['id']?'selected':'')?>><?=hms_h($option['fullName'].' · '.$option['email'])?></option><?php endwhile;?></select> <button class="btn btn-primary">Open history</button></form><form method="get" class="form-inline" style="margin-top:12px"><input type="date" name="date" class="form-control" value="<?=hms_h($_GET['date']??'')?>"><input type="search" name="diagnosis" maxlength="100" class="form-control" placeholder="Filter diagnosis" value="<?=hms_h($_GET['diagnosis']??'')?>"><button class="btn btn-default">Filter my records</button></form></div><?php endif;?>
<?php if($role==='admin'):?><div class="mr-panel"><form method="get" class="form-inline"><label>Open full patient timeline</label> <select name="patient_id" class="form-control" required><option value="">Select patient</option><?php while($historyOption=$adminPatientHistoryOptions->fetch_assoc()):?><option value="<?=hms_h($historyOption['id'])?>" ><?=hms_h($historyOption['fullName'])?></option><?php endwhile;?></select> <button class="btn btn-default">Open history</button></form></div><div class="mr-panel"><form method="get" class="mr-grid"><div class="form-group"><label>Patient</label><select name="filter_patient" class="form-control"><option value="">All patients</option><?php while($option=$patientOptions->fetch_assoc()):?><option value="<?=hms_h($option['id'])?>" <?=((string)($_GET['filter_patient']??'')===(string)$option['id']?'selected':'')?>><?=hms_h($option['fullName'])?></option><?php endwhile;?></select></div><div class="form-group"><label>Doctor</label><select name="filter_doctor" class="form-control"><option value="">All doctors</option><?php while($option=$doctorOptions->fetch_assoc()):?><option value="<?=hms_h($option['id'])?>" <?=((string)($_GET['filter_doctor']??'')===(string)$option['id']?'selected':'')?>><?=hms_h($option['doctorName'])?></option><?php endwhile;?></select></div><div class="form-group"><label>Date</label><input class="form-control" type="date" name="date" value="<?=hms_h($_GET['date']??'')?>"></div><div class="form-group"><label>Diagnosis</label><input class="form-control" type="search" name="diagnosis" maxlength="100" value="<?=hms_h($_GET['diagnosis']??'')?>"></div><div><button class="btn btn-primary">Filter records</button> <a class="btn btn-default" href="medical-records.php">Clear</a></div></form></div><?php endif;?>
<?php if($role!=='patient'&&!$patient):?><div class="mr-panel"><h2>Clinical record search</h2><p>Choose an authorized patient to review their record timeline. Admin access is read-only.</p><?php if($role==='doctor'):?><p>Patient access is limited to patients with an appointment assigned to you or a legacy patient profile assigned to you.</p><?php endif;?></div><?php endif;?>
<?php if($role!=='patient'&&$filteredRecords):?><div class="mr-panel"><h2>Structured records</h2><div class="table-responsive"><table class="table table-striped"><thead><tr><th>Date</th><th>Patient</th><th>Doctor</th><th>Diagnosis</th><th>Appointment</th><th>View</th></tr></thead><tbody><?php while($row=$filteredRecords->fetch_assoc()):?><tr><td><?=hms_h($row['recordDate'])?></td><td><?=hms_h($row['patientName'])?></td><td><?=hms_h($row['doctorName'])?></td><td><?=hms_h($row['diagnosis'])?></td><td><?=hms_h($row['appointmentId']?:'—')?></td><td><a href="medical-records.php?patient_id=<?=hms_h($row['patientUserId'])?>">Open patient history</a><?php if($role==='doctor'&&(int)$row['doctorId']===$actorId):?> · <a href="medical-records.php?patient_id=<?=hms_h($row['patientUserId'])?>&amp;edit=<?=hms_h($row['id'])?>">Edit</a><?php endif;?></td></tr><?php endwhile;?></tbody></table></div></div><?php endif;?>
<?php if($patient):?><section class="mr-panel"><div class="mr-grid"><div><span class="mr-label">Patient</span><h2 class="mr-title"><?=hms_h($patient['fullName'])?></h2></div><div><span class="mr-label">Email</span><div><?=hms_h($patient['email'])?></div></div><div><span class="mr-label">Gender</span><div><?=hms_h($patient['gender'])?></div></div><div><span class="mr-label">Location</span><div><?=hms_h(trim((string)$patient['city'].' '.(string)$patient['address']))?></div></div></div></section>
<?php if($role==='doctor'):?><section class="mr-panel"><h2><?=$recordToEdit?'Update clinical record':'Add clinical record'?></h2><?php if($error):?><div class="alert alert-danger"><?=hms_h($error)?></div><?php endif;?><form method="post"><?=hms_csrf_field()?><input type="hidden" name="patient_id" value="<?=hms_h($patientId)?>"><input type="hidden" name="record_id" value="<?=hms_h($recordToEdit['id']??0)?>"><div class="mr-grid"><div class="form-group"><label>Record date</label><input class="form-control" type="date" name="record_date" max="<?=date('Y-m-d')?>" value="<?=hms_h($recordToEdit['recordDate']??date('Y-m-d'))?>" required></div><div class="form-group"><label>Appointment (optional)</label><select class="form-control" name="appointment_id"><option value="">No linked appointment</option><?php if($appointmentOptions)while($op=$appointmentOptions->fetch_assoc()):?><option value="<?=hms_h($op['id'])?>" <?=((int)($recordToEdit['appointmentId']??0)===(int)$op['id']?'selected':'')?>>#<?=hms_h($op['id'])?> · <?=hms_h($op['appointmentDate'].' '.$op['appointmentTime'].' · '.$op['status'])?></option><?php endwhile;?></select></div><div class="form-group"><label>Diagnosis *</label><input class="form-control" name="diagnosis" maxlength="255" value="<?=hms_h($recordToEdit['diagnosis']??'')?>" required></div></div><div class="form-group"><label>Symptoms / complaints</label><textarea class="form-control" name="symptoms" maxlength="4000" rows="3"><?=hms_h($recordToEdit['symptoms']??'')?></textarea></div><div class="form-group"><label>Clinical notes</label><textarea class="form-control" name="clinical_notes" maxlength="8000" rows="4"><?=hms_h($recordToEdit['clinicalNotes']??'')?></textarea></div><div class="form-group"><label>Treatment / plan</label><textarea class="form-control" name="treatment" maxlength="8000" rows="3"><?=hms_h($recordToEdit['treatmentPlan']??'')?></textarea></div><div class="form-group"><label>Follow-up instructions</label><textarea class="form-control" name="follow_up" maxlength="4000" rows="3"><?=hms_h($recordToEdit['followUpInstructions']??'')?></textarea></div><h3>Observations (optional)</h3><div class="mr-grid"><div class="form-group"><label>Blood pressure (e.g. 120/80)</label><input class="form-control" name="blood_pressure" maxlength="20" value="<?=hms_h($recordToEdit['bloodPressure']??'')?>"></div><div class="form-group"><label>Temperature °C (30–45)</label><input class="form-control" type="number" step="0.1" min="30" max="45" name="temperature" value="<?=hms_h($recordToEdit['temperature']??'')?>"></div><div class="form-group"><label>Pulse / min</label><input class="form-control" type="number" min="20" max="250" name="pulse" value="<?=hms_h($recordToEdit['pulse']??'')?>"></div><div class="form-group"><label>Weight kg</label><input class="form-control" type="number" step="0.1" min="0.5" max="500" name="weight" value="<?=hms_h($recordToEdit['weight']??'')?>"></div><div class="form-group"><label>Height cm</label><input class="form-control" type="number" step="0.1" min="30" max="250" name="height" value="<?=hms_h($recordToEdit['height']??'')?>"></div></div><button class="btn btn-primary">Save clinical record</button></form></section><?php endif;?>
<section class="mr-panel"><h2>Clinical history timeline</h2><div class="mr-timeline"><?php $hasAny=false;if($records)while($record=$records->fetch_assoc()):$hasAny=true;?><article class="mr-card"><span class="mr-label"><?=hms_h($record['recordDate'])?> · Dr. <?=hms_h($record['doctorName'])?><?php if($record['appointmentId']):?> · Appointment #<?=hms_h($record['appointmentId'])?><?php endif;?></span><h3><?=hms_h($record['diagnosis'])?></h3><?php if($record['symptoms']):?><p><strong>Symptoms:</strong> <?=nl2br(hms_h($record['symptoms']))?></p><?php endif;?><?php if($record['clinicalNotes']):?><p><strong>Clinical notes:</strong> <?=nl2br(hms_h($record['clinicalNotes']))?></p><?php endif;?><?php if($record['treatmentPlan']):?><p><strong>Treatment:</strong> <?=nl2br(hms_h($record['treatmentPlan']))?></p><?php endif;?><?php if($record['followUpInstructions']):?><p><strong>Follow-up:</strong> <?=nl2br(hms_h($record['followUpInstructions']))?></p><?php endif;?><div class="mr-vitals"><?php foreach(['Blood pressure'=>'bloodPressure','Temperature °C'=>'temperature','Pulse'=>'pulse','Weight kg'=>'weight','Height cm'=>'height'] as $label=>$key):if($record[$key]!==null&&$record[$key]!==''):?><span><?=hms_h($label)?>: <?=hms_h($record[$key])?></span><?php endif;endforeach;?></div><?php if($role==='doctor'&&(int)$record['doctorId']===$actorId):?><p><a href="medical-records.php?patient_id=<?=hms_h($patientId)?>&amp;edit=<?=hms_h($record['id'])?>">Edit this record</a> <small>Created <?=hms_h($record['createdAt'])?><?php if($record['updatedAt']):?> · Updated <?=hms_h($record['updatedAt'])?><?php endif;?></small></p><?php endif;?></article><?php endwhile;?><?php if($legacyRecords)while($legacy=$legacyRecords->fetch_assoc()):$hasAny=true;?><article class="mr-card"><span class="mr-label"><?=hms_h($legacy['CreationDate'])?> · <?=hms_h($legacy['doctorName']?'Dr. '.$legacy['doctorName']:'Legacy record')?></span><h3>Previous medical history</h3><?php if($legacy['MedicalPres']):?><p><strong>Prescription / notes:</strong> <?=nl2br(hms_h($legacy['MedicalPres']))?></p><?php endif;?><div class="mr-vitals"><?php foreach(['Blood pressure'=>'BloodPressure','Blood sugar'=>'BloodSugar','Temperature'=>'Temperature','Weight'=>'Weight'] as $label=>$key):if($legacy[$key]!==null&&$legacy[$key]!==''):?><span><?=hms_h($label)?>: <?=hms_h($legacy[$key])?></span><?php endif;endforeach;?></div><small>Legacy medical history</small></article><?php endwhile;?><?php if(!$hasAny):?><div class="mr-card">No medical records are available yet.</div><?php endif;?></div></section><?php endif;?></main></body></html>
