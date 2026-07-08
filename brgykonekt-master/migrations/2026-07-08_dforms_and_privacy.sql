-- Migration: Digital Forms (D-Forms) + Data Privacy Act (RA 10173) consent
-- Run this against the BrgyKonekt database.
-- The application also creates these automatically on first use
-- (includes/dforms.php and includes/privacy.php), so running this file is
-- optional but recommended for fresh deployments.

-- ---------------------------------------------------------------
-- FEATURE 1: Digital Forms (D-Forms)
-- ---------------------------------------------------------------

-- Registry of digital forms. `form_key` matches the definition key in
-- includes/dforms.php; `template` is the printable layout file in
-- modules/forms/templates/ used to auto-fill the official barangay form.
CREATE TABLE IF NOT EXISTS forms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    form_key VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    category VARCHAR(80) NOT NULL DEFAULT 'General',
    template VARCHAR(150) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- One row per resident submission. `submission_data` stores the validated
-- answers as JSON; the printable form is generated from it on demand.
CREATE TABLE IF NOT EXISTS form_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    form_id INT NOT NULL,
    resident_id INT NOT NULL,
    submission_data TEXT NOT NULL,
    workflow_status VARCHAR(30) NOT NULL DEFAULT 'Pending',
    remarks VARCHAR(255) DEFAULT '',
    reviewed_by INT DEFAULT NULL,
    reviewed_at DATETIME DEFAULT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (form_id) REFERENCES forms(id) ON DELETE CASCADE,
    FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE CASCADE
);

-- ---------------------------------------------------------------
-- FEATURE 2: Data Privacy Act of 2012 (RA 10173) consent
-- ---------------------------------------------------------------

-- Consent flag, timestamp, and version of the notice accepted.
-- (Skip these if the application already added them automatically.)
ALTER TABLE residents ADD COLUMN privacy_consent TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE residents ADD COLUMN privacy_consent_date DATETIME DEFAULT NULL;
ALTER TABLE residents ADD COLUMN privacy_version VARCHAR(20) DEFAULT '';
