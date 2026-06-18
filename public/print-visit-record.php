<?php
include_once __DIR__ . "/../includes/session.php";
include_once __DIR__ . "/../config/db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "hospital") {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$record_id = 0;
$organization_id = 0;
$organization = array(
    "hospital_name" => $_SESSION["name_tag"],
    "organization_type" => "Medical organization",
    "phone" => "Not provided",
    "address" => "Not provided",
    "state" => "Not provided",
    "lga" => "Not provided",
);
$record_found = false;
$record = array();

if (isset($_GET["record_id"])) {
    $record_id = (int) $_GET["record_id"];
}

$hospital_stmt = $conn->prepare("SELECT id, hospital_name, organization_type, phone, address, state, lga FROM hospitals WHERE user_id = ? LIMIT 1");
$hospital_stmt->bind_param("i", $user_id);
$hospital_stmt->execute();
$hospital_stmt->bind_result($hospital_id, $hospital_name, $organization_type, $phone, $address, $state, $lga);

if ($hospital_stmt->fetch()) {
    $organization_id = $hospital_id;

    if (!empty($hospital_name)) {
        $organization["hospital_name"] = $hospital_name;
    }

    if (!empty($organization_type)) {
        $organization["organization_type"] = ucwords(str_replace("_", " ", $organization_type));
    }

    if (!empty($phone)) {
        $organization["phone"] = $phone;
    }

    if (!empty($address)) {
        $organization["address"] = $address;
    }

    if (!empty($state)) {
        $organization["state"] = $state;
    }

    if (!empty($lga)) {
        $organization["lga"] = $lga;
    }
}

$hospital_stmt->close();

if ($record_id > 0 && $organization_id > 0) {
    $record_stmt = $conn->prepare("SELECT medical_records.visit_date, medical_records.visit_type, medical_records.heart_rate, medical_records.blood_pressure, medical_records.temperature, medical_records.weight, medical_records.symptoms, medical_records.possible_illness, medical_records.result_status, medical_records.result_summary, medical_records.doctor_name, medical_records.diagnosis, medical_records.treatment_notes, medical_records.prescription_notes, medical_records.medication_status, patients.full_name, patients.nhis_number, patients.health_id, patients.phone, patients.gender, patients.date_of_birth, patients.blood_group, patients.genotype, patients.allergies, patients.chronic_conditions FROM medical_records INNER JOIN patients ON medical_records.patient_id = patients.id WHERE medical_records.id = ? AND medical_records.hospital_id = ? LIMIT 1");
    $record_stmt->bind_param("ii", $record_id, $organization_id);
    $record_stmt->execute();
    $record_stmt->bind_result($visit_date, $visit_type, $heart_rate, $blood_pressure, $temperature, $weight, $symptoms, $possible_illness, $result_status, $result_summary, $doctor_name, $diagnosis, $treatment_notes, $prescription_notes, $medication_status, $full_name, $nhis_number, $health_id, $patient_phone, $gender, $date_of_birth, $blood_group, $genotype, $allergies, $chronic_conditions);

    if ($record_stmt->fetch()) {
        $record_found = true;
        $record = array(
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
            "full_name" => $full_name,
            "nhis_number" => $nhis_number,
            "health_id" => $health_id,
            "patient_phone" => $patient_phone,
            "gender" => $gender,
            "date_of_birth" => $date_of_birth,
            "blood_group" => $blood_group,
            "genotype" => $genotype,
            "allergies" => $allergies,
            "chronic_conditions" => $chronic_conditions,
        );
    }

    $record_stmt->close();
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

    if ($value == "not_started") {
        return "Not started";
    }

    if ($value == "in_progress") {
        return "In progress";
    }

    if ($value == "completed") {
        return "Completed";
    }

    return print_value($value);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Visit Record | Medic</title>
    <link rel="stylesheet" href="assets/css/print-summary.css">
</head>
<body>
    <main class="print-page">
        <div class="print-actions">
            <a href="hospital-medical-records.php">Back to records</a>
            <?php if ($record_found) { ?>
                <button type="button" onclick="window.print()">Print / Save PDF</button>
            <?php } ?>
        </div>

        <section class="print-sheet">
            <?php if (!$record_found) { ?>
                <div class="print-title">
                    <span>Record unavailable</span>
                    <h2>This visit record could not be found for your organization.</h2>
                </div>
            <?php } else { ?>
                <header class="print-header">
                    <div class="print-brand">
                        <h1>Medic</h1>
                        <p><?php echo print_value($organization["hospital_name"]); ?></p>
                        <p><?php echo print_value($organization["organization_type"]); ?> | <?php echo print_value($organization["state"] . " / " . $organization["lga"]); ?></p>
                    </div>
                    <div class="print-meta">
                        <p>Generated: <?php echo htmlspecialchars(date("M d, Y h:i A")); ?></p>
                        <p>Visit date: <?php echo print_value($record["visit_date"]); ?></p>
                    </div>
                </header>

                <div class="print-title">
                    <span>Visit record</span>
                    <h2><?php echo print_value($record["full_name"]); ?></h2>
                </div>

                <section class="print-grid">
                    <div class="print-box"><span>NHIS number</span><strong><?php echo print_value($record["nhis_number"]); ?></strong></div>
                    <div class="print-box"><span>Health ID</span><strong><?php echo print_value($record["health_id"]); ?></strong></div>
                    <div class="print-box"><span>Phone</span><strong><?php echo print_value($record["patient_phone"]); ?></strong></div>
                    <div class="print-box"><span>Gender</span><strong><?php echo print_value(ucfirst($record["gender"])); ?></strong></div>
                    <div class="print-box"><span>Date of birth</span><strong><?php echo print_value($record["date_of_birth"]); ?></strong></div>
                    <div class="print-box"><span>Blood group / genotype</span><strong><?php echo print_value($record["blood_group"] . " / " . $record["genotype"]); ?></strong></div>
                    <div class="print-box"><span>Allergies</span><strong><?php echo print_value($record["allergies"]); ?></strong></div>
                    <div class="print-box"><span>Chronic conditions</span><strong><?php echo print_value($record["chronic_conditions"]); ?></strong></div>
                </section>

                <section class="print-section">
                    <h3>Clinical Details</h3>
                    <div class="print-grid">
                        <div class="print-box"><span>Visit type</span><strong><?php echo print_value($record["visit_type"]); ?></strong></div>
                        <div class="print-box"><span>Doctor / staff</span><strong><?php echo print_value($record["doctor_name"]); ?></strong></div>
                        <div class="print-box"><span>Heart rate</span><strong><?php echo print_value($record["heart_rate"]); ?></strong></div>
                        <div class="print-box"><span>Blood pressure</span><strong><?php echo print_value($record["blood_pressure"]); ?></strong></div>
                        <div class="print-box"><span>Temperature</span><strong><?php echo print_value($record["temperature"]); ?></strong></div>
                        <div class="print-box"><span>Weight</span><strong><?php echo print_value($record["weight"]); ?></strong></div>
                        <div class="print-box"><span>Symptoms / complaint</span><strong><?php echo print_value($record["symptoms"]); ?></strong></div>
                        <div class="print-box"><span>Possible illness</span><strong><?php echo print_value($record["possible_illness"]); ?></strong></div>
                        <div class="print-box"><span>Result status</span><strong><?php echo print_status($record["result_status"]); ?></strong></div>
                        <div class="print-box"><span>Medication status</span><strong><?php echo print_status($record["medication_status"]); ?></strong></div>
                        <div class="print-box"><span>Diagnosis / summary</span><strong><?php echo print_value($record["diagnosis"]); ?></strong></div>
                        <div class="print-box"><span>Result summary</span><strong><?php echo print_value($record["result_summary"]); ?></strong></div>
                        <div class="print-box"><span>Treatment notes</span><strong><?php echo print_value($record["treatment_notes"]); ?></strong></div>
                        <div class="print-box"><span>Prescription notes</span><strong><?php echo print_value($record["prescription_notes"]); ?></strong></div>
                    </div>
                </section>

                <p class="print-note">This visit record was generated from Medic organization records and should be reviewed by authorized medical staff before clinical decisions are made.</p>
            <?php } ?>
        </section>
    </main>
</body>
</html>
