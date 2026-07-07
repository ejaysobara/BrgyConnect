-- Migration: Bio / Health Information + printable forms support
-- Run this against the BrgyKonekt database.

-- Private health information, one row per resident.
-- Visible only to the resident themselves and authorized health staff
-- (enforced in application code: requireHealthAccess / canViewHealthRecords).
CREATE TABLE IF NOT EXISTS resident_health_info (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resident_id INT NOT NULL UNIQUE,
    blood_type VARCHAR(10) DEFAULT 'Unknown',
    allergies TEXT,
    medical_conditions TEXT,
    medications TEXT,
    disabilities TEXT,
    philhealth_number VARCHAR(30) DEFAULT '',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE CASCADE
);

-- Appointments now support non-health service categories and a free-text
-- "other service" field. (Skip these if seed_users.php already added them.)
ALTER TABLE appointments ADD COLUMN service_category VARCHAR(50) DEFAULT 'health';
ALTER TABLE appointments ADD COLUMN other_service VARCHAR(180) DEFAULT '';

-- Resident profile photo shown on My Profile and the staff resident preview.
ALTER TABLE residents ADD COLUMN photo_path VARCHAR(255) DEFAULT '';

-- Optional future home for the printable-forms registry (currently the
-- skeleton uses a hardcoded list in includes/forms.php). Uncomment when the
-- barangay uploads its official forms and we move to database-managed forms.
-- CREATE TABLE IF NOT EXISTS printable_forms (
--     id INT AUTO_INCREMENT PRIMARY KEY,
--     form_key VARCHAR(50) NOT NULL UNIQUE,
--     label VARCHAR(150) NOT NULL,
--     file_path VARCHAR(255) NOT NULL,
--     min_staff_level TINYINT DEFAULT 0,
--     is_active TINYINT(1) DEFAULT 1,
--     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
-- );
