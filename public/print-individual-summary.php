<?php
include_once __DIR__ . "/../includes/session.php";
include_once __DIR__ . "/../config/db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "patient") {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$patient = array(
    "id" => 0,
    "health_id" => "Not provided",
    "nhis_number" => "Not provided",
    "full_name" => $_SESSION["name_tag"],
    "phone" => "Not provided",
    "gender" => "Not provided",
    "date_of_birth" => "Not provided",
    "blood_group" => "Not provided",
    "genotype" => "Not provided",
    "address" => "Not provided",
    "allergies" => "Not provided",
    "chronic_conditions" => "Not provided",
    "current_medications" => "Not provided",
    "emergency_contact_name" => "Not provided",
    "emergency_contact_phone" => "Not provided",
);
$records = array();
$medications = array();

$patient_stmt = $conn->prepare("SELECT id, health_id, nhis_number, full_name, phone, gender, date_of_birth, blood_group, genotype, address, allergies, chronic_conditions, current_medications, emergency_contact_name, emergency_contact_phone FROM patients WHERE user_id = ? LIMIT 1");
$patient_stmt->bind_param("i", $user_id);
$patient_stmt->execute();
$patient_stmt->bind_result($patient_id, $health_id, $nhis_number, $full_name, $phone, $gender, $date_of_birth, $blood_group, $genotype, $address, $allergies, $chronic_conditions, $current_medications, $emergency_contact_name, $emergency_contact_phone);

if ($patient_stmt->fetch()) {
    $patient["id"] = $patient_id;

    if (!empty($health_id)) {
        $patient["health_id"] = $health_id;
    }

    if (!empty($nhis_number)) {
        $patient["nhis_number"] = $nhis_number;
    }

    if (!empty($full_name)) {
        $patient["full_name"] = $full_name;
    }

    if (!empty($phone)) {
        $patient["phone"] = $phone;
    }

    if (!empty($gender)) {
        $patient["gender"] = ucfirst($gender);
    }

    if (!empty($date_of_birth)) {
        $patient["date_of_birth"] = $date_of_birth;
    }

    if (!empty($blood_group)) {
        $patient["blood_group"] = $blood_group;
    }

    if (!empty($genotype)) {
        $patient["genotype"] = $genotype;
    }

    if (!empty($address)) {
        $patient["address"] = $address;
    }

    if (!empty($allergies)) {
        $patient["allergies"] = $allergies;
    }

    if (!empty($chronic_conditions)) {
        $patient["chronic_conditions"] = $chronic_conditions;
    }

    if (!empty($current_medications)) {
        $patient["current_medications"] = $current_medications;
    }

    if (!empty($emergency_contact_name)) {
        $patient["emergency_contact_name"] = $emergency_contact_name;
    }

    if (!empty($emergency_contact_phone)) {
        $patient["emergency_contact_phone"] = $emergency_contact_phone;
    }
}

$patient_stmt->close();

if ($patient["id"] > 0) {
    $record_stmt = $conn->prepare("SELECT medical_records.visit_date, medical_records.visit_type, medical_records.heart_rate, medical_records.blood_pressure, medical_records.temperature, medical_records.weight, medical_records.symptoms, medical_records.possible_illness, medical_records.result_status, medical_records.result_summary, medical_records.doctor_name, medical_records.diagnosis, medical_records.treatment_notes, medical_records.prescription_notes, medical_records.medication_status, hospitals.hospital_name FROM medical_records LEFT JOIN hospitals ON medical_records.hospital_id = hospitals.id WHERE medical_records.patient_id = ? ORDER BY medical_records.visit_date DESC, medical_records.created_at DESC");
    $record_stmt->bind_param("i", $patient["id"]);
    $record_stmt->execute();
    $record_stmt->bind_result($visit_date, $visit_type, $heart_rate, $blood_pressure, $temperature, $weight, $symptoms, $possible_illness, $result_status, $result_summary, $doctor_name, $diagnosis, $treatment_notes, $prescription_notes, $medication_status, $hospital_name);

    while ($record_stmt->fetch()) {
        $records[] = array(
            "visit_date" => $visit_date,
            "visit_type" => $visit_type,
            "heart_rate" => $heart_rate,
            "blood_pressure" => $blood_pressure,
            "temperature" => $temperature,
            "weight" => $weight,
            "symptoms" => $symptoms,
            "possible_illness" => $possible_illness,
            "result_status" => $result_status,
            "result_summary" => $result_summary,
            "doctor_name" => $doctor_name,
            "diagnosis" => $diagnosis,
            "treatment_notes" => $treatment_notes,
            "prescription_notes" => $prescription_notes,
            "medication_status" => $medication_status,
            "hospital_name" => $hospital_name,
        );
    }

    $record_stmt->close();

    $medication_stmt = $conn->prepare("SELECT medication_name, dosage, frequency, start_date, end_date, status FROM medications WHERE patient_id = ? ORDER BY status ASC, created_at DESC");
    $medication_stmt->bind_param("i", $patient["id"]);
    $medication_stmt->execute();
    $medication_stmt->bind_result($medication_name, $dosage, $frequency, $start_date, $end_date, $status);

    while ($medication_stmt->fetch()) {
        $medications[] = array(
            "medication_name" => $medication_name,
            "dosage" => $dosage,
            "frequency" => $frequency,
            "start_date" => $start_date,
            "end_date" => $end_date,
            "status" => $status,
        );
    }

    $medication_stmt->close();
}

function print_value($value)
{
    if (!empty($value)) {
        return htmlspecialchars($value);
    }

    return "Not provided";
}

function print_status($value)
{
    if ($value == "awaiting_result") {
        return "Awaiting result";
    }

    if ($value == "result_ready") {
        return "Result ready";
    }

    if ($value == "in_progress") {
        return "In progress";
    }

    if ($value == "completed") {
        return "Completed";
    }

    if ($value == "stopped") {
        return "Stopped";
    }

    if ($value == "active") {
        return "Active";
    }

    if ($value == "not_started") {
        return "Not started";
    }

    return print_value($value);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Medical Summary | Medic</title>
    <link rel="stylesheet" href="assets/css/print-summary.css">
</head>
<body>
    <main class="print-page">
        <div class="print-actions">
            <a href="individual-medical-records.php">Back to records</a>
            <button type="button" onclick="window.print()">Print / Save PDF</button>
        </div>

        <section class="print-sheet">
            <header class="print-header">
                <div class="print-brand">
                    <h1>Medic</h1>
                    <p>Individual medical summary</p>
                </div>
                <div class="print-meta">
                    <p>Generated: <?php echo htmlspecialchars(date("M d, Y h:i A")); ?></p>
                    <p>NHIS: <?php echo print_value($patient["nhis_number"]); ?></p>
                </div>
            </header>

            <div class="print-title">
                <span>Individual summary</span>
                <h2><?php echo print_value($patient["full_name"]); ?></h2>
            </div>

            <section class="print-grid">
                <div class="print-box"><span>Health ID</span><strong><?php echo print_value($patient["health_id"]); ?></strong></div>
                <div class="print-box"><span>Phone</span><strong><?php echo print_value($patient["phone"]); ?></strong></div>
                <div class="print-box"><span>Gender</span><strong><?php echo print_value($patient["gender"]); ?></strong></div>
                <div class="print-box"><span>Date of birth</span><strong><?php echo print_value($patient["date_of_birth"]); ?></strong></div>
                <div class="print-box"><span>Blood group</span><strong><?php echo print_value($patient["blood_group"]); ?></strong></div>
                <div class="print-box"><span>Genotype</span><strong><?php echo print_value($patient["genotype"]); ?></strong></div>
                <div class="print-box"><span>Allergies</span><strong><?php echo print_value($patient["allergies"]); ?></strong></div>
                <div class="print-box"><span>Chronic conditions</span><strong><?php echo print_value($patient["chronic_conditions"]); ?></strong></div>
                <div class="print-box"><span>Current medications</span><strong><?php echo print_value($patient["current_medications"]); ?></strong></div>
                <div class="print-box"><span>Emergency contact</span><strong><?php echo print_value($patient["emergency_contact_name"] . " " . $patient["emergency_contact_phone"]); ?></strong></div>
                <div class="print-box"><span>Address</span><strong><?php echo print_value($patient["address"]); ?></strong></div>
            </section>

            <section class="print-section">
                <h3>Medical Records</h3>
                <?php if (empty($records)) { ?>
                    <p>No medical records available.</p>
                <?php } else { ?>
                    <?php foreach ($records as $record) { ?>
                        <article class="print-record">
                            <div class="print-record-header">
                                <strong><?php echo print_value($record["hospital_name"]); ?></strong>
                                <span><?php echo print_value($record["visit_date"]); ?></span>
                            </div>
                            <div class="print-grid">
                                <div class="print-box"><span>Visit type</span><strong><?php echo print_value($record["visit_type"]); ?></strong></div>
                                <div class="print-box"><span>Possible illness</span><strong><?php echo print_value($record["possible_illness"]); ?></strong></div>
                                <div class="print-box"><span>Result status</span><strong><?php echo print_status($record["result_status"]); ?></strong></div>
                                <div class="print-box"><span>Medication status</span><strong><?php echo print_status($record["medication_status"]); ?></strong></div>
                                <div class="print-box"><span>Vitals</span><strong>HR: <?php echo print_value($record["heart_rate"]); ?> | BP: <?php echo print_value($record["blood_pressure"]); ?> | Temp: <?php echo print_value($record["temperature"]); ?> | Weight: <?php echo print_value($record["weight"]); ?></strong></div>
                                <div class="print-box"><span>Doctor/staff</span><strong><?php echo print_value($record["doctor_name"]); ?></strong></div>
                                <div class="print-box"><span>Diagnosis</span><strong><?php echo print_value($record["diagnosis"]); ?></strong></div>
                                <div class="print-box"><span>Prescription</span><strong><?php echo print_value($record["prescription_notes"]); ?></strong></div>
                            </div>
                        </article>
                    <?php } ?>
                <?php } ?>
            </section>

            <section class="print-section">
                <h3>Medication List</h3>
                <?php if (empty($medications)) { ?>
                    <p>No medication reminders available.</p>
                <?php } else { ?>
                    <?php foreach ($medications as $medication) { ?>
                        <article class="print-record">
                            <div class="print-record-header">
                                <strong><?php echo print_value($medication["medication_name"]); ?></strong>
                                <span><?php echo print_status($medication["status"]); ?></span>
                            </div>
                            <div class="print-grid">
                                <div class="print-box"><span>Dosage</span><strong><?php echo print_value($medication["dosage"]); ?></strong></div>
                                <div class="print-box"><span>Frequency</span><strong><?php echo print_value($medication["frequency"]); ?></strong></div>
                                <div class="print-box"><span>Start date</span><strong><?php echo print_value($medication["start_date"]); ?></strong></div>
                                <div class="print-box"><span>End date</span><strong><?php echo print_value($medication["end_date"]); ?></strong></div>
                            </div>
                        </article>
                    <?php } ?>
                <?php } ?>
            </section>

            <p class="print-note">This summary is generated from Medic records and should be reviewed by a qualified medical professional before clinical decisions are made.</p>
        </section>
    </main>
</body>
</html>
