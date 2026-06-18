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
$organizations = array();
$appointments = array();

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

if (is_post_request() && isset($_POST["cancel_appointment"])) {
    $appointment_id = 0;

    if (isset($_POST["appointment_id"])) {
        $appointment_id = (int) $_POST["appointment_id"];
    }

    if ($appointment_id <= 0) {
        $error = "Select a valid appointment to cancel.";
    }

    if (empty($error)) {
        $cancel_stmt = $conn->prepare("UPDATE appointments SET status = 'cancelled', updated_at = NOW() WHERE id = ? AND patient_id = ? AND status IN ('pending', 'approved')");
        $cancel_stmt->bind_param("ii", $appointment_id, $patient_id);

        if ($cancel_stmt->execute()) {
            if ($cancel_stmt->affected_rows > 0) {
                $message = "Appointment cancelled successfully.";
            } else {
                $error = "Only pending or approved appointments can be cancelled.";
            }
        } else {
            $error = "Appointment could not be cancelled. Please try again.";
        }

        $cancel_stmt->close();
    }
}

if (is_post_request() && isset($_POST["book_appointment"])) {
    $hospital_id = 0;
    $service_area = "";
    $appointment_date = "";
    $appointment_time = "";
    $reason = "";

    if (isset($_POST["hospital_id"])) {
        $hospital_id = (int) $_POST["hospital_id"];
    }

    if (isset($_POST["service_area"])) {
        $service_area = clean_input($_POST["service_area"]);
    }

    if (isset($_POST["appointment_date"])) {
        $appointment_date = clean_input($_POST["appointment_date"]);
    }

    if (isset($_POST["appointment_time"])) {
        $appointment_time = clean_input($_POST["appointment_time"]);
    }

    if (isset($_POST["reason"])) {
        $reason = clean_input($_POST["reason"]);
    }

    if ($hospital_id <= 0) {
        $error = "Select a hospital, clinic, or medical firm.";
    }

    if (empty($service_area)) {
        $error = "Select the service or department you need.";
    }

    if (empty($appointment_date)) {
        $error = "Select an appointment date.";
    }

    if (!empty($appointment_date)) {
        if (strtotime($appointment_date) < strtotime(date("Y-m-d"))) {
            $error = "Appointment date cannot be in the past.";
        }
    }

    if (empty($reason)) {
        $error = "Enter your reason for the appointment.";
    }

    if (empty($error)) {
        $org_check_stmt = $conn->prepare("SELECT hospitals.id FROM hospitals INNER JOIN users ON hospitals.user_id = users.id WHERE hospitals.id = ? AND users.status = 'active' AND hospitals.onboarding_completed = 1 LIMIT 1");
        $org_check_stmt->bind_param("i", $hospital_id);
        $org_check_stmt->execute();
        $org_check_stmt->store_result();

        if ($org_check_stmt->num_rows != 1) {
            $error = "Select an approved hospital, clinic, or medical firm.";
        }

        $org_check_stmt->close();
    }

    if (empty($error)) {
        $insert_stmt = $conn->prepare("INSERT INTO appointments (patient_id, hospital_id, service_area, appointment_date, appointment_time, reason, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
        $insert_stmt->bind_param("iissss", $patient_id, $hospital_id, $service_area, $appointment_date, $appointment_time, $reason);

        if ($insert_stmt->execute()) {
            $message = "Appointment request sent successfully.";
        } else {
            $error = "Appointment request could not be sent. Please try again.";
        }

        $insert_stmt->close();
    }
}

$organization_stmt = $conn->prepare("SELECT hospitals.id, hospitals.hospital_name, hospitals.organization_type, hospitals.state, hospitals.lga FROM hospitals INNER JOIN users ON hospitals.user_id = users.id WHERE users.status = 'active' AND hospitals.onboarding_completed = 1 ORDER BY hospitals.hospital_name ASC");
$organization_stmt->execute();
$organization_stmt->bind_result($org_id, $hospital_name, $organization_type, $state, $lga);

while ($organization_stmt->fetch()) {
    $organizations[] = array(
        "id" => $org_id,
        "hospital_name" => $hospital_name,
        "organization_type" => $organization_type,
        "state" => $state,
        "lga" => $lga,
    );
}

$organization_stmt->close();

$appointment_stmt = $conn->prepare("SELECT appointments.id, appointments.service_area, appointments.appointment_date, appointments.appointment_time, appointments.reason, appointments.status, hospitals.hospital_name FROM appointments INNER JOIN hospitals ON appointments.hospital_id = hospitals.id WHERE appointments.patient_id = ? ORDER BY appointments.appointment_date DESC, appointments.created_at DESC");
$appointment_stmt->bind_param("i", $patient_id);
$appointment_stmt->execute();
$appointment_stmt->bind_result($appointment_id, $service_area, $appointment_date, $appointment_time, $reason, $status, $hospital_name);

while ($appointment_stmt->fetch()) {
    $appointments[] = array(
        "id" => $appointment_id,
        "service_area" => $service_area,
        "appointment_date" => $appointment_date,
        "appointment_time" => $appointment_time,
        "reason" => $reason,
        "status" => $status,
        "hospital_name" => $hospital_name,
    );
}

$appointment_stmt->close();

function show_appointment_value($value)
{
    if (!empty($value)) {
        return htmlspecialchars($value);
    }

    return "Not provided";
}

function show_appointment_status($value)
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
    <title>Appointments | Medic</title>
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
                <span>AP</span>
            </div>

            <div>
                <span class="eyebrow">Appointments</span>
                <h1>Book a consultation, <?php echo htmlspecialchars($individual_name); ?>.</h1>
                <p>Request a visit with an approved hospital, clinic, or medical firm. When the visit happens, the organization can turn it into a medical record.</p>

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
                    <span>Request appointment</span>
                    <h2>Select an approved organization</h2>
                </div>

                <form action="" method="post" class="record-form">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="hospitalId">Hospital / clinic / medical firm</label>
                            <select class="form-select" id="hospitalId" name="hospital_id" required>
                                <option value="">Select organization</option>
                                <?php foreach ($organizations as $organization) { ?>
                                    <option value="<?php echo htmlspecialchars($organization["id"]); ?>">
                                        <?php echo htmlspecialchars($organization["hospital_name"] . " - " . ucwords(str_replace("_", " ", $organization["organization_type"])) . " - " . $organization["state"] . " / " . $organization["lga"]); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="serviceArea">Service / department</label>
                            <select class="form-select" id="serviceArea" name="service_area" required>
                                <option value="">Select service</option>
                                <option value="General consultation">General consultation</option>
                                <option value="Vitals check">Vitals check</option>
                                <option value="Laboratory review">Laboratory review</option>
                                <option value="Medication follow-up">Medication follow-up</option>
                                <option value="Emergency follow-up">Emergency follow-up</option>
                                <option value="Specialist consultation">Specialist consultation</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="appointmentDate">Appointment date</label>
                            <input type="date" class="form-control" id="appointmentDate" name="appointment_date" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="appointmentTime">Preferred time</label>
                            <input type="time" class="form-control" id="appointmentTime" name="appointment_time">
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="reason">Reason for appointment</label>
                            <textarea class="form-control" id="reason" name="reason" rows="4" placeholder="Briefly describe what you want checked" required></textarea>
                        </div>

                        <div class="col-12">
                            <button type="submit" name="book_appointment" class="primary-action">Send appointment request</button>
                        </div>
                    </div>
                </form>
            </article>
        </section>

        <section class="profile-grid recent-records-grid">
            <article class="profile-card recent-records-card">
                <div class="profile-card-heading">
                    <span>Appointment history</span>
                    <h2>Your appointment requests</h2>
                </div>

                <?php if (empty($appointments)) { ?>
                    <p class="helper-text">You have not requested any appointments yet.</p>
                <?php } else { ?>
                    <div class="record-timeline">
                        <?php foreach ($appointments as $appointment) { ?>
                            <article class="record-item">
                                <div class="record-item-header">
                                    <div>
                                        <span><?php echo show_appointment_status($appointment["status"]); ?></span>
                                        <h3><?php echo show_appointment_value($appointment["hospital_name"]); ?></h3>
                                    </div>
                                    <strong><?php echo show_appointment_value($appointment["appointment_date"]); ?></strong>
                                </div>

                                <div class="record-status-row">
                                    <span>Service: <?php echo show_appointment_value($appointment["service_area"]); ?></span>
                                    <span>Time: <?php echo show_appointment_value($appointment["appointment_time"]); ?></span>
                                    <span>Status: <?php echo show_appointment_status($appointment["status"]); ?></span>
                                </div>

                                <p class="helper-text"><?php echo show_appointment_value($appointment["reason"]); ?></p>

                                <?php if ($appointment["status"] == "pending" || $appointment["status"] == "approved") { ?>
                                    <form action="" method="post" class="record-update-form">
                                        <input type="hidden" name="appointment_id" value="<?php echo htmlspecialchars($appointment["id"]); ?>">
                                        <button type="submit" name="cancel_appointment" class="secondary-action">Cancel appointment</button>
                                    </form>
                                <?php } ?>
                            </article>
                        <?php } ?>
                    </div>
                <?php } ?>
            </article>
        </section>
    </main>
</body>
</html>
