<?php
/*
 * Digital Forms (D-Forms) registry and helpers.
 *
 * Each definition digitizes an official barangay paper form. Residents answer
 * the digital version (modules/forms/dform_fill.php); after staff approval the
 * answers are transferred onto the official printable layout
 * (modules/forms/dform_print.php using modules/forms/templates/*), keeping the
 * physical layout of the paper form so it can be printed, saved as PDF, and
 * signed.
 *
 * To add a future form: add one entry to getDFormDefinitions() and, if needed,
 * one printable template file. Everything else (listing, validation, storage,
 * approval, printing) picks it up automatically.
 *
 * Field definition keys:
 *   label    — shown to the resident
 *   type     — text | textarea | date | time | select | email | tel | number
 *   required — true/false
 *   options  — for select fields
 *   prefill  — resident profile source: full_name | address | contact_number |
 *              civil_status | birthdate | purok | age | resident_code | email
 *   help     — optional hint under the field
 */

require_once __DIR__ . "/auth_check.php";

function getDFormDefinitions() {
    return [
        "barangay_clearance" => [
            "name" => "Barangay Clearance",
            "category" => "Certificates",
            "template" => "personal_clearance.php",
            "fields" => [
                "full_name" => ["label" => "Full Name", "type" => "text", "required" => true, "prefill" => "full_name"],
                "age" => ["label" => "Age", "type" => "number", "required" => true, "prefill" => "age"],
                "civil_status" => ["label" => "Civil Status", "type" => "select", "required" => true, "prefill" => "civil_status", "options" => ["Single", "Married", "Widowed", "Annulled"]],
                "address" => ["label" => "Complete Address", "type" => "textarea", "required" => true, "prefill" => "address"],
                "purok" => ["label" => "Purok", "type" => "text", "required" => false, "prefill" => "purok"],
                "contact_number" => ["label" => "Contact Number", "type" => "tel", "required" => true, "prefill" => "contact_number"],
                "purpose" => ["label" => "Purpose of Clearance", "type" => "text", "required" => true, "help" => "Employment, scholarship, travel, loan, etc."],
            ],
        ],
        "certificate_residency" => [
            "name" => "Certificate of Residency",
            "category" => "Certificates",
            "template" => "certificate_residency.php",
            "fields" => [
                "full_name" => ["label" => "Full Name", "type" => "text", "required" => true, "prefill" => "full_name"],
                "age" => ["label" => "Age", "type" => "number", "required" => true, "prefill" => "age"],
                "civil_status" => ["label" => "Civil Status", "type" => "select", "required" => true, "prefill" => "civil_status", "options" => ["Single", "Married", "Widowed", "Annulled"]],
                "address" => ["label" => "Complete Address", "type" => "textarea", "required" => true, "prefill" => "address"],
                "years_of_residency" => ["label" => "Years of Residency in the Barangay", "type" => "number", "required" => true],
                "purpose" => ["label" => "Purpose of Certification", "type" => "text", "required" => true],
            ],
        ],
        "certificate_indigency" => [
            "name" => "Certificate of Indigency",
            "category" => "Certificates",
            "template" => "certificate_indigency.php",
            "fields" => [
                "full_name" => ["label" => "Full Name", "type" => "text", "required" => true, "prefill" => "full_name"],
                "age" => ["label" => "Age", "type" => "number", "required" => true, "prefill" => "age"],
                "address" => ["label" => "Complete Address", "type" => "textarea", "required" => true, "prefill" => "address"],
                "monthly_income" => ["label" => "Estimated Monthly Household Income (PHP)", "type" => "number", "required" => false],
                "purpose" => ["label" => "Purpose of Certification", "type" => "text", "required" => true, "help" => "Medical assistance, educational assistance, legal aid, etc."],
            ],
        ],
        "business_clearance" => [
            "name" => "Business Clearance",
            "category" => "Certificates",
            "template" => "barangay_clearance.php",
            "fields" => [
                "full_name" => ["label" => "Owner / Applicant Full Name", "type" => "text", "required" => true, "prefill" => "full_name"],
                "business_name" => ["label" => "Business Name / Style", "type" => "text", "required" => true],
                "business_nature" => ["label" => "Nature of Business", "type" => "text", "required" => true],
                "business_address" => ["label" => "Business Address", "type" => "textarea", "required" => true, "prefill" => "address"],
                "operating_since" => ["label" => "Operating in the Barangay Since (year)", "type" => "text", "required" => false, "help" => "Leave blank or write Not Applicable for a first application."],
                "contact_number" => ["label" => "Contact Number", "type" => "tel", "required" => true, "prefill" => "contact_number"],
            ],
        ],
        "medical_mission" => [
            "name" => "Medical Mission Registration",
            "category" => "Health",
            "template" => "medical_mission.php",
            "fields" => [
                "full_name" => ["label" => "Full Name", "type" => "text", "required" => true, "prefill" => "full_name"],
                "birthdate" => ["label" => "Birthdate", "type" => "date", "required" => true, "prefill" => "birthdate"],
                "age" => ["label" => "Age", "type" => "number", "required" => true, "prefill" => "age"],
                "gender" => ["label" => "Gender", "type" => "select", "required" => true, "options" => ["Male", "Female"]],
                "address" => ["label" => "Complete Address", "type" => "textarea", "required" => true, "prefill" => "address"],
                "contact_number" => ["label" => "Contact Number", "type" => "tel", "required" => true, "prefill" => "contact_number"],
                "services_needed" => ["label" => "Services Needed", "type" => "select", "required" => true, "options" => ["General Consultation", "Dental", "Optical", "Laboratory", "Vaccination", "Circumcision", "Others"]],
                "medical_conditions" => ["label" => "Existing Medical Conditions / Maintenance Medicines", "type" => "textarea", "required" => false],
            ],
        ],
        "health_services" => [
            "name" => "Health Services",
            "category" => "Health",
            "template" => "health_appointment.php",
            "fields" => [
                "full_name" => ["label" => "Full Name", "type" => "text", "required" => true, "prefill" => "full_name"],
                "resident_code" => ["label" => "Resident Code", "type" => "text", "required" => false, "prefill" => "resident_code"],
                "birthdate" => ["label" => "Birthdate", "type" => "date", "required" => true, "prefill" => "birthdate"],
                "age" => ["label" => "Age", "type" => "number", "required" => true, "prefill" => "age"],
                "gender" => ["label" => "Gender", "type" => "select", "required" => true, "options" => ["Male", "Female"]],
                "address" => ["label" => "Complete Address", "type" => "textarea", "required" => true, "prefill" => "address"],
                "contact_number" => ["label" => "Contact Number", "type" => "tel", "required" => true, "prefill" => "contact_number"],
                "philhealth_number" => ["label" => "PhilHealth Number", "type" => "text", "required" => false],
                "service_type" => ["label" => "Service Requested", "type" => "select", "required" => true, "options" => ["General Checkup", "Prenatal", "Postnatal", "Child Immunization", "Senior Checkup", "PWD Checkup", "Vaccination", "Medical Mission", "Other"]],
                "preferred_date" => ["label" => "Preferred Date", "type" => "date", "required" => true],
                "preferred_time" => ["label" => "Preferred Time", "type" => "time", "required" => true],
                "reason" => ["label" => "Reason for Visit / Symptoms", "type" => "textarea", "required" => true],
            ],
        ],
        "blotter_complaint" => [
            "name" => "Blotter Complaint Form",
            "category" => "Public Safety",
            "template" => "blotter.php",
            "fields" => [
                "full_name" => ["label" => "Complainant Full Name", "type" => "text", "required" => true, "prefill" => "full_name"],
                "age" => ["label" => "Complainant Age", "type" => "number", "required" => true, "prefill" => "age"],
                "address" => ["label" => "Complainant Address", "type" => "textarea", "required" => true, "prefill" => "address"],
                "contact_number" => ["label" => "Complainant Contact Number", "type" => "tel", "required" => true, "prefill" => "contact_number"],
                "respondent_name" => ["label" => "Respondent Name", "type" => "text", "required" => true],
                "respondent_address" => ["label" => "Respondent Address", "type" => "text", "required" => false],
                "complaint" => ["label" => "Complaint", "type" => "text", "required" => true],
                "incident_date" => ["label" => "Date of Incident", "type" => "date", "required" => true],
                "incident_time" => ["label" => "Time of Incident", "type" => "time", "required" => true],
                "incident_place" => ["label" => "Place of Incident", "type" => "text", "required" => true],
                "narrative" => ["label" => "Narrative of the Incident (Salaysay)", "type" => "textarea", "required" => true],
            ],
        ],
        "appointment_request" => [
            "name" => "Appointment Request",
            "category" => "Services",
            "template" => "health_appointment.php",
            "fields" => [
                "full_name" => ["label" => "Full Name", "type" => "text", "required" => true, "prefill" => "full_name"],
                "resident_code" => ["label" => "Resident Code", "type" => "text", "required" => false, "prefill" => "resident_code"],
                "address" => ["label" => "Complete Address", "type" => "textarea", "required" => true, "prefill" => "address"],
                "contact_number" => ["label" => "Contact Number", "type" => "tel", "required" => true, "prefill" => "contact_number"],
                "service_type" => ["label" => "Service / Office to Visit", "type" => "select", "required" => true, "options" => ["General Checkup", "Vaccination", "Barangay Captain's Office", "Secretary's Office", "Treasurer's Office", "Other"]],
                "preferred_date" => ["label" => "Preferred Date", "type" => "date", "required" => true],
                "preferred_time" => ["label" => "Preferred Time", "type" => "time", "required" => true],
                "reason" => ["label" => "Reason / Concern", "type" => "textarea", "required" => true],
            ],
        ],
        "relief_assistance" => [
            "name" => "Relief Assistance Form",
            "category" => "Social Services",
            "template" => "relief_assistance.php",
            "fields" => [
                "full_name" => ["label" => "Head of Family / Applicant Full Name", "type" => "text", "required" => true, "prefill" => "full_name"],
                "age" => ["label" => "Age", "type" => "number", "required" => true, "prefill" => "age"],
                "address" => ["label" => "Complete Address", "type" => "textarea", "required" => true, "prefill" => "address"],
                "purok" => ["label" => "Purok", "type" => "text", "required" => false, "prefill" => "purok"],
                "contact_number" => ["label" => "Contact Number", "type" => "tel", "required" => true, "prefill" => "contact_number"],
                "household_members" => ["label" => "Number of Household Members", "type" => "number", "required" => true],
                "assistance_type" => ["label" => "Type of Assistance Requested", "type" => "select", "required" => true, "options" => ["Food Pack", "Financial Assistance", "Medical Assistance", "Shelter Materials", "Evacuation Support", "Others"]],
                "calamity" => ["label" => "Calamity / Reason for Assistance", "type" => "text", "required" => true, "help" => "Typhoon, flood, fire, loss of livelihood, etc."],
                "details" => ["label" => "Additional Details", "type" => "textarea", "required" => false],
            ],
        ],
    ];
}

// Creates the D-Forms tables when the SQL migration has not been run, and
// keeps the `forms` registry rows in sync with the definitions above.
function ensureDFormTables($conn) {
    static $done = false;
    if ($done || !$conn) {
        return;
    }
    $done = true;

    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS forms (
        id INT AUTO_INCREMENT PRIMARY KEY,
        form_key VARCHAR(50) NOT NULL UNIQUE,
        name VARCHAR(150) NOT NULL,
        category VARCHAR(80) NOT NULL DEFAULT 'General',
        template VARCHAR(150) NOT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'Active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS form_submissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        form_id INT NOT NULL,
        resident_id INT NOT NULL,
        submission_data TEXT NOT NULL,
        workflow_status VARCHAR(30) NOT NULL DEFAULT 'Pending',
        remarks VARCHAR(255) DEFAULT '',
        reviewed_by INT DEFAULT NULL,
        reviewed_at DATETIME DEFAULT NULL,
        submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $stmt = mysqli_prepare($conn, "INSERT INTO forms (form_key, name, category, template)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE name = VALUES(name), category = VALUES(category), template = VALUES(template)");
    foreach (getDFormDefinitions() as $form_key => $definition) {
        mysqli_stmt_bind_param($stmt, "ssss", $form_key, $definition["name"], $definition["category"], $definition["template"]);
        mysqli_stmt_execute($stmt);
    }
}

// `forms` row (id, status, ...) for a registry key, or null when unknown
// or deactivated by staff.
function getDFormRecord($conn, $form_key) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM forms WHERE form_key = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $form_key);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return $result ? mysqli_fetch_assoc($result) : null;
}

// Values pulled from the resident profile to pre-fill digital form fields.
function dformPrefillValue($resident, $source) {
    if (!$resident) {
        return "";
    }
    switch ($source) {
        case "full_name":
            return trim($resident["first_name"] . " " . trim($resident["middle_name"] . " ") . $resident["last_name"]);
        case "age":
            if (empty($resident["birthdate"])) {
                return "";
            }
            $birth = date_create($resident["birthdate"]);
            return $birth ? (string)date_diff($birth, date_create("today"))->y : "";
        default:
            return (string)($resident[$source] ?? "");
    }
}

// Validates POST answers against a form definition.
// Returns [clean_values, errors].
function validateDFormInput($definition, $post) {
    $values = [];
    $errors = [];

    foreach ($definition["fields"] as $field_key => $field) {
        $raw = trim((string)($post[$field_key] ?? ""));

        if ($raw === "") {
            if (!empty($field["required"])) {
                $errors[] = $field["label"] . " is required.";
            }
            $values[$field_key] = "";
            continue;
        }

        $max = $field["type"] === "textarea" ? 3000 : 255;
        if (strlen($raw) > $max) {
            $errors[] = $field["label"] . " is too long.";
            continue;
        }

        switch ($field["type"]) {
            case "select":
                if (!in_array($raw, $field["options"] ?? [], true)) {
                    $errors[] = "Please choose a valid option for " . $field["label"] . ".";
                    $raw = "";
                }
                break;
            case "date":
                $parsed = date_create_from_format("Y-m-d", $raw);
                if (!$parsed || $parsed->format("Y-m-d") !== $raw) {
                    $errors[] = $field["label"] . " must be a valid date.";
                    $raw = "";
                }
                break;
            case "time":
                if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $raw)) {
                    $errors[] = $field["label"] . " must be a valid time.";
                    $raw = "";
                }
                break;
            case "number":
                if (!is_numeric($raw) || (float)$raw < 0) {
                    $errors[] = $field["label"] . " must be a valid number.";
                    $raw = "";
                }
                break;
            case "email":
                if (!filter_var($raw, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = $field["label"] . " must be a valid email address.";
                    $raw = "";
                }
                break;
            case "tel":
                if (!preg_match('/^[0-9+\-\s()]{7,20}$/', $raw)) {
                    $errors[] = $field["label"] . " must be a valid contact number.";
                    $raw = "";
                }
                break;
        }

        $values[$field_key] = $raw;
    }

    return [$values, $errors];
}

/*
 * Template helpers. The printable templates in modules/forms/templates/ are
 * dual-mode: with an empty $dform they render as blank paper forms
 * (modules/forms/print.php); with submission data they render the auto-filled
 * official document (modules/forms/dform_print.php).
 */

// A write-on line, filled with the submitted value when available.
function dfLine($key, $class = "") {
    global $dform;
    $value = trim((string)(($dform ?? [])[$key] ?? ""));
    $inner = $value === "" ? "" : "&nbsp;<strong>" . e($value) . "</strong>&nbsp;";
    return '<span class="line ' . e($class) . '">' . $inner . '</span>';
}

// A checkbox, marked when the submitted value matches.
function dfCheck($key, $match) {
    global $dform;
    $value = trim((string)(($dform ?? [])[$key] ?? ""));
    $mark = ($value !== "" && strcasecmp($value, $match) === 0)
        ? '<span style="font-size:11px; line-height:12px; display:block; text-align:center;">&#10005;</span>'
        : '';
    return '<span class="checkbox">' . $mark . '</span>';
}

// Multi-line writing area: shows the text when filled, ruled lines when blank.
function dfWriting($key, $blank_lines = 4) {
    global $dform;
    $value = trim((string)(($dform ?? [])[$key] ?? ""));
    if ($value !== "") {
        return '<div style="border-bottom:1px solid #111; padding:2px 4px 6px; line-height:1.9; min-height:' . (18 * $blank_lines) . 'px;">' . nl2br(e($value)) . '</div>';
    }
    return '<div class="writing-lines">' . str_repeat('<span class="line full"></span>', $blank_lines) . '</div>';
}
?>
