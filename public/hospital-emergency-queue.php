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
$status_filter = "active";
$emergencies = array();

if (isset($_GET["status"])) {
    $status_filter = clean_input($_GET["status"]);
}

if ($status_filter != "active" && $status_filter != "pending" && $status_filter != "responding" && $status_filter != "resolved" && $status_filter != "cancelled" && $status_filter != "all") {
    $status_filter = "active";
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

if (is_post_request() && isset($_POST["update_emergency"])) {
    $emergency_id = 0;
    $action = "";

    if (isset($_POST["emergency_id"])) {
        $emergency_id = (int) $_POST["emergency_id"];
    }

    if (isset($_POST["action"])) {
        $action = clean_input($_POST["action"]);
    }

    if ($emergency_id <= 0) {
        $error = "Select a valid SOS request.";
    }

    if ($action != "request_photo" && $action != "accept" && $action != "dispatch" && $action != "resolve" && $action != "cancel") {
        $error = "Select a valid emergency action.";
    }

    if (empty($error)) {
        $check_stmt = $conn->prepare("SELECT id FROM emergency_requests WHERE id = ? AND hospital_id = ? LIMIT 1");
        $check_stmt->bind_param("ii", $emergency_id, $organization_id);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows != 1) {
            $error = "This SOS request does not belong to your organization.";
        }

        $check_stmt->close();
    }

    if (empty($error)) {
        if ($action == "request_photo") {
            $update_stmt = $conn->prepare("UPDATE emergency_requests SET evidence_requested = 1, updated_at = NOW() WHERE id = ? AND hospital_id = ?");
            $update_stmt->bind_param("ii", $emergency_id, $organization_id);
            $message = "Scene photo requested from the reporter.";
        } elseif ($action == "accept") {
            $update_stmt = $conn->prepare("UPDATE emergency_requests SET status = 'accepted', updated_at = NOW() WHERE id = ? AND hospital_id = ?");
            $update_stmt->bind_param("ii", $emergency_id, $organization_id);
            $message = "SOS request accepted.";
        } elseif ($action == "dispatch") {
            $update_stmt = $conn->prepare("UPDATE emergency_requests SET status = 'responding', evidence_requested = 0, updated_at = NOW() WHERE id = ? AND hospital_id = ?");
            $update_stmt->bind_param("ii", $emergency_id, $organization_id);
            $message = "Ambulance or care unit marked as responding.";
        } elseif ($action == "resolve") {
            $update_stmt = $conn->prepare("UPDATE emergency_requests SET status = 'resolved', evidence_requested = 0, updated_at = NOW() WHERE id = ? AND hospital_id = ?");
            $update_stmt->bind_param("ii", $emergency_id, $organization_id);
            $message = "SOS request resolved.";
        } else {
            $update_stmt = $conn->prepare("UPDATE emergency_requests SET status = 'cancelled', evidence_requested = 0, updated_at = NOW() WHERE id = ? AND hospital_id = ?");
            $update_stmt->bind_param("ii", $emergency_id, $organization_id);
            $message = "SOS request cancelled.";
        }

        if (!$update_stmt->execute()) {
            $error = "SOS request could not be updated.";
            $message = "";
        }

        $update_stmt->close();
    }
}

if ($status_filter == "active") {
    $emergency_stmt = $conn->prepare("SELECT emergency_requests.id, emergency_requests.request_type, emergency_requests.victim_name, emergency_requests.victim_phone, emergency_requests.location_address, emergency_requests.emergency_note, emergency_requests.reporter_note, emergency_requests.scene_photo, emergency_requests.evidence_requested, emergency_requests.status, emergency_requests.created_at, patients.full_name, patients.nhis_number, patients.phone FROM emergency_requests INNER JOIN patients ON emergency_requests.patient_id = patients.id WHERE emergency_requests.hospital_id = ? AND emergency_requests.status IN ('pending', 'accepted', 'responding') ORDER BY emergency_requests.created_at DESC");
    $emergency_stmt->bind_param("i", $organization_id);
} elseif ($status_filter == "all") {
    $emergency_stmt = $conn->prepare("SELECT emergency_requests.id, emergency_requests.request_type, emergency_requests.victim_name, emergency_requests.victim_phone, emergency_requests.location_address, emergency_requests.emergency_note, emergency_requests.reporter_note, emergency_requests.scene_photo, emergency_requests.evidence_requested, emergency_requests.status, emergency_requests.created_at, patients.full_name, patients.nhis_number, patients.phone FROM emergency_requests INNER JOIN patients ON emergency_requests.patient_id = patients.id WHERE emergency_requests.hospital_id = ? ORDER BY emergency_requests.created_at DESC");
    $emergency_stmt->bind_param("i", $organization_id);
} else {
    $emergency_stmt = $conn->prepare("SELECT emergency_requests.id, emergency_requests.request_type, emergency_requests.victim_name, emergency_requests.victim_phone, emergency_requests.location_address, emergency_requests.emergency_note, emergency_requests.reporter_note, emergency_requests.scene_photo, emergency_requests.evidence_requested, emergency_requests.status, emergency_requests.created_at, patients.full_name, patients.nhis_number, patients.phone FROM emergency_requests INNER JOIN patients ON emergency_requests.patient_id = patients.id WHERE emergency_requests.hospital_id = ? AND emergency_requests.status = ? ORDER BY emergency_requests.created_at DESC");
    $emergency_stmt->bind_param("is", $organization_id, $status_filter);
}

$emergency_stmt->execute();
$emergency_stmt->bind_result($emergency_id, $request_type, $victim_name, $victim_phone, $location_address, $emergency_note, $reporter_note, $scene_photo, $evidence_requested, $status, $created_at, $reporter_name, $reporter_nhis, $reporter_phone);

while ($emergency_stmt->fetch()) {
    $emergencies[] = array(
        "id" => $emergency_id,
        "request_type" => $request_type,
        "victim_name" => $victim_name,
        "victim_phone" => $victim_phone,
        "location_address" => $location_address,
        "emergency_note" => $emergency_note,
        "reporter_note" => $reporter_note,
        "scene_photo" => $scene_photo,
        "evidence_requested" => $evidence_requested,
        "status" => $status,
        "created_at" => $created_at,
        "reporter_name" => $reporter_name,
        "reporter_nhis" => $reporter_nhis,
        "reporter_phone" => $reporter_phone,
    );
}

$emergency_stmt->close();

function sos_value($value)
{
    if (!empty($value)) {
        return htmlspecialchars($value);
    }

    return "Not provided";
}

function sos_status($value)
{
    if ($value == "accepted") {
        return "Accepted";
    }

    if ($value == "responding") {
        return "Responding";
    }

    if ($value == "resolved") {
        return "Resolved";
    }

    if ($value == "cancelled") {
        return "Cancelled";
    }

    return "Pending review";
}

function sos_type($value)
{
    if ($value == "other_person") {
        return "Reported for another person";
    }

    return "Self emergency";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergency Queue | Medic</title>
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
                <a href="hospital-appointments.php" class="secondary-action">Appointments</a>
                <form action="../auth/logout.php" method="post">
                    <button type="submit" class="primary-action">Logout</button>
                </form>
            </div>
        </header>

        <section class="profile-hero-card sos-hero">
            <div class="profile-avatar-large">
                <span>ER</span>
            </div>

            <div>
                <span class="eyebrow">Emergency queue</span>
                <h1>Review SOS requests for <?php echo htmlspecialchars($organization_name); ?>.</h1>
                <p>Self emergencies are treated as immediate response. Third-party reports can be verified with scene photos before dispatching a care unit.</p>

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
                    <span>SOS requests</span>
                    <h2>Emergency response list</h2>
                </div>

                <div class="record-status-row filter-row">
                    <a href="hospital-emergency-queue.php?status=active">Active</a>
                    <a href="hospital-emergency-queue.php?status=pending">Pending</a>
                    <a href="hospital-emergency-queue.php?status=responding">Responding</a>
                    <a href="hospital-emergency-queue.php?status=resolved">Resolved</a>
                    <a href="hospital-emergency-queue.php?status=cancelled">Cancelled</a>
                    <a href="hospital-emergency-queue.php?status=all">All</a>
                </div>

                <?php if (empty($emergencies)) { ?>
                    <p class="helper-text">No SOS requests match this queue.</p>
                <?php } else { ?>
                    <div class="record-timeline">
                        <?php foreach ($emergencies as $emergency) { ?>
                            <article class="record-item">
                                <div class="record-item-header">
                                    <div>
                                        <span><?php echo sos_type($emergency["request_type"]); ?></span>
                                        <h3><?php echo sos_value($emergency["victim_name"]); ?></h3>
                                    </div>
                                    <strong><?php echo sos_status($emergency["status"]); ?></strong>
                                </div>

                                <div class="record-status-row">
                                    <span>Reporter: <?php echo sos_value($emergency["reporter_name"]); ?></span>
                                    <span>NHIS: <?php echo sos_value($emergency["reporter_nhis"]); ?></span>
                                    <span>Victim phone: <?php echo sos_value($emergency["victim_phone"]); ?></span>
                                    <span>Sent: <?php echo sos_value($emergency["created_at"]); ?></span>
                                </div>

                                <div class="profile-detail-list record-notes">
                                    <div>
                                        <span>Emergency location</span>
                                        <strong><?php echo sos_value($emergency["location_address"]); ?></strong>
                                    </div>
                                    <div>
                                        <span>Emergency note</span>
                                        <strong><?php echo sos_value($emergency["emergency_note"]); ?></strong>
                                    </div>
                                    <div>
                                        <span>Reporter note</span>
                                        <strong><?php echo sos_value($emergency["reporter_note"]); ?></strong>
                                    </div>
                                    <div>
                                        <span>Evidence status</span>
                                        <strong>
                                            <?php
                                            if ($emergency["evidence_requested"] == 1) {
                                                echo "Photo requested";
                                            } elseif (!empty($emergency["scene_photo"])) {
                                                echo "Photo uploaded";
                                            } else {
                                                echo "No photo yet";
                                            }
                                            ?>
                                        </strong>
                                    </div>
                                </div>

                                <?php if (!empty($emergency["scene_photo"])) { ?>
                                    <img src="<?php echo htmlspecialchars($emergency["scene_photo"]); ?>" alt="Emergency scene" class="scene-photo-preview">
                                <?php } ?>

                                <form action="" method="post" class="record-update-form appointment-action-row">
                                    <input type="hidden" name="emergency_id" value="<?php echo htmlspecialchars($emergency["id"]); ?>">
                                    <input type="hidden" name="update_emergency" value="1">

                                    <?php if ($emergency["request_type"] == "other_person") { ?>
                                        <button type="submit" name="action" value="request_photo" class="secondary-action">Request photo</button>
                                    <?php } ?>

                                    <button type="submit" name="action" value="accept" class="secondary-action">Accept</button>
                                    <button type="submit" name="action" value="dispatch" class="primary-action">Dispatch care unit</button>
                                    <button type="submit" name="action" value="resolve" class="secondary-action">Resolve</button>
                                    <button type="submit" name="action" value="cancel" class="secondary-action">Cancel</button>
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
