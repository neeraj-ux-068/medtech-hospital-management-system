-- MedTech Stage 9: additive in-app notifications. Run once after a database backup.
CREATE TABLE IF NOT EXISTS notifications (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  recipient_role VARCHAR(20) NOT NULL,
  recipient_id INT NOT NULL,
  notification_type VARCHAR(60) NOT NULL,
  message VARCHAR(500) NOT NULL,
  related_type VARCHAR(60) DEFAULT NULL,
  related_id INT DEFAULT NULL,
  link_path VARCHAR(255) DEFAULT NULL,
  event_key VARCHAR(180) DEFAULT NULL,
  read_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_notification_event (event_key),
  KEY idx_notification_recipient_unread (recipient_role, recipient_id, read_at, created_at),
  KEY idx_notification_related (related_type, related_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELIMITER $$
CREATE TRIGGER trg_notification_appointment_insert AFTER INSERT ON appointment FOR EACH ROW
BEGIN
  INSERT INTO notifications(recipient_role,recipient_id,notification_type,message,related_type,related_id,link_path,event_key)
  VALUES ('patient',NEW.userId,'Appointment booked','Your appointment request is awaiting confirmation.','appointment',NEW.id,CONCAT('appointment-details.php?id=',NEW.id),CONCAT('appointment-booked:',NEW.id));
  INSERT INTO notifications(recipient_role,recipient_id,notification_type,message,related_type,related_id,link_path,event_key)
  SELECT 'admin',id,'Appointment booked','A new appointment request needs review.','appointment',NEW.id,CONCAT('appointment-details.php?id=',NEW.id),CONCAT('appointment-booked:',NEW.id,':a',id) FROM admin;
END$$
CREATE TRIGGER trg_notification_appointment_update AFTER UPDATE ON appointment FOR EACH ROW
BEGIN
  IF NEW.status <> OLD.status THEN
    INSERT INTO notifications(recipient_role,recipient_id,notification_type,message,related_type,related_id,link_path,event_key)
    VALUES ('patient',NEW.userId,CONCAT('Appointment ',NEW.status),CONCAT('Your appointment status is now ',NEW.status,'.'),'appointment',NEW.id,CONCAT('appointment-details.php?id=',NEW.id),CONCAT('appointment-status:',NEW.id,':',NEW.status));
  END IF;
END$$
CREATE TRIGGER trg_notification_prescription AFTER INSERT ON prescription_headers FOR EACH ROW
BEGIN
  INSERT INTO notifications(recipient_role,recipient_id,notification_type,message,related_type,related_id,link_path,event_key)
  VALUES ('patient',NEW.patient_user_id,'Prescription available','A new prescription is available in your portal.','prescription',NEW.id,CONCAT('prescription-details.php?id=',NEW.id),CONCAT('prescription-created:',NEW.id));
END$$
CREATE TRIGGER trg_notification_lab_request AFTER INSERT ON laboratory_requests FOR EACH ROW
BEGIN
  INSERT INTO notifications(recipient_role,recipient_id,notification_type,message,related_type,related_id,link_path,event_key)
  VALUES ('patient',NEW.patient_user_id,'Laboratory test requested','A laboratory test has been requested.','laboratory_request',NEW.id,'laboratory.php',CONCAT('laboratory-requested:',NEW.id));
  INSERT INTO notifications(recipient_role,recipient_id,notification_type,message,related_type,related_id,link_path,event_key)
  SELECT 'admin',id,'Laboratory test requested','A laboratory test request is awaiting processing.','laboratory_request',NEW.id,'admin/laboratory.php',CONCAT('laboratory-requested:',NEW.id,':a',id) FROM admin;
END$$
CREATE TRIGGER trg_notification_lab_update AFTER UPDATE ON laboratory_requests FOR EACH ROW
BEGIN
  IF NEW.status <> OLD.status THEN
    INSERT INTO notifications(recipient_role,recipient_id,notification_type,message,related_type,related_id,link_path,event_key)
    VALUES ('patient',NEW.patient_user_id,'Laboratory update',CONCAT('Your laboratory request status is now ',NEW.status,'.'),'laboratory_request',NEW.id,'laboratory.php',CONCAT('laboratory-status:',NEW.id,':',NEW.status));
  END IF;
END$$
CREATE TRIGGER trg_notification_invoice AFTER INSERT ON billing_invoices FOR EACH ROW
BEGIN
  INSERT INTO notifications(recipient_role,recipient_id,notification_type,message,related_type,related_id,link_path,event_key)
  VALUES ('patient',NEW.patient_user_id,'Invoice created','A new invoice is available in your portal.','invoice',NEW.id,CONCAT('invoice.php?id=',NEW.id),CONCAT('invoice-created:',NEW.id));
END$$
CREATE TRIGGER trg_notification_payment AFTER INSERT ON billing_payments FOR EACH ROW
BEGIN
  INSERT INTO notifications(recipient_role,recipient_id,notification_type,message,related_type,related_id,link_path,event_key)
  SELECT 'patient',i.patient_user_id,'Payment recorded','A payment has been recorded for your invoice.','invoice',i.id,CONCAT('invoice.php?id=',i.id),CONCAT('payment-recorded:',NEW.id) FROM billing_invoices i WHERE i.id=NEW.invoice_id;
END$$
DELIMITER ;
