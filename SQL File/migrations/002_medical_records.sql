-- MedTech Hospital Management System: Stage 5 structured clinical records
-- Non-destructive. Requires migration 001_appointment_workflow.sql first.
-- Back up the hms database, then run this file once. Existing tblmedicalhistory
-- rows are intentionally retained and are displayed as legacy history by the app.

CREATE TABLE `medical_records` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `patientUserId` int(11) NOT NULL,
    `doctorId` int(11) NOT NULL,
    `appointmentId` int(11) DEFAULT NULL,
    `recordDate` date NOT NULL,
    `symptoms` text DEFAULT NULL,
    `diagnosis` varchar(255) NOT NULL,
    `clinicalNotes` mediumtext DEFAULT NULL,
    `treatmentPlan` mediumtext DEFAULT NULL,
    `followUpInstructions` mediumtext DEFAULT NULL,
    `bloodPressure` varchar(20) DEFAULT NULL,
    `temperature` decimal(5,2) DEFAULT NULL,
    `pulse` smallint unsigned DEFAULT NULL,
    `weight` decimal(6,2) DEFAULT NULL,
    `height` decimal(5,2) DEFAULT NULL,
    `updatedByDoctorId` int(11) DEFAULT NULL,
    `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
    `updatedAt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_medical_records_patient_date` (`patientUserId`, `recordDate`),
    KEY `idx_medical_records_doctor_date` (`doctorId`, `recordDate`),
    KEY `idx_medical_records_appointment` (`appointmentId`),
    KEY `idx_medical_records_diagnosis` (`diagnosis`(100)),
    CONSTRAINT `fk_medical_record_patient` FOREIGN KEY (`patientUserId`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_medical_record_doctor` FOREIGN KEY (`doctorId`) REFERENCES `doctors` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_medical_record_appointment` FOREIGN KEY (`appointmentId`) REFERENCES `appointment` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_medical_record_updater` FOREIGN KEY (`updatedByDoctorId`) REFERENCES `doctors` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Verification after applying:
SELECT COUNT(*) AS `structured_records` FROM `medical_records`;
SELECT COUNT(*) AS `preserved_legacy_records` FROM `tblmedicalhistory`;
