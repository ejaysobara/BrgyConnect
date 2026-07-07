<?php
include "config/database.php";

function runQuery($conn, $sql, $label = "") {
    if (!mysqli_query($conn, $sql)) {
        echo ($label !== "" ? "$label: " : "") . mysqli_error($conn) . "<br>";
        return false;
    }

    return true;
}

function ensureColumn($conn, $table, $column, $definition) {
    $table = mysqli_real_escape_string($conn, $table);
    $column = mysqli_real_escape_string($conn, $column);
    $result = mysqli_query($conn, "SELECT COUNT(*) AS total
                                   FROM INFORMATION_SCHEMA.COLUMNS
                                   WHERE TABLE_SCHEMA = DATABASE()
                                   AND TABLE_NAME = '$table'
                                   AND COLUMN_NAME = '$column'");
    $row = $result ? mysqli_fetch_assoc($result) : ["total" => 0];

    if ((int)$row["total"] === 0) {
        runQuery($conn, "ALTER TABLE `$table` ADD COLUMN $definition", "Add $table.$column");
    }
}

runQuery($conn, "CREATE TABLE IF NOT EXISTS roles (
    id INT PRIMARY KEY,
    role_name VARCHAR(100) NOT NULL
)");

runQuery($conn, "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    username VARCHAR(80) NOT NULL UNIQUE,
    email VARCHAR(150) DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    status VARCHAR(30) DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

runQuery($conn, "CREATE TABLE IF NOT EXISTS system_settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value TEXT
)");

runQuery($conn, "CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT 0,
    action VARCHAR(100),
    target VARCHAR(255),
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

runQuery($conn, "CREATE TABLE IF NOT EXISTS residents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resident_code VARCHAR(50) DEFAULT NULL,
    user_id INT NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100) DEFAULT '',
    last_name VARCHAR(100) NOT NULL,
    birthdate DATE DEFAULT NULL,
    gender VARCHAR(30) DEFAULT '',
    civil_status VARCHAR(30) DEFAULT '',
    address TEXT,
    purok VARCHAR(100) DEFAULT '',
    contact_number VARCHAR(50) DEFAULT '',
    occupation VARCHAR(150) DEFAULT '',
    family_information TEXT,
    emergency_contact VARCHAR(150) DEFAULT '',
    valid_id_path VARCHAR(255) DEFAULT '',
    proof_of_residency_path VARCHAR(255) DEFAULT '',
    status VARCHAR(50) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

runQuery($conn, "CREATE TABLE IF NOT EXISTS document_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    document_name VARCHAR(150) NOT NULL,
    fee DECIMAL(10,2) DEFAULT 0.00
)");

runQuery($conn, "CREATE TABLE IF NOT EXISTS document_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resident_id INT NOT NULL,
    document_type_id INT NOT NULL,
    purpose VARCHAR(255) NOT NULL,
    status VARCHAR(50) DEFAULT 'Pending',
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

runQuery($conn, "CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    document_request_id INT NOT NULL,
    receipt_number VARCHAR(80) NOT NULL,
    amount DECIMAL(10,2) DEFAULT 0.00,
    payment_method VARCHAR(50) DEFAULT 'Cash',
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

runQuery($conn, "CREATE TABLE IF NOT EXISTS health_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_name VARCHAR(150) NOT NULL
)");

runQuery($conn, "CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resident_id INT NOT NULL,
    health_service_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    queue_number INT DEFAULT 0,
    status VARCHAR(50) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

runQuery($conn, "CREATE TABLE IF NOT EXISTS blotter_cases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resident_id INT NOT NULL,
    respondent_name VARCHAR(150) NOT NULL,
    incident_date DATETIME NOT NULL,
    incident_location VARCHAR(255) NOT NULL,
    complaint_details TEXT,
    evidence_path VARCHAR(255) DEFAULT '',
    assigned_to INT DEFAULT NULL,
    mediation_schedule DATETIME DEFAULT NULL,
    resolution TEXT,
    status VARCHAR(50) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

runQuery($conn, "CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    category VARCHAR(80) NOT NULL,
    content TEXT NOT NULL,
    posted_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

runQuery($conn, "CREATE TABLE IF NOT EXISTS activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    description TEXT,
    activity_date DATE NOT NULL,
    venue VARCHAR(180) NOT NULL,
    organizer VARCHAR(150) NOT NULL,
    participants TEXT,
    budget DECIMAL(10,2) DEFAULT 0.00,
    status VARCHAR(50) DEFAULT 'Upcoming',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

runQuery($conn, "CREATE TABLE IF NOT EXISTS inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asset_name VARCHAR(180) NOT NULL,
    category VARCHAR(100) DEFAULT '',
    total_quantity INT DEFAULT 0,
    available_quantity INT DEFAULT 0,
    condition_status VARCHAR(50) DEFAULT 'Functional',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

runQuery($conn, "CREATE TABLE IF NOT EXISTS borrowed_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inventory_id INT NOT NULL,
    resident_id INT NOT NULL,
    quantity INT DEFAULT 1,
    date_borrowed DATE NOT NULL,
    return_date DATE NOT NULL,
    status VARCHAR(50) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

runQuery($conn, "CREATE TABLE IF NOT EXISTS pets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resident_id INT NOT NULL,
    pet_name VARCHAR(120) NOT NULL,
    species VARCHAR(80) NOT NULL,
    breed VARCHAR(120) DEFAULT '',
    age INT DEFAULT NULL,
    color VARCHAR(80) DEFAULT '',
    vaccination_status VARCHAR(50) DEFAULT 'Not Vaccinated',
    last_vaccination_date DATE DEFAULT NULL,
    next_booster_date DATE DEFAULT NULL,
    veterinarian VARCHAR(150) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

runQuery($conn, "CREATE TABLE IF NOT EXISTS vaccinations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pet_id INT NOT NULL,
    vaccine_name VARCHAR(150) NOT NULL,
    vaccination_date DATE NOT NULL,
    next_due_date DATE NOT NULL,
    veterinarian VARCHAR(150) DEFAULT '',
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

ensureColumn($conn, "residents", "resident_code", "resident_code VARCHAR(50) DEFAULT NULL");
ensureColumn($conn, "residents", "family_information", "family_information TEXT");
ensureColumn($conn, "residents", "valid_id_path", "valid_id_path VARCHAR(255) DEFAULT ''");
ensureColumn($conn, "residents", "proof_of_residency_path", "proof_of_residency_path VARCHAR(255) DEFAULT ''");
ensureColumn($conn, "blotter_cases", "evidence_path", "evidence_path VARCHAR(255) DEFAULT ''");
ensureColumn($conn, "blotter_cases", "assigned_to", "assigned_to INT DEFAULT NULL");
ensureColumn($conn, "blotter_cases", "mediation_schedule", "mediation_schedule DATETIME DEFAULT NULL");
ensureColumn($conn, "blotter_cases", "resolution", "resolution TEXT");
ensureColumn($conn, "activities", "participants", "participants TEXT");

$roles = [
    [1, "Senior Administrator"],
    [2, "Barangay Captain"],
    [3, "Barangay Secretary"],
    [4, "Barangay Treasurer"],
    [5, "Barangay Health Worker"],
    [6, "Lupon / Barangay Tanod"],
    [7, "SK Chairman"],
    [8, "Resident / Constituent"]
];

foreach ($roles as $role) {
    $role_id = $role[0];
    $role_name = mysqli_real_escape_string($conn, $role[1]);
    runQuery($conn, "INSERT INTO roles (id, role_name)
                     VALUES ('$role_id', '$role_name')
                     ON DUPLICATE KEY UPDATE role_name = VALUES(role_name)");
}

$default_password = password_hash("Barangay123", PASSWORD_DEFAULT);
$users = [
    [1, "Super Administrator", "superadmin", "superadmin@barangayconnect.test"],
    [2, "Barangay Captain", "captain", "captain@barangayconnect.test"],
    [3, "Barangay Secretary", "secretary", "secretary@barangayconnect.test"],
    [4, "Barangay Treasurer", "treasurer", "treasurer@barangayconnect.test"],
    [5, "Barangay Health Worker", "bhw", "bhw@barangayconnect.test"],
    [6, "Lupon / Barangay Tanod", "tanod", "tanod@barangayconnect.test"],
    [7, "SK Chairman", "skchairman", "sk@barangayconnect.test"],
    [8, "Resident / Constituent", "resident", "resident@barangayconnect.test"]
];

foreach ($users as $user) {
    $role_id = $user[0];
    $full_name = mysqli_real_escape_string($conn, $user[1]);
    $username = mysqli_real_escape_string($conn, $user[2]);
    $email = mysqli_real_escape_string($conn, $user[3]);

    runQuery($conn, "INSERT INTO users (role_id, full_name, username, email, password, status)
                     VALUES ('$role_id', '$full_name', '$username', '$email', '$default_password', 'Active')
                     ON DUPLICATE KEY UPDATE
                     role_id = VALUES(role_id),
                     full_name = VALUES(full_name),
                     email = VALUES(email),
                     password = VALUES(password),
                     status = VALUES(status)");
    echo "$username ready.<br>";
}

$settings = [
    ["barangay_name", "BrgyKonekt"],
    ["contact_email", "barangay@example.test"],
    ["allow_registration", "1"]
];

foreach ($settings as $setting) {
    $key = mysqli_real_escape_string($conn, $setting[0]);
    $value = mysqli_real_escape_string($conn, $setting[1]);
    runQuery($conn, "INSERT INTO system_settings (setting_key, setting_value)
                     VALUES ('$key', '$value')
                     ON DUPLICATE KEY UPDATE setting_value = setting_value");
}

$document_types = [
    ["Barangay Clearance", 50],
    ["Certificate of Residency", 40],
    ["Certificate of Indigency", 0],
    ["Business Clearance", 150],
    ["Good Moral", 50],
    ["Certification", 50]
];

foreach ($document_types as $document) {
    $name = mysqli_real_escape_string($conn, $document[0]);
    $fee = (float)$document[1];
    runQuery($conn, "INSERT INTO document_types (document_name, fee)
                     SELECT '$name', '$fee'
                     WHERE NOT EXISTS (SELECT 1 FROM document_types WHERE document_name = '$name')");
}

$health_services = [
    "General Checkup",
    "Prenatal",
    "Postnatal",
    "Child Immunization",
    "Senior Checkup",
    "PWD Checkup",
    "Medical Mission",
    "Vaccination"
];

foreach ($health_services as $service) {
    $name = mysqli_real_escape_string($conn, $service);
    runQuery($conn, "INSERT INTO health_services (service_name)
                     SELECT '$name'
                     WHERE NOT EXISTS (SELECT 1 FROM health_services WHERE service_name = '$name')");
}

$inventory_items = [
    ["Tent", "Equipment", 5, "Functional"],
    ["Plastic Chair", "Furniture", 100, "Functional"],
    ["Sound System", "Equipment", 2, "Functional"]
];

foreach ($inventory_items as $item) {
    $asset = mysqli_real_escape_string($conn, $item[0]);
    $category = mysqli_real_escape_string($conn, $item[1]);
    $quantity = (int)$item[2];
    $condition = mysqli_real_escape_string($conn, $item[3]);
    runQuery($conn, "INSERT INTO inventory (asset_name, category, total_quantity, available_quantity, condition_status)
                     SELECT '$asset', '$category', '$quantity', '$quantity', '$condition'
                     WHERE NOT EXISTS (SELECT 1 FROM inventory WHERE asset_name = '$asset')");
}

echo "<br>Setup complete. Default password for seeded accounts: Barangay123";
?>
