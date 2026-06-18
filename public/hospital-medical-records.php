<?php
include_once __DIR__ . "/../includes/session.php";
include_once __DIR__ . "/../includes/functions.php";
include_once __DIR__ . "/../config/db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "hospital") {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$organization_id = 0;
$organization_name = $_SESSION["name_tag"];
$account_status = "pending";
$onboarding_completed = 0;
$update_message = "";
$update_error = "";
$records = array();

$status_stmt = $conn->prepare("SELECT status FROM users WHERE id = ? LIMIT 1");
$status_stmt->bind_param("i", $user_id);
$status_stmt->execute();
$status_stmt->bind_result($saved_status);

if ($status_stmt->fetch()) {
    $account_status = $saved_status;
}

$status_stmt->close();

$hospital_stmt = $conn->prepare("SELECT id, hospital_name, onboarding_completed FROM hospitals WHERE user_id = ? LIMIT 1");
$hospital_stmt->bind_param("i", $user_id);
$hospital_stmt->execute();
$hospital_stmt->bind_result($saved_organization_id, $saved_organization_name, $saved_onboarding_completed);

if ($hospital_stmt->fetch()) {
    $organization_id = $saved_organization_id;
    $onboarding_completed = $saved_onboarding_completed;

    if (!empty($saved_organization_name)) {
        $organization_name = $saved_organization_name;
    }
}

$hospital_stmt->close();

if ($onboarding_completed != 1) {
    header("Location: hospital-onboarding.php");
    exit;
}

if ($account_status != "active") {
    header("Location: hospital-pending.php");
    exit;
}

if (is_post_request() && isset($_POST["update_medical_record"])) {
    $record_id = 0;
    $result_status = "awaiting_result";
    $result_summary = "";
    $diagnosis = "";
    $treatment_notes = "";
    $prescription_notes = "";
    $medication_status = "not_started";

    if (isset($_POST["record_id"])) {
        $record_id = (int) $_POST["record_id"];
    }

    if (isset($_POST["result_status"])) {
        $result_status = clean_input($_POST["result_status"]);
    }

    if (isset($_POST["result_summary"])) {
        $result_summary = clean_input($_POST["result_summary"]);
    }

    if (isset($_POST["diagnosis"])) {
        $diagnosis = clean_input($_POST["diagnosis"]);
    }

    if (isset($_POST["treatment_notes"])) {
        $treatment_notes = clean_input($_POST["treatment_notes"]);
    }

    if (isset($_POST["prescription_notes"])) {
        $prescription_notes = clean_input($_POST["prescription_notes"]);
    }

    if (isset($_POST["medication_status"])) {
        $medication_status = clean_input($_POST["medication_status"]);
    }

    if ($record_id <= 0) {
        $update_error = "Select a valid medical record before updating.";
    }

    if (empty($update_error)) {
        $check_stmt = $conn->prepare("SELECT id FROM medical_records WHERE id = ? AND hospital_id = ? LIMIT 1");
        $check_stmt->bind_param("ii", $record_id, $organization_id);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows != 1) {
            $update_error = "This medical record does not belong to your organization.";
        }

        $check_stmt->close();
    }

    if (empty($update_error)) {
        $update_stmt = $conn->prepare("UPDATE medical_records SET result_status = ?, result_summary = ?, diagnosis = ?, treatment_notes = ?, prescription_notes = ?, medication_status = ?, updated_at = NOW() WHERE id = ? AND hospital_id = ?");
        $update_stmt->bind_param("ssssssii", $result_status, $result_summary, $diagnosis, $treatment_notes, $prescription_notes, $medication_status, $record_id, $organization_id);

        if ($update_stmt->execute()) {
            $update_message = "Medical record updated successfully.";
        } else {
            $update_error = "Medical record could not be updated. Please try again.";
        }

        $update_stmt->close();
    }
}

if ($organization_id > 0) {
    $record_stmt = $conn->prepare("SELECT medical_records.id, medical_records.visit_date, medical_records.visit_type, medical_records.heart_rate, medical_records.blood_pressure, medical_records.temperature, medical_records.weight, medical_records.symptoms, medical_records.possible_illness, medical_records.result_status, medical_records.result_summary, medical_records.doctor_name, medical_records.diagnosis, medical_records.treatment_notes, medical_records.prescription_notes, medical_records.medication_status, patients.full_name, patients.nhis_number FROM medical_records INNER JOIN patients ON medical_records.patient_id = patients.id WHERE medical_records.hospital_id = ? ORDER BY medical_records.visit_date DESC, medical_records.created_at DESC");
    $record_stmt->bind_param("i", $organization_id);
    $record_stmt->execute();
    $record_stmt->bind_result($record_id, $visit_date, $visit_type, $heart_rate, $blood_pressure, $temperature, $weight, $symptoms, $possible_illness, $result_status, $result_summary, $doctor_name, $diagnosis, $treatment_notes, $prescription_notes, $medication_status, $full_name, $nhis_number);

    while ($record_stmt->fetch()) {
        $records[] = array(
            "id" => $record_id,
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
        );
    }

    $record_stmt->close();
}

function record_value($value)
{
    if (!empty($value)) {
        return htmlspecialchars($value);
    }

    return "Not provided";
}

function record_form_value($value)
{
    if (!empty($value)) {
        return htmlspecialchars($value);
    }

    return "";
}

function selected_option($saved_value, $current_value)
{
    if ($saved_value == $current_value) {
        return "selected";
    }

    return "";
}

function record_status_label($value)
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

    return record_value($value);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Records | Medic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/patient-onboarding.css">
</head>
<body>
    <main class="profile-page">
        <header class="profile-header">
            <a href="../dashboard/hospital.php" class="brand">
                <img src="https://placehold.co/44x44/2563EB/FFFFFF?text=M" alt="Medic logo">
                <span>Medic</span>
            </a>

            <div class="profile-actions">
                <a href="../dashboard/hospital.php" class="secondary-action">Dashboard</a>
                <a href="hospital-nhis-lookup.php" class="secondary-action">NHIS Lookup</a>
                <form action="../auth/logout.php" method="post">
                    <button type="submit" class="primary-action">Logout</button>
                </form>
            </div>
        </header>

        <section class="profile-hero-card lookup-hero">
            <div class="profile-avatar-large">
                <span>RX</span>
            </div>

            <div>
                <span class="eyebrow">Medical records</span>
                <h1>Update care results for <?php echo htmlspecialchars($organization_name); ?>.</h1>
                <p>
                    Use this page after lab results are ready, treatment has changed, or medication progress needs to be updated.
                    The individual will see these updates on their own medical records page.
                </p>

                <?php
                if (!empty($update_message)) {
                    echo "<p class=\"form-alert success-alert\">" . htmlspecialchars($update_message) . "</p>";
                }

                if (!empty($update_error)) {
                    echo "<p class=\"form-alert lookup-alert\">" . htmlspecialchars($update_error) . "</p>";
                }
                ?>
            </div>
        </section>

        <section class="profile-grid recent-records-grid">
            <article class="profile-card recent-records-card">
                <div class="profile-card-heading">
                    <span>Record queue</span>
                    <h2>Visits created by your organization</h2>
                </div>

                <?php if (empty($records)) { ?>
                    <p class="helper-text">No medical records have been created by this organization yet. Use NHIS lookup to add a walk-in visit record first.</p>
                <?php } else { ?>
                    <div class="record-timeline">
                        <?php foreach ($records as $record) { ?>
                            <article class="record-item">
                                <div class="record-item-header">
                                    <div>
                                        <span>NHIS: <?php echo record_value($record["nhis_number"]); ?></span>
                                        <h3><?php echo record_value($record["full_name"]); ?></h3>
                                    </div>
                                    <strong><?php echo record_value($record["visit_date"]); ?></strong>
                                </div>

                                <div class="record-status-row">
                                    <span>Possible illness: <?php echo record_value($record["possible_illness"]); ?></span>
                                    <span>Result: <?php echo record_status_label($record["result_status"]); ?></span>
                                    <span>Medication: <?php echo record_status_label($record["medication_status"]); ?></span>
                                </div>

                                <div class="clinical-grid">
                                    <div>
                                        <span>Heart rate</span>
                                        <strong><?php echo record_value($record["heart_rate"]); ?></strong>
                                    </div>
                                    <div>
                                        <span>Blood pressure</span>
                                        <strong><?php echo record_value($record["blood_pressure"]); ?></strong>
                                    </div>
                                    <div>
                                        <span>Temperature</span>
                                        <strong><?php echo record_value($record["temperature"]); ?></strong>
                                    </div>
                                    <div>
                                        <span>Weight</span>
                                        <strong><?php echo record_value($record["weight"]); ?></strong>
                                    </div>
                                </div>

                                <form action="" method="post" class="record-update-form">
                                    <input type="hidden" name="record_id" value="<?php echo htmlspecialchars($record["id"]); ?>">

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label" for="resultStatus<?php echo htmlspecialchars($record["id"]); ?>">Result status</label>
                                            <select class="form-select" id="resultStatus<?php echo htmlspecialchars($record["id"]); ?>" name="result_status">
                                                <option value="awaiting_result" <?php echo selected_option($record["result_status"], "awaiting_result"); ?>>Awaiting result</option>
                                                <option value="result_ready" <?php echo selected_option($record["result_status"], "result_ready"); ?>>Result ready</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label" for="medicationStatus<?php echo htmlspecialchars($record["id"]); ?>">Medication status</label>
                                            <select class="form-select" id="medicationStatus<?php echo htmlspecialchars($record["id"]); ?>" name="medication_status">
                                                <option value="not_started" <?php echo selected_option($record["medication_status"], "not_started"); ?>>Not started</option>
                                                <option value="in_progress" <?php echo selected_option($record["medication_status"], "in_progress"); ?>>In progress</option>
                                                <option value="completed" <?php echo selected_option($record["medication_status"], "completed"); ?>>Completed</option>
                                            </select>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label" for="resultSummary<?php echo htmlspecialchars($record["id"]); ?>">Result summary</label>
                                            <textarea class="form-control" id="resultSummary<?php echo htmlspecialchars($record["id"]); ?>" name="result_summary" rows="3"><?php echo record_form_value($record["result_summary"]); ?></textarea>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label" for="diagnosis<?php echo htmlspecialchars($record["id"]); ?>">Diagnosis / summary</label>
                                            <textarea class="form-control" id="diagnosis<?php echo htmlspecialchars($record["id"]); ?>" name="diagnosis" rows="3"><?php echo record_form_value($record["diagnosis"]); ?></textarea>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label" for="treatmentNotes<?php echo htmlspecialchars($record["id"]); ?>">Treatment notes</label>
                                            <textarea class="form-control" id="treatmentNotes<?php echo htmlspecialchars($record["id"]); ?>" name="treatment_notes" rows="3"><?php echo record_form_value($record["treatment_notes"]); ?></textarea>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label" for="prescriptionNotes<?php echo htmlspecialchars($record["id"]); ?>">Prescription notes</label>
                                            <textarea class="form-control" id="prescriptionNotes<?php echo htmlspecialchars($record["id"]); ?>" name="prescription_notes" rows="3"><?php echo record_form_value($record["prescription_notes"]); ?></textarea>
                                        </div>

                                        <div class="col-12">
                                            <button type="submit" name="update_medical_record" class="primary-action">Update record</button>
                                            <a href="print-visit-record.php?record_id=<?php echo htmlspecialchars($record["id"]); ?>" class="secondary-action">Print visit record</a>
                                        </div>
                                    </div>
                                </form>
                            </article>
                        <?php } ?>
                    </div>
                <?php } ?>
            </article>
        </section>
    </main>
</body>
</html>
