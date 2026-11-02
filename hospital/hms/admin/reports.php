<?php
require_once __DIR__ . '/include/config.php';
require_once __DIR__ . '/../include/analytics.php';
hms_start_session();
hms_require_role('admin');

[$from, $to, $rangeError] = hms_analytics_range($_GET);
$search = trim((string)($_GET['q'] ?? ''));
if (strlen($search) > 80) $search = '';
$labsReady = hms_analytics_table_exists($con, 'laboratory_requests');
$billingReady = hms_analytics_table_exists($con, 'billing_invoices') && hms_analytics_table_exists($con, 'billing_payments');

$appointmentRows = hms_db_query($con, "SELECT COALESCE(NULLIF(status,''),'confirmed') status, COUNT(*) total FROM appointment WHERE appointmentDate BETWEEN ? AND ? GROUP BY status ORDER BY status", [$from, $to]);
$appointmentSummary = [];
while ($row = $appointmentRows->fetch_assoc()) $appointmentSummary[$row['status']] = (int)$row['total'];
$doctorRows = hms_db_query($con, "SELECT d.doctorName, d.specilization, COUNT(a.id) total FROM doctors d LEFT JOIN appointment a ON a.doctorId=d.id AND a.appointmentDate BETWEEN ? AND ? GROUP BY d.id,d.doctorName,d.specilization ORDER BY total DESC,d.doctorName LIMIT 25", [$from, $to]);
$registrationRows = hms_db_query($con, "SELECT DATE(regDate) day, COUNT(*) total FROM users WHERE DATE(regDate) BETWEEN ? AND ? GROUP BY DATE(regDate) ORDER BY day", [$from, $to]);
$registrations = [];
while ($row = $registrationRows->fetch_assoc()) $registrations[] = $row;
$labSummary = [];
if ($labsReady) { $result = hms_db_query($con, "SELECT status,COUNT(*) total FROM laboratory_requests WHERE DATE(requested_at) BETWEEN ? AND ? GROUP BY status ORDER BY status", [$from, $to]); while ($row = $result->fetch_assoc()) $labSummary[$row['status']] = (int)$row['total']; }
$billing = ['invoiced' => 0, 'collected' => 0, 'outstanding' => 0];
if ($billingReady) { $billing = hms_db_query($con, "SELECT COALESCE(SUM(total_amount),0) invoiced,COALESCE(SUM(amount_paid),0) collected,COALESCE(SUM(balance_amount),0) outstanding FROM billing_invoices WHERE issue_date BETWEEN ? AND ?", [$from, $to])->fetch_assoc(); }
$patientSql = "SELECT u.fullName,u.email,COUNT(a.id) appointments FROM users u LEFT JOIN appointment a ON a.userId=u.id AND a.appointmentDate BETWEEN ? AND ?";
$patientParams = [$from, $to];
if ($search !== '') { $patientSql .= ' WHERE u.fullName LIKE ? OR u.email LIKE ?'; $patientParams[] = '%'.$search.'%'; $patientParams[] = '%'.$search.'%'; }
$patientSql .= ' GROUP BY u.id,u.fullName,u.email ORDER BY appointments DESC,u.fullName LIMIT 50';
$patientRows = hms_db_query($con, $patientSql, $patientParams);

if (($_GET['export'] ?? '') === 'appointments') {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="medtech-appointments-'.$from.'-to-'.$to.'.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Date', 'Patient', 'Doctor', 'Department', 'Status']);
    $rows = hms_db_query($con, "SELECT a.appointmentDate,u.fullName,d.doctorName,a.doctorSpecialization,a.status FROM appointment a JOIN users u ON u.id=a.userId JOIN doctors d ON d.id=a.doctorId WHERE a.appointmentDate BETWEEN ? AND ? ORDER BY a.appointmentDate,a.id", [$from, $to]);
    while ($row = $rows->fetch_assoc()) fputcsv($out, array_map('hms_csv_value', [$row['appointmentDate'], $row['fullName'], $row['doctorName'], $row['doctorSpecialization'], $row['status']]));
    fclose($out); exit;
}
$appointmentTotal = array_sum($appointmentSummary);
$maxRegistration = max(1, ...array_map(static fn($row) => (int)$row['total'], $registrations ?: [['total'=>0]]));
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><title>MedTech | Analytics & reports</title>
<link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css"><link rel="stylesheet" href="vendor/fontawesome/css/font-awesome.min.css"><link rel="stylesheet" href="vendor/themify-icons/themify-icons.min.css"><link rel="stylesheet" href="vendor/perfect-scrollbar/perfect-scrollbar.min.css"><link rel="stylesheet" href="assets/css/styles.css"><link rel="stylesheet" href="assets/css/themes/theme-1.css"><link rel="stylesheet" href="../assets/css/medtech-app.css"><link rel="stylesheet" href="../assets/css/medtech-enterprise.css"></head><body><div id="app"><?php include 'include/sidebar.php'; ?><div class="app-content"><?php include 'include/header.php'; ?><div class="main-content"><div class="wrap-content container"><section id="page-title"><h1 class="mainTitle">Analytics &amp; reports</h1><p class="text-muted">Operational reporting for <?=hms_h($from)?> through <?=hms_h($to)?>.</p></section>
<?php if ($rangeError): ?><div class="alert alert-warning"><?=hms_h($rangeError)?></div><?php endif; ?>
<div class="medtech-report-panel"><form class="row" method="get"><div class="col-sm-3 form-group"><label>From</label><input class="form-control" type="date" name="from" value="<?=hms_h($from)?>"></div><div class="col-sm-3 form-group"><label>To</label><input class="form-control" type="date" name="to" value="<?=hms_h($to)?>"></div><div class="col-sm-4 form-group"><label>Patient search</label><input class="form-control" name="q" value="<?=hms_h($search)?>" placeholder="Name or email"></div><div class="col-sm-2 form-group"><label>&nbsp;</label><button class="btn btn-primary btn-block" type="submit">Apply filters</button></div></form></div>
<div class="medtech-stat-grid"><div class="medtech-stat-card"><span class="medtech-stat-label">Appointments</span><strong><?=$appointmentTotal?></strong><small>In selected period</small></div><div class="medtech-stat-card"><span class="medtech-stat-label">Completed visits</span><strong><?=$appointmentSummary['completed'] ?? 0?></strong><small>Clinical work completed</small></div><div class="medtech-stat-card"><span class="medtech-stat-label">New registrations</span><strong><?=array_sum(array_map(static fn($r)=>(int)$r['total'],$registrations))?></strong><small>Patients registered</small></div><div class="medtech-stat-card"><span class="medtech-stat-label">Outstanding balance</span><strong>₹<?=number_format((float)$billing['outstanding'],2)?></strong><small><?=$billingReady ? 'Billing data in period' : 'Billing is unavailable'?></small></div></div>
<div class="row"><div class="col-md-7"><section class="medtech-report-panel"><header><h3>Appointment status summary</h3><a class="btn btn-default btn-sm" href="?from=<?=hms_h($from)?>&amp;to=<?=hms_h($to)?>&amp;export=appointments">Export CSV</a></header><div class="medtech-status-bars"><?php foreach (['pending','confirmed','completed','cancelled','rejected'] as $status): $count=$appointmentSummary[$status]??0; $width=$appointmentTotal ? max(2,round($count/$appointmentTotal*100)) : 0; ?><div><span><?=hms_h(ucfirst($status))?></span><b style="width:<?=$width?>%"></b><em><?=$count?></em></div><?php endforeach; ?></div></section></div><div class="col-md-5"><section class="medtech-report-panel"><h3>Billing summary</h3><?php if($billingReady): ?><dl class="medtech-definition"><dt>Invoiced</dt><dd>₹<?=number_format((float)$billing['invoiced'],2)?></dd><dt>Collected</dt><dd>₹<?=number_format((float)$billing['collected'],2)?></dd><dt>Outstanding</dt><dd>₹<?=number_format((float)$billing['outstanding'],2)?></dd></dl><?php else: ?><p class="text-muted">Billing tables are not available in this database.</p><?php endif; ?></section></div></div>
<div class="row"><div class="col-md-6"><section class="medtech-report-panel"><h3>Doctor appointment counts</h3><div class="table-responsive"><table class="table"><thead><tr><th>Doctor</th><th>Specialization</th><th>Appointments</th></tr></thead><tbody><?php while($row=$doctorRows->fetch_assoc()):?><tr><td><?=hms_h($row['doctorName'])?></td><td><?=hms_h($row['specilization'])?></td><td><?=hms_h($row['total'])?></td></tr><?php endwhile;?></tbody></table></div></section></div><div class="col-md-6"><section class="medtech-report-panel"><h3>Laboratory status</h3><?php if($labsReady && $labSummary):?><div class="medtech-status-bars"><?php foreach($labSummary as $status=>$count):?><div><span><?=hms_h(ucfirst(str_replace('_',' ',$status)))?></span><b style="width:<?=max(2,round($count/max($labSummary)*100))?>%"></b><em><?=$count?></em></div><?php endforeach;?></div><?php else:?><p class="text-muted">No laboratory activity in this period.</p><?php endif;?></section><section class="medtech-report-panel"><h3>Patient registration trend</h3><?php if($registrations):?><div class="medtech-mini-chart"><?php foreach($registrations as $row):?><div title="<?=hms_h($row['day'])?>: <?=hms_h($row['total'])?>"><b style="height:<?=max(8,round(((int)$row['total']/$maxRegistration)*100))?>%"></b><small><?=hms_h(date('M j',strtotime($row['day'])))?></small></div><?php endforeach;?></div><?php else:?><p class="text-muted">No patient registrations in this period.</p><?php endif;?></section></div></div>
<section class="medtech-report-panel"><h3>Patient appointment activity</h3><div class="table-responsive"><table class="table"><thead><tr><th>Patient</th><th>Email</th><th>Appointments in period</th></tr></thead><tbody><?php while($row=$patientRows->fetch_assoc()):?><tr><td><?=hms_h($row['fullName'])?></td><td><?=hms_h($row['email'])?></td><td><?=hms_h($row['appointments'])?></td></tr><?php endwhile;?></tbody></table></div></section>
</div></div><?php include 'include/footer.php'; ?></div></div><script src="vendor/jquery/jquery.min.js"></script><script src="vendor/bootstrap/js/bootstrap.min.js"></script><script src="vendor/perfect-scrollbar/perfect-scrollbar.min.js"></script><script src="assets/js/main.js"></script><script>jQuery(function(){Main.init();});</script></body></html>
