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
$account_status = "pending";
$onboarding_completed = 0;
$organization_name = $_SESSION["name_tag"];
$nhis_number = "";
$lookup_message = "";
$record_message = "";
$record_error = "";
$record_columns_ready = false;
$individual_found = false;
$individual = array(
    "id" => 0,
    "health_id" => "",
    "nhis_number" => "",
    "full_name" => "",
    "phone" => "",
    "gender" => "",
    "date_of_birth" => "",
    "blood_group" => "",
    "genotype" => "",
    "allergies" => "",
    "chronic_conditions" => "",
    "current_medications" => "",
    "emergency_contact_name" => "",
    "emergency_contact_phone" => "",
);
$recent_records = array();
$required_record_columns = array(
    "visit_type",
    "heart_rate",
    "blood_pressure",
    "temperature",
    "weight",
    "symptoms",
    "possible_illness",
    "result_status",
    "result_summary",
    "medication_status"
);

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

    if (!empty($saved_organization_name)) {
        $organization_name = $saved_organization_name;
    }

    $onboarding_completed = $saved_onboarding_completed;
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

$column_check_sql = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'medical_records' AND COLUMN_NAME IN ('visit_type', 'heart_rate', 'blood_pressure', 'temperature', 'weight', 'symptoms', 'possible_illness', 'result_status', 'result_summary', 'medication_status')";
$column_check_stmt = $conn->prepare($column_check_sql);
$column_check_stmt->execute();
$column_check_stmt->bind_result($existing_record_columns);

if ($column_check_stmt->fetch()) {
    if ($existing_record_columns == count($required_record_columns)) {
        $record_columns_ready = true;
    }
}

$column_check_stmt->close();

if (is_post_request() && isset($_POST["save_medical_record"])) {
    $record_patient_id = 0;
    $visit_type = "walk_in";
    $heart_rate = "";
    $blood_pressure = "";
    $temperature = "";
    $weight = "";
    $symptoms = "";
    $possible_illness = "";
    $result_status = "awaiting_result";
    $result_summary = "";
    $doctor_name = "";
    $diagnosis = "";
    $treatment_notes = "";
    $prescription_notes = "";
    $medication_status = "not_started";
    $visit_date = "";

    if (isset($_POST["patient_id"])) {
        $record_patient_id = (int) $_POST["patient_id"];
    }

    if (isset($_POST["doctor_name"])) {
        $doctor_name = clean_input($_POST["doctor_name"]);
    }

    if (isset($_POST["visit_type"])) {
        $visit_type = clean_input($_POST["visit_type"]);
    }

    if (isset($_POST["heart_rate"])) {
        $heart_rate = clean_input($_POST["heart_rate"]);
    }

    if (isset($_POST["blood_pressure"])) {
        $blood_pressure = clean_input($_POST["blood_pressure"]);
    }

    if (isset($_POST["temperature"])) {
        $temperature = clean_input($_POST["temperature"]);
    }

    if (isset($_POST["weight"])) {
        $weight = clean_input($_POST["weight"]);
    }

    if (isset($_POST["symptoms"])) {
        $symptoms = clean_input($_POST["symptoms"]);
    }

    if (isset($_POST["possible_illness"])) {
        $possible_illness = clean_input($_POST["possible_illness"]);
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

    if (isset($_POST["visit_date"])) {
        $visit_date = clean_input($_POST["visit_date"]);
    }

    if ($record_patient_id <= 0) {
        $record_error = "Select a valid individual before saving a medical record.";
    }

    if (empty($possible_illness) && empty($diagnosis)) {
        $record_error = "Enter possible illness or diagnosis summary before saving.";
    }

    if (empty($visit_date)) {
        $record_error = "Select visit date before saving.";
    }

    if (!$record_columns_ready) {
        $record_error = "The medical record table needs the stage-one walk-in columns before this record can be saved.";
    }

    if (empty($record_error)) {
        $patient_stmt = $conn->prepare("SELECT id, health_id, nhis_number, full_name, phone, gender, date_of_birth, blood_group, genotype, allergies, chronic_conditions, current_medications, emergency_contact_name, emergency_contact_phone FROM patients WHERE id = ? LIMIT 1");
        $patient_stmt->bind_param("i", $record_patient_id);
        $patient_stmt->execute();
        $patient_stmt->store_result();

        if ($patient_stmt->num_rows == 1) {
            $patient_stmt->bind_result($patient_id, $health_id, $saved_nhis_number, $full_name, $phone, $gender, $date_of_birth, $blood_group, $genotype, $allergies, $chronic_conditions, $current_medications, $emergency_contact_name, $emergency_contact_phone);
            $patient_stmt->fetch();

            $individual_found = true;
            $individual["id"] = $patient_id;
            $individual["health_id"] = $health_id;
            $individual["nhis_number"] = $saved_nhis_number;
            $individual["full_name"] = $full_name;
            $individual["phone"] = $phone;
            $individual["gender"] = $gender;
            $individual["date_of_birth"] = $date_of_birth;
            $individual["blood_group"] = $blood_group;
            $individual["genotype"] = $genotype;
            $individual["allergies"] = $allergies;
            $individual["chronic_conditions"] = $chronic_conditions;
            $individual["current_medications"] = $current_medications;
            $individual["emergency_contact_name"] = $emergency_contact_name;
            $individual["emergency_contact_phone"] = $emergency_contact_phone;
            $nhis_number = $saved_nhis_number;
        } else {
            $record_error = "Individual record could not be found.";
        }

        $patient_stmt->close();
    }

    if (empty($record_error)) {
        $insert_sql = "INSERT INTO medical_records (patient_id, hospital_id, visit_type, heart_rate, blood_pressure, temperature, weight, symptoms, possible_illness, result_status, result_summary, doctor_name, diagnosis, treatment_notes, prescription_notes, medication_status, visit_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("iisssssssssssssss", $record_patient_id, $organization_id, $visit_type, $heart_rate, $blood_pressure, $temperature, $weight, $symptoms, $possible_illness, $result_status, $result_summary, $doctor_name, $diagnosis, $treatment_notes, $prescription_notes, $medication_status, $visit_date);

        if ($insert_stmt->execute()) {
            $record_message = "Medical record saved successfully.";
        } else {
            $record_error = "Medical record could not be saved. Please try again.";
        }

        $insert_stmt->close();
    }
}

if (is_post_request() && isset($_POST["search_nhis"])) {
    if (isset($_POST["nhis_number"])) {
        $nhis_number = clean_input($_POST["nhis_number"]);
    }

    if (empty($nhis_number)) {
        $lookup_message = "Enter an NHIS number to search.";
    } elseif (!preg_match("/^[A-Za-z0-9\-\/ ]{4,50}$/", $nhis_number)) {
        $lookup_message = "Enter a valid NHIS number.";
    } else {
        $patient_stmt = $conn->prepare("SELECT id, health_id, nhis_number, full_name, phone, gender, date_of_birth, blood_group, genotype, allergies, chronic_conditions, current_medications, emergency_contact_name, emergency_contact_phone FROM patients WHERE nhis_number = ? LIMIT 1");
        $patient_stmt->bind_param("s", $nhis_number);
        $patient_stmt->execute();
        $patient_stmt->store_result();

        if ($patient_stmt->num_rows == 1) {
            $patient_stmt->bind_result($patient_id, $health_id, $saved_nhis_number, $full_name, $phone, $gender, $date_of_birth, $blood_group, $genotype, $allergies, $chronic_conditions, $current_medications, $emergency_contact_name, $emergency_contact_phone);
            $patient_stmt->fetch();

            $individual_found = true;
            $individual["id"] = $patient_id;
            $individual["health_id"] = $health_id;
            $individual["nhis_number"] = $saved_nhis_number;
            $individual["full_name"] = $full_name;
            $individual["phone"] = $phone;
            $individual["gender"] = $gender;
            $individual["date_of_birth"] = $date_of_birth;
            $individual["blood_group"] = $blood_group;
            $individual["genotype"] = $genotype;
            $individual["allergies"] = $allergies;
            $individual["chronic_conditions"] = $chronic_conditions;
            $individual["current_medications"] = $current_medications;
            $individual["emergency_contact_name"] = $emergency_contact_name;
            $individual["emergency_contact_phone"] = $emergency_contact_phone;
        } else {
            $lookup_message = "No individual was found with that NHIS number.";
        }

        $patient_stmt->close();
    }
}

if ($organization_id > 0) {
    if ($record_columns_ready) {
        $recent_stmt = $conn->prepare("SELECT medical_records.visit_date, medical_records.doctor_name, medical_records.diagnosis, medical_records.possible_illness, medical_records.result_status, medical_records.medication_status, patients.full_name, patients.nhis_number FROM medical_records INNER JOIN patients ON medical_records.patient_id = patients.id WHERE medical_records.hospital_id = ? ORDER BY medical_records.created_at DESC LIMIT 5");
        $recent_stmt->bind_param("i", $organization_id);
        $recent_stmt->execute();
        $recent_stmt->bind_result($record_visit_date, $record_doctor_name, $record_diagnosis, $record_possible_illness, $record_result_status, $record_medication_status, $record_full_name, $record_nhis_number);

        while ($recent_stmt->fetch()) {
            $recent_records[] = array(
                "visit_date" => $record_visit_date,
                "doctor_name" => $record_doctor_name,
                "diagnosis" => $record_diagnosis,
                "possible_illness" => $record_possible_illness,
                "result_status" => $record_result_status,
                "medication_status" => $record_medication_status,
                "full_name" => $record_full_name,
                "nhis_number" => $record_nhis_number,
            );
        }
    } else {
        $recent_stmt = $conn->prepare("SELECT medical_records.visit_date, medical_records.doctor_name, medical_records.diagnosis, patients.full_name, patients.nhis_number FROM medical_records INNER JOIN patients ON medical_records.patient_id = patients.id WHERE medical_records.hospital_id = ? ORDER BY medical_records.created_at DESC LIMIT 5");
        $recent_stmt->bind_param("i", $organization_id);
        $recent_stmt->execute();
        $recent_stmt->bind_result($record_visit_date, $record_doctor_name, $record_diagnosis, $record_full_name, $record_nhis_number);

        while ($recent_stmt->fetch()) {
            $recent_records[] = array(
                "visit_date" => $record_visit_date,
                "doctor_name" => $record_doctor_name,
                "diagnosis" => $record_diagnosis,
                "possible_illness" => "",
                "result_status" => "",
                "medication_status" => "",
                "full_name" => $record_full_name,
                "nhis_number" => $record_nhis_number,
            );
        }
    }

    $recent_stmt->close();
}

function display_value($value)
{
    if (!empty($value)) {
        return htmlspecialchars($value);
    }

    return "Not provided";
}

function display_status_label($value)
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

    return display_value($value);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NHIS Individual Lookup | Medic</title>
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
                <form action="../auth/logout.php" method="post">
                    <button type="submit" class="primary-action">Logout</button>
                </form>
            </div>
        </header>

        <section class="profile-hero-card lookup-hero">
            <div class="profile-avatar-large">
                <span>NH</span>
            </div>

            <div>
                <span class="eyebrow">NHIS lookup</span>
                <h1>Find an individual by NHIS number.</h1>
                <p>
                    <?php echo htmlspecialchars($organization_name); ?> can use this page to find a limited individual summary before full record modules are added.
                </p>

                <form action="" method="post" class="lookup-form">
                    <input type="text" class="form-control" name="nhis_number" placeholder="Enter NHIS number" value="<?php echo htmlspecialchars($nhis_number); ?>">
                    <button type="submit" name="search_nhis" class="primary-action">Search</button>
                </form>

                <?php
                if (!empty($lookup_message)) {
                    echo "<p class=\"form-alert lookup-alert\">" . htmlspecialchars($lookup_message) . "</p>";
                }
                ?>
            </div>
        </section>

        <?php if ($individual_found) { ?>
            <section class="profile-grid lookup-result-grid">
                <article class="profile-card">
                    <div class="profile-card-heading">
                        <span>Identity</span>
                        <h2><?php echo display_value($individual["full_name"]); ?></h2>
                    </div>

                    <div class="profile-detail-list">
                        <div>
                            <span>NHIS number</span>
                            <strong><?php echo display_value($individual["nhis_number"]); ?></strong>
                        </div>
                        <div>
                            <span>Health ID</span>
                            <strong><?php echo display_value($individual["health_id"]); ?></strong>
                        </div>
                        <div>
                            <span>Gender</span>
                            <strong><?php echo display_value(ucfirst($individual["gender"])); ?></strong>
                        </div>
                        <div>
                            <span>Date of birth</span>
                            <strong><?php echo display_value($individual["date_of_birth"]); ?></strong>
                        </div>
                    </div>
                </article>

                <article class="profile-card">
                    <div class="profile-card-heading">
                        <span>Medical summary</span>
                        <h2>Care details</h2>
                    </div>

                    <div class="profile-detail-list">
                        <div>
                            <span>Blood group</span>
                            <strong><?php echo display_value($individual["blood_group"]); ?></strong>
                        </div>
                        <div>
                            <span>Genotype</span>
                            <strong><?php echo display_value($individual["genotype"]); ?></strong>
                        </div>
                        <div>
                            <span>Known allergies</span>
                            <strong><?php echo display_value($individual["allergies"]); ?></strong>
                        </div>
                        <div>
                            <span>Chronic conditions</span>
                            <strong><?php echo display_value($individual["chronic_conditions"]); ?></strong>
                        </div>
                        <div>
                            <span>Current medications</span>
                            <strong><?php echo display_value($individual["current_medications"]); ?></strong>
                        </div>
                    </div>
                </article>

                <article class="profile-card">
                    <div class="profile-card-heading">
                        <span>Emergency</span>
                        <h2>Contact details</h2>
                    </div>

                    <div class="profile-detail-list">
                        <div>
                            <span>Individual phone</span>
                            <strong><?php echo display_value($individual["phone"]); ?></strong>
                        </div>
                        <div>
                            <span>Emergency contact</span>
                            <strong><?php echo display_value($individual["emergency_contact_name"]); ?></strong>
                        </div>
                        <div>
                            <span>Emergency phone</span>
                            <strong><?php echo display_value($individual["emergency_contact_phone"]); ?></strong>
                        </div>
                    </div>
                </article>
            </section>

            <section class="profile-hero-card record-form-card">
                <div class="profile-avatar-large">
                    <span>MR</span>
                </div>

                <div>
                    <span class="eyebrow">Medical record</span>
                    <h1>Add visit record</h1>
                    <p>
                        Save a simple visit note for <?php echo display_value($individual["full_name"]); ?>. This creates the first version of organization-managed medical records.
                    </p>

                    <?php
                    if (!empty($record_message)) {
                        echo "<p class=\"form-alert success-alert\">" . htmlspecialchars($record_message) . "</p>";
                    }

                    if (!empty($record_error)) {
                        echo "<p class=\"form-alert lookup-alert\">" . htmlspecialchars($record_error) . "</p>";
                    }

                    if (!$record_columns_ready) {
                        echo "<p class=\"form-alert lookup-alert\">Run the stage-one medical record SQL before saving walk-in records. I will send the SQL after these file updates.</p>";
                    }
                    ?>

                    <form action="" method="post" class="record-form">
                        <input type="hidden" name="patient_id" value="<?php echo htmlspecialchars($individual["id"]); ?>">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="visitType" class="form-label">Visit type</label>
                                <select class="form-select" id="visitType" name="visit_type">
                                    <option value="walk_in" selected>Walk-in visit</option>
                                    <option value="appointment">Online appointment visit</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="visitDate" class="form-label">Visit date</label>
                                <input type="date" class="form-control" id="visitDate" name="visit_date" required>
                            </div>

                            <div class="col-md-6">
                                <label for="doctorName" class="form-label">Doctor / staff name</label>
                                <input type="text" class="form-control" id="doctorName" name="doctor_name" placeholder="Enter doctor or staff name">
                            </div>

                            <div class="col-md-6">
                                <label for="heartRate" class="form-label">Heart rate</label>
                                <input type="text" class="form-control" id="heartRate" name="heart_rate" placeholder="Example: 78 bpm">
                            </div>

                            <div class="col-md-4">
                                <label for="bloodPressure" class="form-label">Blood pressure</label>
                                <input type="text" class="form-control" id="bloodPressure" name="blood_pressure" placeholder="Example: 120/80">
                            </div>

                            <div class="col-md-4">
                                <label for="temperature" class="form-label">Temperature</label>
                                <input type="text" class="form-control" id="temperature" name="temperature" placeholder="Example: 37.2 C">
                            </div>

                            <div class="col-md-4">
                                <label for="weight" class="form-label">Weight</label>
                                <input type="text" class="form-control" id="weight" name="weight" placeholder="Example: 68 kg">
                            </div>

                            <div class="col-12">
                                <label for="symptoms" class="form-label">Symptoms / complaint</label>
                                <textarea class="form-control" id="symptoms" name="symptoms" rows="3" placeholder="Enter what the individual complained about"></textarea>
                            </div>

                            <div class="col-12">
                                <label for="possibleIllness" class="form-label">Possible illness</label>
                                <textarea class="form-control" id="possibleIllness" name="possible_illness" rows="3" placeholder="Example: Possible malaria, typhoid, infection, etc."></textarea>
                            </div>

                            <div class="col-12">
                                <label for="diagnosis" class="form-label">Diagnosis / visit summary</label>
                                <textarea class="form-control" id="diagnosis" name="diagnosis" rows="3" placeholder="Enter diagnosis or visit summary"></textarea>
                            </div>

                            <div class="col-md-6">
                                <label for="resultStatus" class="form-label">Lab/result status</label>
                                <select class="form-select" id="resultStatus" name="result_status">
                                    <option value="awaiting_result" selected>Awaiting result</option>
                                    <option value="result_ready">Result ready</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="medicationStatus" class="form-label">Medication status</label>
                                <select class="form-select" id="medicationStatus" name="medication_status">
                                    <option value="not_started" selected>Not started</option>
                                    <option value="in_progress">In progress</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label for="resultSummary" class="form-label">Result summary</label>
                                <textarea class="form-control" id="resultSummary" name="result_summary" rows="3" placeholder="Leave empty if result is not ready yet"></textarea>
                            </div>

                            <div class="col-12">
                                <label for="treatmentNotes" class="form-label">Treatment notes</label>
                                <textarea class="form-control" id="treatmentNotes" name="treatment_notes" rows="3" placeholder="Enter treatment notes"></textarea>
                            </div>

                            <div class="col-12">
                                <label for="prescriptionNotes" class="form-label">Prescription notes</label>
                                <textarea class="form-control" id="prescriptionNotes" name="prescription_notes" rows="3" placeholder="Enter prescription notes"></textarea>
                            </div>

                            <div class="col-12">
                                <button type="submit" name="save_medical_record" class="primary-action">Save medical record</button>
                            </div>
                        </div>
                    </form>
                </div>
            </section>
        <?php } ?>

        <section class="profile-grid recent-records-grid">
            <article class="profile-card recent-records-card">
                <div class="profile-card-heading">
                    <span>Recent records</span>
                    <h2>Latest records from your organization</h2>
                </div>

                <?php if (empty($recent_records)) { ?>
                    <p class="helper-text">No medical records have been created by this organization yet.</p>
                <?php } else { ?>
                    <div class="profile-detail-list">
                        <?php foreach ($recent_records as $record) { ?>
                            <div>
                                <span>
                                    <?php echo display_value($record["visit_date"]); ?> |
                                    NHIS: <?php echo display_value($record["nhis_number"]); ?>
                                </span>
                                <strong><?php echo display_value($record["full_name"]); ?></strong>
                                <p class="helper-text"><?php echo display_value($record["diagnosis"]); ?></p>
                                <p class="helper-text">Possible illness: <?php echo display_value($record["possible_illness"]); ?></p>
                                <p class="helper-text">Result: <?php echo display_status_label($record["result_status"]); ?> | Medication: <?php echo display_status_label($record["medication_status"]); ?></p>
                                <p class="helper-text">Doctor/staff: <?php echo display_value($record["doctor_name"]); ?></p>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>
            </article>
        </section>
    </main>
</body>
</html>
