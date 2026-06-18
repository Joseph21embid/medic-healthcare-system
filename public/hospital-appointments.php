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
$message = "";
$error = "";
$status_filter = "all";
$appointments = array();

if (isset($_GET["status"])) {
    $status_filter = clean_input($_GET["status"]);
}

if ($status_filter != "all" && $status_filter != "pending" && $status_filter != "approved" && $status_filter != "rejected" && $status_filter != "completed" && $status_filter != "cancelled") {
    $status_filter = "all";
}

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

if (is_post_request() && isset($_POST["update_appointment_status"])) {
    $appointment_id = 0;
    $new_status = "";
    $appointment_date = "";
    $appointment_time = "";

    if (isset($_POST["appointment_id"])) {
        $appointment_id = (int) $_POST["appointment_id"];
    }

    if (isset($_POST["new_status"])) {
        $new_status = clean_input($_POST["new_status"]);
    }

    if (isset($_POST["appointment_date"])) {
        $appointment_date = clean_input($_POST["appointment_date"]);
    }

    if (isset($_POST["appointment_time"])) {
        $appointment_time = clean_input($_POST["appointment_time"]);
    }

    if ($appointment_id <= 0) {
        $error = "Select a valid appointment.";
    }

    if ($new_status != "approved" && $new_status != "rejected" && $new_status != "cancelled") {
        $error = "Select a valid appointment action.";
    }

    if (empty($error)) {
        $check_stmt = $conn->prepare("SELECT id FROM appointments WHERE id = ? AND hospital_id = ? LIMIT 1");
        $check_stmt->bind_param("ii", $appointment_id, $organization_id);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows != 1) {
            $error = "This appointment does not belong to your organization.";
        }

        $check_stmt->close();
    }

    if (empty($error)) {
        if (!empty($appointment_date)) {
            $update_stmt = $conn->prepare("UPDATE appointments SET status = ?, appointment_date = ?, appointment_time = ?, updated_at = NOW() WHERE id = ? AND hospital_id = ?");
            $update_stmt->bind_param("sssii", $new_status, $appointment_date, $appointment_time, $appointment_id, $organization_id);
        } else {
            $update_stmt = $conn->prepare("UPDATE appointments SET status = ?, updated_at = NOW() WHERE id = ? AND hospital_id = ?");
            $update_stmt->bind_param("sii", $new_status, $appointment_id, $organization_id);
        }

        if ($update_stmt->execute()) {
            $message = "Appointment updated successfully.";
        } else {
            $error = "Appointment could not be updated. Please try again.";
        }

        $update_stmt->close();
    }
}

if (is_post_request() && isset($_POST["start_appointment_visit"])) {
    $appointment_id = 0;
    $patient_id = 0;
    $service_area = "";
    $reason = "";
    $appointment_date = date("Y-m-d");

    if (isset($_POST["appointment_id"])) {
        $appointment_id = (int) $_POST["appointment_id"];
    }

    if ($appointment_id <= 0) {
        $error = "Select a valid appointment before starting a visit.";
    }

    if (empty($error)) {
        $appointment_stmt = $conn->prepare("SELECT patient_id, service_area, appointment_date, reason FROM appointments WHERE id = ? AND hospital_id = ? AND status = 'approved' LIMIT 1");
        $appointment_stmt->bind_param("ii", $appointment_id, $organization_id);
        $appointment_stmt->execute();
        $appointment_stmt->bind_result($saved_patient_id, $saved_service_area, $saved_appointment_date, $saved_reason);

        if ($appointment_stmt->fetch()) {
            $patient_id = $saved_patient_id;
            $service_area = $saved_service_area;
            $appointment_date = $saved_appointment_date;
            $reason = $saved_reason;
        } else {
            $error = "Only approved appointments can be converted into a visit record.";
        }

        $appointment_stmt->close();
    }

    if (empty($error)) {
        $duplicate_stmt = $conn->prepare("SELECT id FROM medical_records WHERE appointment_id = ? AND hospital_id = ? LIMIT 1");
        $duplicate_stmt->bind_param("ii", $appointment_id, $organization_id);
        $duplicate_stmt->execute();
        $duplicate_stmt->store_result();

        if ($duplicate_stmt->num_rows > 0) {
            $error = "This appointment has already been converted into a medical record.";
        }

        $duplicate_stmt->close();
    }

    if (empty($error)) {
        $possible_illness = "To be assessed during consultation";
        $result_status = "awaiting_result";
        $medication_status = "not_started";
        $diagnosis = "";
        $treatment_notes = "";
        $prescription_notes = "";
        $doctor_name = "";
        $result_summary = "";
        $heart_rate = "";
        $blood_pressure = "";
        $temperature = "";
        $weight = "";
        $visit_type = "appointment";
        $symptoms = $reason;

        if (!empty($service_area)) {
            $symptoms = "Service: " . $service_area . "\nReason: " . $reason;
        }

        $insert_stmt = $conn->prepare("INSERT INTO medical_records (patient_id, hospital_id, appointment_id, visit_type, heart_rate, blood_pressure, temperature, weight, symptoms, possible_illness, result_status, result_summary, doctor_name, diagnosis, treatment_notes, prescription_notes, medication_status, visit_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insert_stmt->bind_param("iiisssssssssssssss", $patient_id, $organization_id, $appointment_id, $visit_type, $heart_rate, $blood_pressure, $temperature, $weight, $symptoms, $possible_illness, $result_status, $result_summary, $doctor_name, $diagnosis, $treatment_notes, $prescription_notes, $medication_status, $appointment_date);

        if ($insert_stmt->execute()) {
            $complete_stmt = $conn->prepare("UPDATE appointments SET status = 'completed', updated_at = NOW() WHERE id = ? AND hospital_id = ?");
            $complete_stmt->bind_param("ii", $appointment_id, $organization_id);
            $complete_stmt->execute();
            $complete_stmt->close();

            $message = "Appointment converted into a medical record. Continue the clinical update on the Medical Records page.";
        } else {
            $error = "Visit record could not be created from this appointment.";
        }

        $insert_stmt->close();
    }
}

if ($status_filter == "all") {
    $appointment_stmt = $conn->prepare("SELECT appointments.id, appointments.service_area, appointments.appointment_date, appointments.appointment_time, appointments.reason, appointments.status, patients.full_name, patients.nhis_number, patients.phone FROM appointments INNER JOIN patients ON appointments.patient_id = patients.id WHERE appointments.hospital_id = ? ORDER BY appointments.appointment_date ASC, appointments.created_at DESC");
    $appointment_stmt->bind_param("i", $organization_id);
} else {
    $appointment_stmt = $conn->prepare("SELECT appointments.id, appointments.service_area, appointments.appointment_date, appointments.appointment_time, appointments.reason, appointments.status, patients.full_name, patients.nhis_number, patients.phone FROM appointments INNER JOIN patients ON appointments.patient_id = patients.id WHERE appointments.hospital_id = ? AND appointments.status = ? ORDER BY appointments.appointment_date ASC, appointments.created_at DESC");
    $appointment_stmt->bind_param("is", $organization_id, $status_filter);
}

$appointment_stmt->execute();
$appointment_stmt->bind_result($appointment_id, $service_area, $appointment_date, $appointment_time, $reason, $status, $full_name, $nhis_number, $phone);

while ($appointment_stmt->fetch()) {
    $appointments[] = array(
        "id" => $appointment_id,
        "service_area" => $service_area,
        "appointment_date" => $appointment_date,
        "appointment_time" => $appointment_time,
        "reason" => $reason,
        "status" => $status,
        "full_name" => $full_name,
        "nhis_number" => $nhis_number,
        "phone" => $phone,
    );
}

$appointment_stmt->close();

function appointment_value($value)
{
    if (!empty($value)) {
        return htmlspecialchars($value);
    }

    return "Not provided";
}

function appointment_form_value($value)
{
    if (!empty($value)) {
        return htmlspecialchars($value);
    }

    return "";
}

function appointment_status($value)
{
    if ($value == "approved") {
        return "Approved";
    }

    if ($value == "rejected") {
        return "Rejected";
    }

    if ($value == "completed") {
        return "Completed";
    }

    if ($value == "cancelled") {
        return "Cancelled";
    }

    return "Pending";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organization Appointments | Medic</title>
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
                <a href="hospital-medical-records.php" class="secondary-action">Medical Records</a>
                <form action="../auth/logout.php" method="post">
                    <button type="submit" class="primary-action">Logout</button>
                </form>
            </div>
        </header>

        <section class="profile-hero-card lookup-hero">
            <div class="profile-avatar-large">
                <span>AP</span>
            </div>

            <div>
                <span class="eyebrow">Appointment queue</span>
                <h1>Manage appointment requests for <?php echo htmlspecialchars($organization_name); ?>.</h1>
                <p>Approve, reject, reschedule, or start a visit from an approved appointment. Starting a visit creates a medical record for the individual.</p>

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
                    <span>Requests</span>
                    <h2>Appointments assigned to your organization</h2>
                </div>

                <div class="record-status-row filter-row">
                    <a href="hospital-appointments.php?status=all">All</a>
                    <a href="hospital-appointments.php?status=pending">Pending</a>
                    <a href="hospital-appointments.php?status=approved">Approved</a>
                    <a href="hospital-appointments.php?status=completed">Completed</a>
                    <a href="hospital-appointments.php?status=rejected">Rejected</a>
                    <a href="hospital-appointments.php?status=cancelled">Cancelled</a>
                </div>

                <?php if (empty($appointments)) { ?>
                    <p class="helper-text">No appointment requests have been sent to your organization yet.</p>
                <?php } else { ?>
                    <div class="record-timeline">
                        <?php foreach ($appointments as $appointment) { ?>
                            <article class="record-item">
                                <div class="record-item-header">
                                    <div>
                                        <span><?php echo appointment_status($appointment["status"]); ?></span>
                                        <h3><?php echo appointment_value($appointment["full_name"]); ?></h3>
                                    </div>
                                    <strong><?php echo appointment_value($appointment["appointment_date"]); ?></strong>
                                </div>

                                <div class="record-status-row">
                                    <span>NHIS: <?php echo appointment_value($appointment["nhis_number"]); ?></span>
                                    <span>Service: <?php echo appointment_value($appointment["service_area"]); ?></span>
                                    <span>Phone: <?php echo appointment_value($appointment["phone"]); ?></span>
                                    <span>Time: <?php echo appointment_value($appointment["appointment_time"]); ?></span>
                                </div>

                                <p class="helper-text"><?php echo appointment_value($appointment["reason"]); ?></p>

                                <form action="" method="post" class="record-update-form">
                                    <input type="hidden" name="appointment_id" value="<?php echo htmlspecialchars($appointment["id"]); ?>">

                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label" for="newStatus<?php echo htmlspecialchars($appointment["id"]); ?>">Action</label>
                                            <select class="form-select" id="newStatus<?php echo htmlspecialchars($appointment["id"]); ?>" name="new_status">
                                                <option value="approved">Approve</option>
                                                <option value="rejected">Reject</option>
                                                <option value="cancelled">Cancel</option>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label" for="appointmentDate<?php echo htmlspecialchars($appointment["id"]); ?>">Date</label>
                                            <input type="date" class="form-control" id="appointmentDate<?php echo htmlspecialchars($appointment["id"]); ?>" name="appointment_date" value="<?php echo appointment_form_value($appointment["appointment_date"]); ?>">
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label" for="appointmentTime<?php echo htmlspecialchars($appointment["id"]); ?>">Time</label>
                                            <input type="time" class="form-control" id="appointmentTime<?php echo htmlspecialchars($appointment["id"]); ?>" name="appointment_time" value="<?php echo appointment_form_value($appointment["appointment_time"]); ?>">
                                        </div>

                                        <div class="col-12 appointment-action-row">
                                            <button type="submit" name="update_appointment_status" class="primary-action">Update appointment</button>

                                            <?php if ($appointment["status"] == "approved") { ?>
                                                <button type="submit" name="start_appointment_visit" class="secondary-action">Start visit record</button>
                                            <?php } ?>
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
