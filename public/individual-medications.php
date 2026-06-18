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
$message = "";
$error = "";
$medications = array();

$patient_stmt = $conn->prepare("SELECT id, full_name FROM patients WHERE user_id = ? LIMIT 1");
$patient_stmt->bind_param("i", $user_id);
$patient_stmt->execute();
$patient_stmt->bind_result($saved_patient_id, $saved_full_name);

if ($patient_stmt->fetch()) {
    $patient_id = $saved_patient_id;

    if (!empty($saved_full_name)) {
        $individual_name = $saved_full_name;
    }
}

$patient_stmt->close();

if ($patient_id <= 0) {
    header("Location: patient-onboarding.php");
    exit;
}

if (is_post_request() && isset($_POST["add_medication"])) {
    $medication_name = "";
    $dosage = "";
    $frequency = "";
    $start_date = "";
    $end_date = "";

    if (isset($_POST["medication_name"])) {
        $medication_name = clean_input($_POST["medication_name"]);
    }

    if (isset($_POST["dosage"])) {
        $dosage = clean_input($_POST["dosage"]);
    }

    if (isset($_POST["frequency"])) {
        $frequency = clean_input($_POST["frequency"]);
    }

    if (isset($_POST["start_date"])) {
        $start_date = clean_input($_POST["start_date"]);
    }

    if (isset($_POST["end_date"])) {
        $end_date = clean_input($_POST["end_date"]);
    }

    if (empty($medication_name)) {
        $error = "Enter the medication name.";
    }

    if (empty($frequency)) {
        $error = "Enter how often the medication should be taken.";
    }

    if (empty($error)) {
        $insert_stmt = $conn->prepare("INSERT INTO medications (patient_id, medication_name, dosage, frequency, start_date, end_date, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
        $insert_stmt->bind_param("isssss", $patient_id, $medication_name, $dosage, $frequency, $start_date, $end_date);

        if ($insert_stmt->execute()) {
            $message = "Medication reminder added successfully.";
        } else {
            $error = "Medication reminder could not be added. Please try again.";
        }

        $insert_stmt->close();
    }
}

if (is_post_request() && isset($_POST["update_medication_status"])) {
    $medication_id = 0;
    $new_status = "";

    if (isset($_POST["medication_id"])) {
        $medication_id = (int) $_POST["medication_id"];
    }

    if (isset($_POST["new_status"])) {
        $new_status = clean_input($_POST["new_status"]);
    }

    if ($new_status != "completed" && $new_status != "stopped" && $new_status != "active") {
        $error = "Select a valid medication status.";
    }

    if ($medication_id <= 0) {
        $error = "Select a valid medication.";
    }

    if (empty($error)) {
        $update_stmt = $conn->prepare("UPDATE medications SET status = ?, updated_at = NOW() WHERE id = ? AND patient_id = ?");
        $update_stmt->bind_param("sii", $new_status, $medication_id, $patient_id);

        if ($update_stmt->execute()) {
            $message = "Medication status updated.";
        } else {
            $error = "Medication status could not be updated.";
        }

        $update_stmt->close();
    }
}

$medication_stmt = $conn->prepare("SELECT id, medication_name, dosage, frequency, start_date, end_date, status FROM medications WHERE patient_id = ? ORDER BY status ASC, created_at DESC");
$medication_stmt->bind_param("i", $patient_id);
$medication_stmt->execute();
$medication_stmt->bind_result($medication_id, $medication_name, $dosage, $frequency, $start_date, $end_date, $status);

while ($medication_stmt->fetch()) {
    $medications[] = array(
        "id" => $medication_id,
        "medication_name" => $medication_name,
        "dosage" => $dosage,
        "frequency" => $frequency,
        "start_date" => $start_date,
        "end_date" => $end_date,
        "status" => $status,
    );
}

$medication_stmt->close();

function medication_value($value)
{
    if (!empty($value)) {
        return htmlspecialchars($value);
    }

    return "Not provided";
}

function medication_status_text($value)
{
    if ($value == "completed") {
        return "Completed";
    }

    if ($value == "stopped") {
        return "Stopped";
    }

    return "Active";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medications | Medic</title>
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
                <a href="individual-medical-records.php" class="secondary-action">Medical Records</a>
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
                <span class="eyebrow">Medication reminders</span>
                <h1>Track medication for <?php echo htmlspecialchars($individual_name); ?>.</h1>
                <p>Add medications, dosage, frequency, and end date. This is the first version before we add full notification reminders.</p>

                <?php
                if (!empty($message)) {
                    echo "<p class=\"form-alert success-alert\">" . htmlspecialchars($message) . "</p>";
                }

                if (!empty($error)) {
                    echo "<p class=\"form-alert lookup-alert\">" . htmlspecialchars($error) . "</p>";
                }
                ?>
            </div>
        </section>

        <section class="profile-grid recent-records-grid">
            <article class="profile-card recent-records-card">
                <div class="profile-card-heading">
                    <span>Add reminder</span>
                    <h2>Medication details</h2>
                </div>

                <form action="" method="post" class="record-form">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="medicationName">Medication name</label>
                            <input type="text" class="form-control" id="medicationName" name="medication_name" placeholder="Example: Amoxicillin" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="dosage">Dosage</label>
                            <input type="text" class="form-control" id="dosage" name="dosage" placeholder="Example: 500mg">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="frequency">Frequency</label>
                            <input type="text" class="form-control" id="frequency" name="frequency" placeholder="Example: Twice daily" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label" for="startDate">Start date</label>
                            <input type="date" class="form-control" id="startDate" name="start_date">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label" for="endDate">End date</label>
                            <input type="date" class="form-control" id="endDate" name="end_date">
                        </div>

                        <div class="col-12">
                            <button type="submit" name="add_medication" class="primary-action">Add medication</button>
                        </div>
                    </div>
                </form>
            </article>
        </section>

        <section class="profile-grid recent-records-grid">
            <article class="profile-card recent-records-card">
                <div class="profile-card-heading">
                    <span>Medication list</span>
                    <h2>Your current and past medications</h2>
                </div>

                <?php if (empty($medications)) { ?>
                    <p class="helper-text">No medications have been added yet.</p>
                <?php } else { ?>
                    <div class="record-timeline">
                        <?php foreach ($medications as $medication) { ?>
                            <article class="record-item">
                                <div class="record-item-header">
                                    <div>
                                        <span><?php echo medication_status_text($medication["status"]); ?></span>
                                        <h3><?php echo medication_value($medication["medication_name"]); ?></h3>
                                    </div>
                                    <strong><?php echo medication_value($medication["frequency"]); ?></strong>
                                </div>

                                <div class="record-status-row">
                                    <span>Dosage: <?php echo medication_value($medication["dosage"]); ?></span>
                                    <span>Start: <?php echo medication_value($medication["start_date"]); ?></span>
                                    <span>End: <?php echo medication_value($medication["end_date"]); ?></span>
                                </div>

                                <form action="" method="post" class="record-update-form appointment-action-row">
                                    <input type="hidden" name="medication_id" value="<?php echo htmlspecialchars($medication["id"]); ?>">
                                    <button type="submit" name="update_medication_status" value="1" class="secondary-action" onclick="this.form.new_status.value='active';">Mark active</button>
                                    <button type="submit" name="update_medication_status" value="1" class="secondary-action" onclick="this.form.new_status.value='completed';">Mark completed</button>
                                    <button type="submit" name="update_medication_status" value="1" class="secondary-action" onclick="this.form.new_status.value='stopped';">Mark stopped</button>
                                    <input type="hidden" name="new_status" value="">
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
