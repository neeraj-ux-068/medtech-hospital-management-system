-- MedTech Stage 7: additive billing, invoice items, and payments. Run once after backup.
CREATE TABLE IF NOT EXISTS billing_invoices (
 id int NOT NULL AUTO_INCREMENT, invoice_number varchar(40) NOT NULL, patient_user_id int NOT NULL,
 appointment_id int DEFAULT NULL, issue_date date NOT NULL, due_date date DEFAULT NULL, status varchar(20) NOT NULL DEFAULT 'unpaid',
 subtotal decimal(12,2) NOT NULL DEFAULT 0, discount_amount decimal(12,2) NOT NULL DEFAULT 0, tax_amount decimal(12,2) NOT NULL DEFAULT 0,
 total_amount decimal(12,2) NOT NULL DEFAULT 0, amount_paid decimal(12,2) NOT NULL DEFAULT 0, balance_amount decimal(12,2) NOT NULL DEFAULT 0,
 created_by_admin_id int DEFAULT NULL, created_at timestamp NOT NULL DEFAULT current_timestamp(), updated_at timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
 PRIMARY KEY(id), UNIQUE KEY uq_billing_invoice_number(invoice_number), KEY idx_billing_patient_status(patient_user_id,status), KEY idx_billing_issue_date(issue_date),
 CONSTRAINT fk_billing_invoice_patient FOREIGN KEY(patient_user_id) REFERENCES users(id) ON DELETE RESTRICT,
 CONSTRAINT fk_billing_invoice_appointment FOREIGN KEY(appointment_id) REFERENCES appointment(id) ON DELETE SET NULL,
 CONSTRAINT fk_billing_invoice_admin FOREIGN KEY(created_by_admin_id) REFERENCES admin(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS billing_invoice_items (
 id int NOT NULL AUTO_INCREMENT, invoice_id int NOT NULL, description varchar(255) NOT NULL, quantity decimal(10,2) NOT NULL DEFAULT 1,
 unit_price decimal(12,2) NOT NULL, line_total decimal(12,2) NOT NULL, source_type varchar(30) DEFAULT NULL, source_id int DEFAULT NULL,
 PRIMARY KEY(id), KEY idx_billing_item_invoice(invoice_id), UNIQUE KEY uq_billing_source(source_type,source_id),
 CONSTRAINT fk_billing_item_invoice FOREIGN KEY(invoice_id) REFERENCES billing_invoices(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS billing_payments (
 id int NOT NULL AUTO_INCREMENT, receipt_number varchar(40) NOT NULL, invoice_id int NOT NULL, amount decimal(12,2) NOT NULL, payment_method varchar(30) NOT NULL,
 transaction_reference varchar(120) DEFAULT NULL, received_at datetime NOT NULL DEFAULT current_timestamp(), received_by_admin_id int DEFAULT NULL,
 PRIMARY KEY(id), UNIQUE KEY uq_billing_receipt_number(receipt_number), KEY idx_billing_payment_invoice(invoice_id,received_at),
 CONSTRAINT fk_billing_payment_invoice FOREIGN KEY(invoice_id) REFERENCES billing_invoices(id) ON DELETE RESTRICT,
 CONSTRAINT fk_billing_payment_admin FOREIGN KEY(received_by_admin_id) REFERENCES admin(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
