-- MedTech Hospital Management System: operational modules
-- Additive only: existing tables and records are not modified or removed.
-- Back up hms before importing. Apply once through phpMyAdmin.

CREATE TABLE IF NOT EXISTS `departments` (
    `id` int NOT NULL AUTO_INCREMENT,
    `department_name` varchar(120) NOT NULL,
    `head_doctor_id` int DEFAULT NULL,
    `description` varchar(500) DEFAULT NULL,
    `status` varchar(20) NOT NULL DEFAULT 'active',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_departments_name` (`department_name`),
    KEY `idx_departments_head` (`head_doctor_id`),
    CONSTRAINT `fk_departments_head` FOREIGN KEY (`head_doctor_id`) REFERENCES `doctors` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `hospital_rooms` (
    `id` int NOT NULL AUTO_INCREMENT,
    `room_number` varchar(30) NOT NULL,
    `ward` varchar(100) NOT NULL,
    `room_type` varchar(60) NOT NULL DEFAULT 'General',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_hospital_room_number` (`room_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `hospital_beds` (
    `id` int NOT NULL AUTO_INCREMENT,
    `room_id` int NOT NULL,
    `bed_label` varchar(30) NOT NULL,
    `status` varchar(20) NOT NULL DEFAULT 'available',
    `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_hospital_bed_label` (`room_id`, `bed_label`),
    KEY `idx_hospital_bed_status` (`status`),
    CONSTRAINT `fk_hospital_bed_room` FOREIGN KEY (`room_id`) REFERENCES `hospital_rooms` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pharmacy_medicines` (
    `id` int NOT NULL AUTO_INCREMENT,
    `medicine_name` varchar(180) NOT NULL,
    `category` varchar(100) NOT NULL,
    `quantity` int unsigned NOT NULL DEFAULT 0,
    `reorder_level` int unsigned NOT NULL DEFAULT 10,
    `expiry_date` date DEFAULT NULL,
    `supplier` varchar(180) DEFAULT NULL,
    `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_medicine_name` (`medicine_name`),
    KEY `idx_medicine_expiry` (`expiry_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `lab_orders` (
    `id` int NOT NULL AUTO_INCREMENT,
    `patient_user_id` int NOT NULL,
    `doctor_id` int NOT NULL,
    `test_name` varchar(180) NOT NULL,
    `sample_status` varchar(30) NOT NULL DEFAULT 'pending',
    `result_status` varchar(30) NOT NULL DEFAULT 'pending',
    `ordered_at` date NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_lab_patient_date` (`patient_user_id`, `ordered_at`),
    KEY `idx_lab_status` (`sample_status`, `result_status`),
    CONSTRAINT `fk_lab_patient` FOREIGN KEY (`patient_user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_lab_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `invoices` (
    `id` int NOT NULL AUTO_INCREMENT,
    `invoice_number` varchar(40) NOT NULL,
    `patient_user_id` int NOT NULL,
    `service_description` varchar(255) NOT NULL,
    `amount` decimal(12,2) NOT NULL,
    `payment_method` varchar(40) DEFAULT NULL,
    `status` varchar(20) NOT NULL DEFAULT 'pending',
    `due_date` date DEFAULT NULL,
    `paid_at` datetime DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_invoice_number` (`invoice_number`),
    KEY `idx_invoice_patient_status` (`patient_user_id`, `status`),
    KEY `idx_invoice_created` (`created_at`),
    CONSTRAINT `fk_invoice_patient` FOREIGN KEY (`patient_user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `prescriptions` (
    `id` int NOT NULL AUTO_INCREMENT,
    `patient_user_id` int NOT NULL,
    `doctor_id` int NOT NULL,
    `medication` varchar(180) NOT NULL,
    `dosage` varchar(100) NOT NULL,
    `frequency` varchar(100) NOT NULL,
    `duration` varchar(100) NOT NULL,
    `instructions` varchar(1000) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_prescription_patient_date` (`patient_user_id`, `created_at`),
    CONSTRAINT `fk_prescription_patient` FOREIGN KEY (`patient_user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_prescription_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

