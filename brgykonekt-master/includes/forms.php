<?php
/*
 * Registry of printable blank forms and certificate templates.
 * Each entry renders a generic, print-ready template from
 * modules/forms/templates/ carrying the barangay identity configured in
 * System Settings (name, address, logo). No sample-specific names/places.
 *
 *   "key" => [label, template file, minimum staff level (0 = residents), group]
 */

function getPrintableForms() {
    return [
        "pet_registration" => ["Pet Registration Form", "pet_registration.php", 0, "Forms"],
        "complaint" => ["Complaint Form", "complaint.php", 0, "Forms"],
        "blotter" => ["Blotter Form", "blotter.php", 0, "Forms"],
        "business_permit" => ["Application for Business Permit", "business_permit.php", 0, "Forms"],
        "special_permit" => ["Request for Special Permit", "special_permit.php", 0, "Forms"],
        "health_appointment" => ["Health Center Appointment Form", "health_appointment.php", 0, "Forms"],
        "medical_mission" => ["Medical Mission Registration Form", "medical_mission.php", 0, "Forms"],
        "relief_assistance" => ["Relief Assistance Form", "relief_assistance.php", 0, "Forms"],
        // Certificates are issued by staff (Secretary and above).
        "certificate_indigency" => ["Certificate of Indigency", "certificate_indigency.php", 3, "Certificates"],
        "certificate_residency" => ["Certificate of Residency", "certificate_residency.php", 3, "Certificates"],
        "barangay_clearance" => ["Barangay Clearance (Business)", "barangay_clearance.php", 3, "Certificates"],
        "personal_clearance" => ["Barangay Clearance (Personal)", "personal_clearance.php", 3, "Certificates"],
    ];
}

function printableFormTemplate($template) {
    return dirname(__DIR__) . "/modules/forms/templates/" . $template;
}

/*
 * Names of the signing officials, taken from the active official accounts
 * (Manage Officials): Captain (role 2, falling back to Chairman 7),
 * Secretary (role 3), Treasurer (role 4). Printed above the signature lines
 * on every form/certificate.
 */
function getBarangayOfficials($conn) {
    $officials = ["captain" => "", "secretary" => "", "treasurer" => ""];
    if (!$conn) {
        return $officials;
    }

    $map = ["captain" => [2, 7], "secretary" => [3], "treasurer" => [4]];
    foreach ($map as $key => $role_ids) {
        foreach ($role_ids as $role_id) {
            $stmt = mysqli_prepare($conn, "SELECT full_name FROM users WHERE role_id = ? AND status = 'Active' ORDER BY id ASC LIMIT 1");
            mysqli_stmt_bind_param($stmt, "i", $role_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if ($result && ($row = mysqli_fetch_assoc($result))) {
                $officials[$key] = $row["full_name"];
                break;
            }
        }
    }
    return $officials;
}

// Signature line pre-printed with the configured official's name (if any).
// $key: captain | secretary | treasurer. Reads $brgy_officials set by the
// print pages so templates stay simple.
function officialLine($key) {
    global $brgy_officials;
    $name = trim((string)(($brgy_officials ?? [])[$key] ?? ""));
    $inner = $name === "" ? "" : "<strong>" . e(strtoupper($name)) . "</strong>";
    return '<span class="line">' . $inner . '</span>';
}

// Barangay identity block used on every printed form/certificate header.
function getBarangayIdentity($conn) {
    return [
        "name" => getSystemSetting($conn, "barangay_name", "Barangay Name"),
        "address" => getSystemSetting($conn, "barangay_address", "Municipality / City, Province"),
        "logo" => getSystemSetting($conn, "barangay_logo", "")
    ];
}
