-- MedTech Hospital Management System: Stage 4 appointment workflow
-- Safe, additive migration for an existing HMS database. It does not delete rows.
-- Before running: export a full backup of hms using phpMyAdmin or mysqldump.
-- Run once against the existing `hms` database. Do not re-run after successful application.

-- Existing flags are retained for compatibility with legacy pages.
-- Legacy rows with both flags set to 1 are confirmed; all other combinations are cancelled.
ALTER TABLE `appointment`
    ADD COLUMN `status` varchar(20) NOT NULL DEFAULT 'confirmed' AFTER `doctorStatus`,
    ADD COLUMN `visitReason` text DEFAULT NULL AFTER `status`,
    ADD COLUMN `doctorNotes` mediumtext DEFAULT NULL AFTER `visitReason`;

UPDATE `appointment`
SET `status` = CASE
    WHEN `userStatus` = 1 AND `doctorStatus` = 1 THEN 'confirmed'
    ELSE 'cancelled'
END;

ALTER TABLE `appointment`
    ADD KEY `idx_appointment_doctor_date_status` (`doctorId`, `appointmentDate`, `status`),
    ADD KEY `idx_appointment_patient_date_status` (`userId`, `appointmentDate`, `status`);

CREATE TABLE `doctor_availability` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `doctorId` int(11) NOT NULL,
    `weekday` tinyint unsigned NOT NULL COMMENT 'ISO weekday: Monday=1 through Sunday=7',
    `startTime` time NOT NULL,
    `endTime` time NOT NULL,
    `slotMinutes` smallint unsigned NOT NULL DEFAULT 30,
    `maxPerSlot` smallint unsigned NOT NULL DEFAULT 1,
    `isActive` tinyint(1) NOT NULL DEFAULT 1,
    `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_doctor_availability_window` (`doctorId`, `weekday`, `startTime`, `endTime`),
    KEY `idx_doctor_availability_day` (`doctorId`, `weekday`, `isActive`),
    CONSTRAINT `fk_doctor_availability_doctor`
        FOREIGN KEY (`doctorId`) REFERENCES `doctors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `doctor_unavailable_dates` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `doctorId` int(11) NOT NULL,
    `unavailableDate` date NOT NULL,
    `reason` varchar(255) DEFAULT NULL,
    `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_doctor_unavailable_date` (`doctorId`, `unavailableDate`),
    CONSTRAINT `fk_doctor_unavailable_doctor`
        FOREIGN KEY (`doctorId`) REFERENCES `doctors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `appointment_events` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `appointmentId` int(11) NOT NULL,
    `actorRole` varchar(20) NOT NULL,
    `actorId` int(11) DEFAULT NULL,
    `eventType` varchar(40) NOT NULL,
    `fromStatus` varchar(20) DEFAULT NULL,
    `toStatus` varchar(20) DEFAULT NULL,
    `oldDate` date DEFAULT NULL,
    `oldTime` time DEFAULT NULL,
    `newDate` date DEFAULT NULL,
    `newTime` time DEFAULT NULL,
    `notes` text DEFAULT NULL,
    `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_appointment_events_appointment` (`appointmentId`, `createdAt`),
    CONSTRAINT `fk_appointment_events_appointment`
        FOREIGN KEY (`appointmentId`) REFERENCES `appointment` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `appointment_reschedule_requests` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `appointmentId` int(11) NOT NULL,
    `requestedByUserId` int(11) NOT NULL,
    `requestedDate` date NOT NULL,
    `requestedTime` time NOT NULL,
    `status` varchar(20) NOT NULL DEFAULT 'pending',
    `requestNote` varchar(1000) DEFAULT NULL,
    `reviewedByRole` varchar(20) DEFAULT NULL,
    `reviewedById` int(11) DEFAULT NULL,
    `reviewNote` varchar(1000) DEFAULT NULL,
    `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
    `reviewedAt` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_reschedule_appointment_status` (`appointmentId`, `status`),
    KEY `idx_reschedule_patient` (`requestedByUserId`, `status`),
    CONSTRAINT `fk_reschedule_appointment`
        FOREIGN KEY (`appointmentId`) REFERENCES `appointment` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_reschedule_patient`
        FOREIGN KEY (`requestedByUserId`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Verification after applying: these counts should agree with the existing appointment table.
SELECT `status`, COUNT(*) AS `appointments` FROM `appointment` GROUP BY `status`;
SELECT COUNT(*) AS `availability_rules` FROM `doctor_availability`;
