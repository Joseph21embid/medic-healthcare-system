<?php
include_once __DIR__ . "/../includes/session.php";
include_once __DIR__ . "/../includes/functions.php";
include_once __DIR__ . "/../config/db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "patient") {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$patient_id = 0;
$individual_name = $_SESSION["name_tag"];
$nhis_number = "Not provided";
$records = array();
$record_columns_ready = false;

$column_check_sql = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'medical_records' AND COLUMN_NAME IN ('visit_type', 'heart_rate', 'blood_pressure', 'temperature', 'weight', 'symptoms', 'possible_illness', 'result_status', 'result_summary', 'medication_status')";
$column_check_stmt = $conn->prepare($column_check_sql);
$column_check_stmt->execute();
$column_check_stmt->bind_result($existing_record_columns);

if ($column_check_stmt->fetch()) {
    if ($existing_record_columns == 10) {
        $record_columns_ready = true;
    }
}

$column_check_stmt->close();

$patient_stmt = $conn->prepare("SELECT id, full_name, nhis_number FROM patients WHERE user_id = ? LIMIT 1");
$patient_stmt->bind_param("i", $user_id);
$patient_stmt->execute();
$patient_stmt->bind_result($saved_patient_id, $saved_full_name, $saved_nhis_number);

if ($patient_stmt->fetch()) {
    $patient_id = $saved_patient_id;

    if (!empty($saved_full_name)) {
        $individual_name = $saved_full_name;
    }

    if (!empty($saved_nhis_number)) {
        $nhis_number = $saved_nhis_number;
    }
}

$patient_stmt->close();

if ($patient_id > 0) {
    if ($record_columns_ready) {
        $record_stmt = $conn->prepare("SELECT medical_records.visit_date, medical_records.visit_type, medical_records.heart_rate, medical_records.blood_pressure, medical_records.temperature, medical_records.weight, medical_records.symptoms, medical_records.possible_illness, medical_records.result_status, medical_records.result_summary, medical_records.doctor_name, medical_records.diagnosis, medical_records.treatment_notes, medical_records.prescription_notes, medical_records.medication_status, hospitals.hospital_name FROM medical_records LEFT JOIN hospitals ON medical_records.hospital_id = hospitals.id WHERE medical_records.patient_id = ? ORDER BY medical_records.visit_date DESC, medical_records.created_at DESC");
        $record_stmt->bind_param("i", $patient_id);
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
    } else {
        $record_stmt = $conn->prepare("SELECT medical_records.visit_date, medical_records.doctor_name, medical_records.diagnosis, medical_records.treatment_notes, medical_records.prescription_notes, hospitals.hospital_name FROM medical_records LEFT JOIN hospitals ON medical_records.hospital_id = hospitals.id WHERE medical_records.patient_id = ? ORDER BY medical_records.visit_date DESC, medical_records.created_at DESC");
        $record_stmt->bind_param("i", $patient_id);
        $record_stmt->execute();
        $record_stmt->bind_result($visit_date, $doctor_name, $diagnosis, $treatment_notes, $prescription_notes, $hospital_name);

        while ($record_stmt->fetch()) {
            $records[] = array(
                "visit_date" => $visit_date,
                "visit_type" => "walk_in",
                "heart_rate" => "",
                "blood_pressure" => "",
                "temperature" => "",
                "weight" => "",
                "symptoms" => "",
                "possible_illness" => "",
                "result_status" => "",
                "result_summary" => "",
                "doctor_name" => $doctor_name,
                "diagnosis" => $diagnosis,
                "treatment_notes" => $treatment_notes,
                "prescription_notes" => $prescription_notes,
                "medication_status" => "",
                "hospital_name" => $hospital_name,
            );
        }

        $record_stmt->close();
    }
}

function show_record_value($value)
{
    if (!empty($value)) {
        return htmlspecialchars($value);
    }

    return "Not provided";
}

function show_record_status($value)
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

    return show_record_value($value);
}

function show_visit_type($value)
{
    if ($value == "appointment") {
        return "Online appointment";
    }

    return "Walk-in visit";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Medical Records | Medic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/patient-onboarding.css">
</head>
<body>
    <main class="profile-page">
        <header class="profile-header">
            <a href="../dashboard/patient.php" class="brand">
                <img src="https://placehold.co/44x44/2563EB/FFFFFF?text=M" alt="Medic logo">
                <span>Medic</span>
            </a>

            <div class="profile-actions">
                <a href="../dashboard/patient.php" class="secondary-action">Dashboard</a>
                <a href="individual-profile.php" class="secondary-action">Profile</a>
                <a href="print-individual-summary.php" class="secondary-action">Print summary</a>
                <form action="../auth/logout.php" method="post">
                    <button type="submit" class="primary-action">Logout</button>
                </form>
            </div>
        </header>

        <section class="profile-hero-card lookup-hero">
            <div class="profile-avatar-large">
                <span>MR</span>
            </div>

            <div>
                <span class="eyebrow">Medical records</span>
                <h1>Your care history, <?php echo htmlspecialchars($individual_name); ?>.</h1>
                <p>
                    Approved hospitals, clinics, and medical firms can add visit records after identifying you with your NHIS number.
                    You will see possible illness, result status, prescriptions, and medication progress here.
                </p>
                <div class="record-status-row">
                    <span>NHIS: <?php echo htmlspecialchars($nhis_number); ?></span>
                    <span>Total records: <?php echo htmlspecialchars(count($records)); ?></span>
                </div>
            </div>
        </section>

        <?php if (!$record_columns_ready) { ?>
            <section class="profile-grid recent-records-grid">
                <article class="profile-card recent-records-card">
                    <div class="profile-card-heading">
                        <span>Setup needed</span>
                        <h2>Stage-one record fields are not in the database yet</h2>
                    </div>
                    <p class="helper-text">Run the SQL I provide after this update so this page can show vitals, possible illness, result status, and medication status.</p>
                </article>
            </section>
        <?php } ?>

        <section class="profile-grid recent-records-grid">
            <article class="profile-card recent-records-card">
                <div class="profile-card-heading">
                    <span>Visit timeline</span>
                    <h2>Records from approved organizations</h2>
                </div>

                <?php if (empty($records)) { ?>
                    <p class="helper-text">No medical records have been added for your profile yet.</p>
                <?php } else { ?>
                    <div class="record-timeline">
                        <?php foreach ($records as $record) { ?>
                            <article class="record-item">
                                <div class="record-item-header">
                                    <div>
                                        <span><?php echo show_visit_type($record["visit_type"]); ?></span>
                                        <h3><?php echo show_record_value($record["hospital_name"]); ?></h3>
                                    </div>
                                    <strong><?php echo show_record_value($record["visit_date"]); ?></strong>
                                </div>

                                <div class="record-status-row">
                                    <span>Possible illness: <?php echo show_record_value($record["possible_illness"]); ?></span>
                                    <span>Result: <?php echo show_record_status($record["result_status"]); ?></span>
                                    <span>Medication: <?php echo show_record_status($record["medication_status"]); ?></span>
                                </div>

                                <?php if ($record["medication_status"] == "completed") { ?>
                                    <div class="checkup-reminder">
                                        <strong>Follow-up checkup recommended</strong>
                                        <p>Your medication is marked as completed. Please book or attend another checkup so a medical professional can confirm your vitals and recovery status.</p>
                                    </div>
                                <?php } ?>

                                <div class="clinical-grid">
                                    <div>
                                        <span>Heart rate</span>
                                        <strong><?php echo show_record_value($record["heart_rate"]); ?></strong>
                                    </div>
                                    <div>
                                        <span>Blood pressure</span>
                                        <strong><?php echo show_record_value($record["blood_pressure"]); ?></strong>
                                    </div>
                                    <div>
                                        <span>Temperature</span>
                                        <strong><?php echo show_record_value($record["temperature"]); ?></strong>
                                    </div>
                                    <div>
                                        <span>Weight</span>
                                        <strong><?php echo show_record_value($record["weight"]); ?></strong>
                                    </div>
                                </div>

                                <div class="profile-detail-list record-notes">
                                    <div>
                                        <span>Symptoms / complaint</span>
                                        <strong><?php echo show_record_value($record["symptoms"]); ?></strong>
                                    </div>
                                    <div>
                                        <span>Diagnosis / summary</span>
                                        <strong><?php echo show_record_value($record["diagnosis"]); ?></strong>
                                    </div>
                                    <div>
                                        <span>Result summary</span>
                                        <strong><?php echo show_record_value($record["result_summary"]); ?></strong>
                                    </div>
                                    <div>
                                        <span>Treatment notes</span>
                                        <strong><?php echo show_record_value($record["treatment_notes"]); ?></strong>
                                    </div>
                                    <div>
                                        <span>Prescription notes</span>
                                        <strong><?php echo show_record_value($record["prescription_notes"]); ?></strong>
                                    </div>
                                    <div>
                                        <span>Doctor / staff</span>
                                        <strong><?php echo show_record_value($record["doctor_name"]); ?></strong>
                                    </div>
                                </div>
                            </article>
                        <?php } ?>
                    </div>
                <?php } ?>
            </article>
        </section>
    </main>
</body>
</html>
