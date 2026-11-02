<?php
require_once __DIR__.'/include/config.php'; require_once __DIR__.'/include/billing.php'; hms_start_session(); hms_require_role('patient');
$id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT)?:0;
if(!hms_billing_ready($con)) exit(hms_billing_notice());
$payment=hms_db_query($con,'SELECT p.*,i.invoice_number FROM billing_payments p JOIN billing_invoices i ON i.id=p.invoice_id WHERE p.id=? AND i.patient_user_id=?',[$id,(int)$_SESSION['id']])->fetch_assoc();
if(!$payment){http_response_code(404);exit('Receipt not found.');}
?>
<!doctype html><html><head><meta charset="utf-8"><title>Receipt <?=hms_h($payment['receipt_number'])?></title><link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css"><style>@media print{button,a{display:none}}main{max-width:700px;margin:30px auto}</style></head><body><main><a href="invoice.php?id=<?=hms_h($payment['invoice_id'])?>">Back</a><button onclick="print()" class="btn btn-primary pull-right">Print receipt</button><h1>MedTech Hospital</h1><h2>Receipt <?=hms_h($payment['receipt_number'])?></h2><p>Invoice <?=hms_h($payment['invoice_number'])?></p><h3>₹<?=number_format((float)$payment['amount'],2)?></h3><p><?=hms_h($payment['received_at'])?> · <?=hms_h($payment['payment_method'])?><br>Reference: <?=hms_h($payment['transaction_reference'])?></p></main></body></html>
