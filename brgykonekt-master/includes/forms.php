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
        // Certificates are issued by staff (Secretary and above).
        "certificate_indigency" => ["Certificate of Indigency", "certificate_indigency.php", 3, "Certificates"],
        "barangay_clearance" => ["Barangay Clearance", "barangay_clearance.php", 3, "Certificates"],
    ];
}

function printableFormTemplate($template) {
    return dirname(__DIR__) . "/modules/forms/templates/" . $template;
}

// Barangay identity block used on every printed form/certificate header.
function getBarangayIdentity($conn) {
    return [
        "name" => getSystemSetting($conn, "barangay_name", "Barangay Name"),
        "address" => getSystemSetting($conn, "barangay_address", "Municipality / City, Province"),
        "logo" => getSystemSetting($conn, "barangay_logo", "")
    ];
}
