<?php
require_once __DIR__ . '/include/config.php';
hms_start_session();
hms_require_role('admin');

$modules = [
    'departments' => ['title'=>'Departments','description'=>'Organize clinical services and department leadership.','table'=>'departments','icon'=>'fa-hospital-o','search'=>'department_name','list'=>"SELECT d.id,d.department_name,COALESCE(doc.doctorName,'—') AS head_doctor,d.description,d.status FROM departments d LEFT JOIN doctors doc ON doc.id=d.head_doctor_id ORDER BY d.department_name",'fields'=>[
        ['name'=>'department_name','label'=>'Department name','type'=>'text','required'=>true,'max'=>120],['name'=>'head_doctor_id','label'=>'Department head','type'=>'doctor'],['name'=>'description','label'=>'Description','type'=>'text','max'=>500],['name'=>'status','label'=>'Status','type'=>'select','options'=>['active'=>'Active','inactive'=>'Inactive']]
    ]],
    'beds' => ['title'=>'Beds & rooms','description'=>'Track room allocation and current bed availability.','table'=>'hospital_beds','icon'=>'fa-bed','search'=>'r.room_number','list'=>"SELECT b.id,r.room_number,r.ward,r.room_type,b.bed_label,b.status FROM hospital_beds b JOIN hospital_rooms r ON r.id=b.room_id ORDER BY r.room_number,b.bed_label",'fields'=>[
        ['name'=>'room_number','label'=>'Room number','type'=>'text','required'=>true,'max'=>30],['name'=>'ward','label'=>'Ward','type'=>'text','required'=>true,'max'=>100],['name'=>'room_type','label'=>'Room type','type'=>'text','required'=>true,'max'=>60],['name'=>'bed_label','label'=>'Bed label','type'=>'text','required'=>true,'max'=>30]
    ]],
    'pharmacy' => ['title'=>'Pharmacy','description'=>'Manage medicine stock, suppliers and expiry dates.','table'=>'pharmacy_medicines','icon'=>'fa-medkit','search'=>'medicine_name','list'=>"SELECT id,medicine_name,category,quantity,reorder_level,expiry_date,supplier,unit_price,CASE WHEN expiry_date<CURDATE() THEN 'expired' WHEN quantity=0 THEN 'out of stock' WHEN quantity<=reorder_level THEN 'low stock' ELSE 'in stock' END AS stock_status FROM pharmacy_medicines ORDER BY medicine_name",'fields'=>[
        ['name'=>'medicine_name','label'=>'Medicine name','type'=>'text','required'=>true,'max'=>180],['name'=>'category','label'=>'Category','type'=>'text','required'=>true,'max'=>100],['name'=>'quantity','label'=>'Quantity in stock','type'=>'number','required'=>true,'min'=>0,'max'=>100000],['name'=>'reorder_level','label'=>'Low-stock threshold','type'=>'number','required'=>true,'min'=>0,'max'=>100000],['name'=>'expiry_date','label'=>'Expiry date','type'=>'date'],['name'=>'supplier','label'=>'Supplier','type'=>'text','max'=>180],['name'=>'unit_price','label'=>'Unit price','type'=>'decimal','required'=>true,'min'=>0]
    ]],
    'laboratory' => ['title'=>'Laboratory','description'=>'Follow lab requests from sample collection through results.','table'=>'lab_orders','icon'=>'fa-flask','search'=>'l.test_name','list'=>"SELECT l.id,l.test_name,l.ordered_at,l.sample_status,l.result_status,u.fullName AS patient_name,d.doctorName AS doctor_name FROM lab_orders l JOIN users u ON u.id=l.patient_user_id JOIN doctors d ON d.id=l.doctor_id ORDER BY l.ordered_at DESC,l.id DESC",'fields'=>[
        ['name'=>'patient_user_id','label'=>'Patient','type'=>'patient','required'=>true],['name'=>'doctor_id','label'=>'Ordering doctor','type'=>'doctor','required'=>true],['name'=>'test_name','label'=>'Test name','type'=>'text','required'=>true,'max'=>180],['name'=>'ordered_at','label'=>'Order date','type'=>'date','required'=>true]
    ]],
    'billing' => ['title'=>'Billing & invoices','description'=>'Create invoices and follow payment status.','table'=>'invoices','icon'=>'fa-file-text-o','search'=>'i.invoice_number','list'=>"SELECT i.id,i.invoice_number,i.service_description,i.amount,i.payment_method,i.status,i.due_date,i.created_at,u.fullName AS patient_name FROM invoices i JOIN users u ON u.id=i.patient_user_id ORDER BY i.created_at DESC",'fields'=>[
        ['name'=>'patient_user_id','label'=>'Patient','type'=>'patient','required'=>true],['name'=>'service_description','label'=>'Service','type'=>'text','required'=>true,'max'=>255],['name'=>'amount','label'=>'Amount','type'=>'decimal','required'=>true,'min'=>0],['name'=>'payment_method','label'=>'Payment method','type'=>'select','options'=>[''=>'Not recorded','cash'=>'Cash','card'=>'Card','bank transfer'=>'Bank transfer','insurance'=>'Insurance']],['name'=>'due_date','label'=>'Due date','type'=>'date']
    ]],
    'prescriptions' => ['title'=>'Prescriptions','description'=>'Review prescribed medicines and instructions.','table'=>'prescriptions','icon'=>'fa-stethoscope','search'=>'p.medication','list'=>"SELECT p.id,p.medication,p.dosage,p.frequency,p.duration,p.instructions,p.created_at,u.fullName AS patient_name,d.doctorName AS doctor_name FROM prescriptions p JOIN users u ON u.id=p.patient_user_id JOIN doctors d ON d.id=p.doctor_id ORDER BY p.created_at DESC",'fields'=>[
        ['name'=>'patient_user_id','label'=>'Patient','type'=>'patient','required'=>true],['name'=>'doctor_id','label'=>'Prescribing doctor','type'=>'doctor','required'=>true],['name'=>'medication','label'=>'Medication','type'=>'text','required'=>true,'max'=>180],['name'=>'dosage','label'=>'Dosage','type'=>'text','required'=>true,'max'=>100],['name'=>'frequency','label'=>'Frequency','type'=>'text','required'=>true,'max'=>100],['name'=>'duration','label'=>'Duration','type'=>'text','required'=>true,'max'=>100],['name'=>'instructions','label'=>'Instructions','type'=>'textarea','max'=>1000]
    ]],
];
$moduleKey = (string)($_GET['module'] ?? 'departments');
if (!isset($modules[$moduleKey])) { http_response_code(404); exit('Module not found.'); }
$module = $modules[$moduleKey];
$message = '';
$error = '';
$rows = null;
$count = 0;
$search = trim((string)($_GET['q'] ?? ''));
$tableCheck = hms_db_query($con, 'SELECT TABLE_NAME FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=?', [$module['table']]);
$ready = $tableCheck && $tableCheck->num_rows > 0;

if ($ready && $_SERVER['REQUEST_METHOD'] === 'POST') {
    hms_verify_csrf();
    $action = (string)($_POST['action'] ?? 'create');
    try {
        if ($action === 'status') {
            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
            if (!$id || !in_array($moduleKey, ['departments','beds','pharmacy','laboratory','billing'], true)) throw new InvalidArgumentException('Choose a valid record.');
            if ($moduleKey === 'departments') { $value=(string)($_POST['status']??''); if(!in_array($value,['active','inactive'],true)) throw new InvalidArgumentException('Choose a valid status.'); hms_db_query($con,'UPDATE departments SET status=? WHERE id=?',[$value,$id]); }
            if ($moduleKey === 'beds') { $value=(string)($_POST['status']??''); if(!in_array($value,['available','occupied','reserved','maintenance'],true)) throw new InvalidArgumentException('Choose a valid bed status.'); hms_db_query($con,'UPDATE hospital_beds SET status=? WHERE id=?',[$value,$id]); }
            if ($moduleKey === 'pharmacy') { $quantity=filter_var($_POST['quantity']??null,FILTER_VALIDATE_INT); if($quantity===false||$quantity<0||$quantity>100000) throw new InvalidArgumentException('Enter a valid stock quantity.'); hms_db_query($con,'UPDATE pharmacy_medicines SET quantity=? WHERE id=?',[$quantity,$id]); }
            if ($moduleKey === 'laboratory') { $sample=(string)($_POST['sample_status']??'');$result=(string)($_POST['result_status']??'');if(!in_array($sample,['pending','collected','processing'],true)||!in_array($result,['pending','processing','completed','critical'],true))throw new InvalidArgumentException('Choose valid laboratory statuses.');hms_db_query($con,'UPDATE lab_orders SET sample_status=?,result_status=? WHERE id=?',[$sample,$result,$id]); }
            if ($moduleKey === 'billing') { $value=(string)($_POST['status']??'');if(!in_array($value,['pending','paid','overdue','cancelled'],true))throw new InvalidArgumentException('Choose a valid invoice status.');hms_db_query($con,"UPDATE invoices SET status=?, paid_at=IF(?='paid',COALESCE(paid_at,NOW()),NULL) WHERE id=?",[$value,$value,$id]); }
            header('Location: operations.php?module='.rawurlencode($moduleKey).'&saved=1'); exit;
        }
        $values = [];
        foreach ($module['fields'] as $field) {
            $name = $field['name']; $value = trim((string)($_POST[$name] ?? ''));
            if (!empty($field['required']) && $value === '') throw new InvalidArgumentException($field['label'].' is required.');
            if (isset($field['max']) && strlen($value) > $field['max']) throw new InvalidArgumentException($field['label'].' is too long.');
            if ($value !== '' && $field['type'] === 'number' && (!ctype_digit($value) || (int)$value < $field['min'] || (int)$value > $field['max'])) throw new InvalidArgumentException('Enter a valid '.$field['label'].'.');
            if ($value !== '' && $field['type'] === 'decimal' && (!is_numeric($value) || (float)$value < $field['min'] || (float)$value > 9999999999)) throw new InvalidArgumentException('Enter a valid '.$field['label'].'.');
            if ($value !== '' && $field['type'] === 'date' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) throw new InvalidArgumentException('Enter a valid '.$field['label'].'.');
            if ($field['type'] === 'select' && !array_key_exists($value, $field['options'])) throw new InvalidArgumentException('Choose a valid '.$field['label'].'.');
            if (in_array($field['type'], ['patient','doctor'], true)) {
                $id = filter_var($value, FILTER_VALIDATE_INT);
                if (!$id || $id < 1) { if(!empty($field['required'])) throw new InvalidArgumentException('Choose a valid '.$field['label'].'.'); $value=''; }
                else { $exists = $field['type']==='patient' ? hms_db_query($con,'SELECT id FROM users WHERE id=?',[$id]) : hms_db_query($con,'SELECT id FROM doctors WHERE id=?',[$id]); if(!$exists || !$exists->num_rows) throw new InvalidArgumentException('The selected '.$field['label'].' was not found.'); $value=(int)$id; }
            }
            if ($field['type'] === 'number') $value = $value === '' ? 0 : (int)$value;
            if ($field['type'] === 'decimal') $value = $value === '' ? 0 : number_format((float)$value, 2, '.', '');
            $values[$name] = $value === '' ? null : $value;
        }
        if ($moduleKey === 'beds') {
            $con->begin_transaction();
            $room = hms_db_query($con,'SELECT id FROM hospital_rooms WHERE room_number=?',[$values['room_number']])->fetch_assoc();
            if ($room) { if($room['ward']!==$values['ward']||$room['room_type']!==$values['room_type']) hms_db_query($con,'UPDATE hospital_rooms SET ward=?,room_type=? WHERE id=?',[$values['ward'],$values['room_type'],(int)$room['id']]); $roomId=(int)$room['id']; }
            else { hms_db_query($con,'INSERT INTO hospital_rooms (room_number,ward,room_type) VALUES (?,?,?)',[$values['room_number'],$values['ward'],$values['room_type']]); $roomId=(int)$con->insert_id; }
            hms_db_query($con,'INSERT INTO hospital_beds (room_id,bed_label) VALUES (?,?)',[$roomId,$values['bed_label']]);
            $con->commit();
        } else {
            if ($moduleKey === 'billing') $values = ['invoice_number'=>'MT-'.date('ym').'-'.bin2hex(random_bytes(4))] + $values;
            $columns = array_keys($values);
            $sql = 'INSERT INTO '.$module['table'].' ('.implode(',', $columns).') VALUES ('.implode(',', array_fill(0,count($columns),'?')).')';
            hms_db_query($con, $sql, array_values($values));
        }
        header('Location: operations.php?module='.rawurlencode($moduleKey).'&saved=1'); exit;
    } catch (InvalidArgumentException $exception) { $error=$exception->getMessage(); }
    catch (Throwable $exception) { if($con->errno) $con->rollback(); error_log('HMS admin module write failed: '.$exception->getMessage()); $error='The record could not be saved. Check required fields and try again.'; }
}
if (!$ready) { $error='This module needs the optional operations database migration before it can be used.'; }
if (isset($_GET['saved'])) $message='Changes saved successfully.';
if ($ready) {
    $countResult = hms_db_query($con, 'SELECT COUNT(*) AS total FROM '.$module['table']);
    $count = (int)($countResult->fetch_assoc()['total'] ?? 0);
    $sql = $module['list']; $params=[];
    if ($search !== '') { $orderPos=strripos($sql,' ORDER BY '); $orderClause=$orderPos===false?'':substr($sql,$orderPos); if($orderPos!==false)$sql=substr($sql,0,$orderPos); $sql .= ' WHERE '.$module['search'].' LIKE ?'.$orderClause; $params[]='%'.substr($search,0,100).'%'; }
    $rows = hms_db_query($con, $sql, $params);
}
$patients = in_array($moduleKey,['laboratory','billing','prescriptions'],true) ? hms_db_query($con,'SELECT id,fullName FROM users ORDER BY fullName LIMIT 500') : null;
$doctors = in_array($moduleKey,['departments','laboratory','prescriptions'],true) ? hms_db_query($con,'SELECT id,doctorName FROM doctors ORDER BY doctorName LIMIT 500') : null;

function op_field(array $field, $patients, $doctors): string {
    $name=hms_h($field['name']); $label=hms_h($field['label']); $required=!empty($field['required'])?' required':''; $html='<div class="form-group"><label for="op-'.$name.'">'.$label.(!empty($field['required'])?' <span aria-hidden="true">*</span>':'').'</label>';
    if ($field['type']==='select') { $html.='<select class="form-control" id="op-'.$name.'" name="'.$name.'"'.$required.'>'; foreach($field['options'] as $value=>$text) $html.='<option value="'.hms_h($value).'">'.hms_h($text).'</option>'; $html.='</select>'; }
    elseif ($field['type']==='patient'||$field['type']==='doctor') { $result=$field['type']==='patient'?$patients:$doctors; $html.='<select class="form-control" id="op-'.$name.'" name="'.$name.'"'.$required.'><option value="">Choose…</option>'; if($result) while($row=$result->fetch_assoc()) $html.='<option value="'.hms_h($row['id']).'">'.hms_h($row[$field['type']==='patient'?'fullName':'doctorName']).'</option>'; $html.='</select>'; }
    elseif ($field['type']==='textarea') $html.='<textarea class="form-control" id="op-'.$name.'" name="'.$name.'" maxlength="'.(int)$field['max'].'" rows="3"></textarea>';
    else { $type=in_array($field['type'],['number','decimal','date'],true)?$field['type']:'text'; $html.='<input class="form-control" id="op-'.$name.'" name="'.$name.'" type="'.$type.'"'.(isset($field['max'])&&$type==='text'?' maxlength="'.(int)$field['max'].'"':'').($type==='number'?' min="'.(int)$field['min'].'" max="'.(int)$field['max'].'" step="1"':'').($type==='decimal'?' min="0" step="0.01"':'').$required.'>'; }
    return $html.'</div>';
}
function op_status(string $value): string { $known=['active'=>'success','available'=>'success','paid'=>'success','completed'=>'success','collected'=>'success','in stock'=>'success','occupied'=>'info','reserved'=>'warning','pending'=>'warning','processing'=>'info','low stock'=>'warning','overdue'=>'danger','critical'=>'danger','maintenance'=>'danger','expired'=>'danger','out of stock'=>'danger','inactive'=>'muted','cancelled'=>'muted']; $class=$known[strtolower($value)]??'muted'; return '<span class="op-status '.$class.'">'.hms_h(ucfirst($value)).'</span>'; }
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=hms_h($module['title'])?> · MedTech</title>
<link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css"><link rel="stylesheet" href="vendor/fontawesome/css/font-awesome.min.css"><link rel="stylesheet" href="vendor/themify-icons/themify-icons.min.css"><link rel="stylesheet" href="assets/css/styles.css"><link rel="stylesheet" href="assets/css/plugins.css"><link rel="stylesheet" href="../assets/css/medtech-app.css"><link rel="stylesheet" href="../assets/css/medtech-enterprise.css"></head><body><div id="app"><?php include 'include/sidebar.php';?><div class="app-content"><?php include 'include/header.php';?><main class="main-content"><div class="wrap-content container" id="container">
<section id="page-title"><div class="op-page-heading"><div><span class="op-eyebrow">HOSPITAL OPERATIONS</span><h1><?=hms_h($module['title'])?></h1><p><?=hms_h($module['description'])?></p></div><span class="op-count"><strong><?=number_format($count)?></strong><small>records</small></span></div></section>
<?php if($message):?><div class="alert alert-success" role="status"><?=hms_h($message)?></div><?php endif;?><?php if($error):?><div class="alert alert-<?=!$ready?'warning':'danger'?>" role="alert"><?=hms_h($error)?><?php if(!$ready):?> Apply <code>SQL File/migrations/003_hospital_operations.sql</code> to the <code>hms</code> database, then reload this page.<?php endif;?></div><?php endif;?>
<div class="op-layout"><section class="op-panel"><div class="op-panel-heading"><div><span class="op-eyebrow">DIRECTORY</span><h2><?=hms_h($module['title'])?> overview</h2></div><form method="get" class="op-search"><input type="hidden" name="module" value="<?=hms_h($moduleKey)?>"><label class="sr-only" for="op-search">Search <?=hms_h(strtolower($module['title']))?></label><input class="form-control" id="op-search" type="search" name="q" value="<?=hms_h($search)?>" placeholder="Search records"><button class="btn btn-default" type="submit" aria-label="Search"><i class="fa fa-search"></i></button></form></div>
<?php if($ready&&$rows&&$rows->num_rows):?><div class="table-responsive"><table class="table op-table"><thead><tr><?php foreach($rows->fetch_fields() as $column):?><th><?=hms_h(str_replace('_',' ',preg_replace('/([a-z])([A-Z])/','$1 $2',$column->name)))?></th><?php endforeach;?><th>Update</th></tr></thead><tbody><?php while($row=$rows->fetch_assoc()):?><tr><?php foreach($row as $key=>$value):?><td><?php if(in_array($key,['status','sample_status','result_status','stock_status'],true)&&$value!==null):?><?=op_status((string)$value)?><?php elseif(in_array($key,['amount','unit_price'],true)):?>₹<?=number_format((float)$value,2)?><?php else:?><?=hms_h($value??'—')?><?php endif;?></td><?php endforeach;?><td><?php if(in_array($moduleKey,['departments','beds','pharmacy','laboratory','billing'],true)):?><details class="op-row-action"><summary>Update</summary><form method="post"><?=hms_csrf_field()?><input type="hidden" name="action" value="status"><input type="hidden" name="id" value="<?=hms_h($row['id'])?>"><?php if($moduleKey==='departments'):?><select class="form-control" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select><?php elseif($moduleKey==='beds'):?><select class="form-control" name="status"><option value="available">Available</option><option value="occupied">Occupied</option><option value="reserved">Reserved</option><option value="maintenance">Maintenance</option></select><?php elseif($moduleKey==='pharmacy'):?><label>Quantity on hand<input class="form-control" name="quantity" type="number" min="0" max="100000" value="<?=hms_h($row['quantity'])?>" required></label><?php elseif($moduleKey==='laboratory'):?><select class="form-control" name="sample_status"><option value="pending">Sample pending</option><option value="collected">Sample collected</option><option value="processing">Processing</option></select><select class="form-control" name="result_status"><option value="pending">Result pending</option><option value="processing">Processing</option><option value="completed">Completed</option><option value="critical">Critical</option></select><?php else:?><select class="form-control" name="status"><option value="pending">Pending</option><option value="paid">Paid</option><option value="overdue">Overdue</option><option value="cancelled">Cancelled</option></select><?php endif;?><button class="btn btn-primary btn-sm">Save</button></form></details><?php else:?>—<?php endif;?></td></tr><?php endwhile;?></tbody></table></div><?php else:?><div class="op-empty"><span class="op-empty-icon"><i class="fa <?=hms_h($module['icon'])?>"></i></span><h3><?= $search!==''?'No matching records':'No records yet'?></h3><p><?= $search!==''?'Try a different search term.':'Add a record using the form. Your data will appear here.'?></p></div><?php endif;?></section>
<aside class="op-panel op-create"><span class="op-eyebrow">ADD TO WORKSPACE</span><h2>New <?=hms_h(rtrim($module['title'],'s'))?></h2><p class="op-muted">Required fields are marked with an asterisk.</p><?php if($ready):?><form method="post" novalidate><?=hms_csrf_field()?><?php foreach($module['fields'] as $field) echo op_field($field,$patients,$doctors);?><button class="btn btn-primary btn-block" type="submit"><i class="fa fa-plus"></i> Save record</button></form><?php endif;?></aside></div>
</div></main><?php include 'include/footer.php';?></div></div><script src="vendor/jquery/jquery.min.js"></script><script src="vendor/bootstrap/js/bootstrap.min.js"></script><script src="assets/js/main.js"></script><script>jQuery(function(){Main.init();});</script></body></html>
