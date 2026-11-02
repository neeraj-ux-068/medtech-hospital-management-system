-- MedTech Stage 6: normalized prescriptions and laboratory workflow.
-- Additive only. Run once against the existing hms database after taking a backup.

CREATE TABLE IF NOT EXISTS prescription_headers (
  id int NOT NULL AUTO_INCREMENT, patient_user_id int NOT NULL, doctor_id int NOT NULL,
  appointment_id int DEFAULT NULL, issue_date date NOT NULL, clinical_notes text DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id), KEY idx_rx_patient_date (patient_user_id,issue_date), KEY idx_rx_doctor_date (doctor_id,issue_date), KEY idx_rx_appointment (appointment_id),
  CONSTRAINT fk_rx_patient FOREIGN KEY (patient_user_id) REFERENCES users(id) ON DELETE RESTRICT,
  CONSTRAINT fk_rx_doctor FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE RESTRICT,
  CONSTRAINT fk_rx_appointment FOREIGN KEY (appointment_id) REFERENCES appointment(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS prescription_items (
  id int NOT NULL AUTO_INCREMENT, prescription_id int NOT NULL, medicine_name varchar(180) NOT NULL,
  dosage varchar(100) NOT NULL, frequency varchar(100) NOT NULL, duration varchar(100) NOT NULL,
  instructions varchar(1000) DEFAULT NULL, PRIMARY KEY (id), KEY idx_rx_item_header (prescription_id),
  CONSTRAINT fk_rx_item_header FOREIGN KEY (prescription_id) REFERENCES prescription_headers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS laboratory_requests (
  id int NOT NULL AUTO_INCREMENT, patient_user_id int NOT NULL, doctor_id int NOT NULL, appointment_id int DEFAULT NULL,
  test_name varchar(180) NOT NULL, clinical_note varchar(1000) DEFAULT NULL,
  status varchar(20) NOT NULL DEFAULT 'requested', requested_at timestamp NOT NULL DEFAULT current_timestamp(),
  updated_at timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (id), KEY idx_lab_request_patient (patient_user_id,requested_at), KEY idx_lab_request_status (status,requested_at), KEY idx_lab_request_doctor (doctor_id,requested_at),
  CONSTRAINT fk_lab_request_patient FOREIGN KEY (patient_user_id) REFERENCES users(id) ON DELETE RESTRICT,
  CONSTRAINT fk_lab_request_doctor FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE RESTRICT,
  CONSTRAINT fk_lab_request_appointment FOREIGN KEY (appointment_id) REFERENCES appointment(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS laboratory_results (
  id int NOT NULL AUTO_INCREMENT, laboratory_request_id int NOT NULL, result_value text DEFAULT NULL,
  units varchar(80) DEFAULT NULL, reference_range varchar(255) DEFAULT NULL, remarks varchar(1000) DEFAULT NULL,
  completed_at datetime DEFAULT NULL, completed_by_admin_id int DEFAULT NULL, revised_at datetime DEFAULT NULL,
  PRIMARY KEY (id), UNIQUE KEY uq_lab_result_request (laboratory_request_id),
  CONSTRAINT fk_lab_result_request FOREIGN KEY (laboratory_request_id) REFERENCES laboratory_requests(id) ON DELETE CASCADE,
  CONSTRAINT fk_lab_result_admin FOREIGN KEY (completed_by_admin_id) REFERENCES admin(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS laboratory_result_audit (
  id int NOT NULL AUTO_INCREMENT, laboratory_result_id int NOT NULL, admin_id int DEFAULT NULL,
  action varchar(40) NOT NULL, previous_result text DEFAULT NULL, new_result text DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(), PRIMARY KEY (id), KEY idx_lab_audit_result (laboratory_result_id,created_at),
  CONSTRAINT fk_lab_audit_result FOREIGN KEY (laboratory_result_id) REFERENCES laboratory_results(id) ON DELETE CASCADE,
  CONSTRAINT fk_lab_audit_admin FOREIGN KEY (admin_id) REFERENCES admin(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
