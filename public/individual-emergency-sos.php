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
$emergencies = array();
$upload_directory = __DIR__ . "/uploads/emergency_photos/";
$upload_public_path = "uploads/emergency_photos/";

if (!is_dir($upload_directory)) {
    mkdir($upload_directory, 0777, true);
}

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

function save_emergency_photo($field_name, $upload_directory, $upload_public_path, &$error)
{
    $saved_path = "";

    if (!isset($_FILES[$field_name])) {
        return $saved_path;
    }

    if ($_FILES[$field_name]["error"] == UPLOAD_ERR_NO_FILE) {
        return $saved_path;
    }

    if ($_FILES[$field_name]["error"] != UPLOAD_ERR_OK) {
        $error = "Photo upload failed. Please try again.";
        return $saved_path;
    }

    $allowed_types = array("image/jpeg", "image/png", "image/webp");
    $file_type = mime_content_type($_FILES[$field_name]["tmp_name"]);

    if (!in_array($file_type, $allowed_types)) {
        $error = "Upload a JPG, PNG, or WEBP photo.";
        return $saved_path;
    }

    if ($_FILES[$field_name]["size"] > 3000000) {
        $error = "Photo must not be larger than 3MB.";
        return $saved_path;
    }

    $extension = ".jpg";

    if ($file_type == "image/png") {
        $extension = ".png";
    } elseif ($file_type == "image/webp") {
        $extension = ".webp";
    }

    $file_name = "emergency_" . time() . "_" . random_int(1000, 9999) . $extension;
    $target_path = $upload_directory . $file_name;

    if (move_uploaded_file($_FILES[$field_name]["tmp_name"], $target_path)) {
        $saved_path = $upload_public_path . $file_name;
    } else {
        $error = "Photo could not be saved. Please try again.";
    }

    return $saved_path;
}

if (is_post_request() && isset($_POST["send_sos"])) {
    $hospital_id = 0;
    $request_type = "self";
    $victim_name = "";
    $victim_phone = "";
    $location_address = "";
    $emergency_note = "";
    $reporter_note = "";
    $status = "pending";
    $evidence_requested = 0;
    $scene_photo = "";

    if (isset($_POST["hospital_id"])) {
        $hospital_id = (int) $_POST["hospital_id"];
    }

    if (isset($_POST["request_type"])) {
        $request_type = clean_input($_POST["request_type"]);
    }

    if (isset($_POST["victim_name"])) {
        $victim_name = clean_input($_POST["victim_name"]);
    }

    if (isset($_POST["victim_phone"])) {
        $victim_phone = clean_input($_POST["victim_phone"]);
    }

    if (isset($_POST["location_address"])) {
        $location_address = clean_input($_POST["location_address"]);
    }

    if (isset($_POST["emergency_note"])) {
        $emergency_note = clean_input($_POST["emergency_note"]);
    }

    if (isset($_POST["reporter_note"])) {
        $reporter_note = clean_input($_POST["reporter_note"]);
    }

    if ($request_type != "self" && $request_type != "other_person") {
        $error = "Select a valid emergency type.";
    }

    if ($hospital_id <= 0) {
        $error = "Select the hospital, clinic, or medical firm to receive the SOS.";
    }

    if (empty($location_address)) {
        $error = "Enter the emergency location.";
    }

    if ($request_type == "other_person") {
        if (empty($victim_name)) {
            $error = "Enter the name or description of the person in emergency.";
        }

        $evidence_requested = 1;
    } else {
        $victim_name = $individual_name;
        $status = "responding";
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
        $scene_photo = save_emergency_photo("scene_photo", $upload_directory, $upload_public_path, $error);
    }

    if (empty($error)) {
        $insert_stmt = $conn->prepare("INSERT INTO emergency_requests (patient_id, hospital_id, request_type, victim_name, victim_phone, location_address, emergency_note, reporter_note, scene_photo, evidence_requested, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insert_stmt->bind_param("iisssssssis", $patient_id, $hospital_id, $request_type, $victim_name, $victim_phone, $location_address, $emergency_note, $reporter_note, $scene_photo, $evidence_requested, $status);

        if ($insert_stmt->execute()) {
            if ($request_type == "self") {
                $message = "Self SOS sent. Ambulance response has been marked as immediate for this first version.";
            } else {
                $message = "SOS report sent. The organization can review it and request/confirm scene photos before dispatch.";
            }
        } else {
            $error = "SOS request could not be sent. Please try again.";
        }

        $insert_stmt->close();
    }
}

if (is_post_request() && isset($_POST["upload_scene_photo"])) {
    $emergency_id = 0;
    $scene_photo = "";

    if (isset($_POST["emergency_id"])) {
        $emergency_id = (int) $_POST["emergency_id"];
    }

    if ($emergency_id <= 0) {
        $error = "Select a valid emergency request.";
    }

    if (empty($error)) {
        $scene_photo = save_emergency_photo("followup_scene_photo", $upload_directory, $upload_public_path, $error);
    }

    if (empty($error)) {
        if (empty($scene_photo)) {
            $error = "Select a photo before uploading.";
        }
    }

    if (empty($error)) {
        $photo_stmt = $conn->prepare("UPDATE emergency_requests SET scene_photo = ?, evidence_requested = 0, updated_at = NOW() WHERE id = ? AND patient_id = ?");
        $photo_stmt->bind_param("sii", $scene_photo, $emergency_id, $patient_id);

        if ($photo_stmt->execute()) {
            $message = "Scene photo uploaded successfully.";
        } else {
            $error = "Scene photo could not be uploaded.";
        }

        $photo_stmt->close();
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

$emergency_stmt = $conn->prepare("SELECT emergency_requests.id, emergency_requests.request_type, emergency_requests.victim_name, emergency_requests.location_address, emergency_requests.emergency_note, emergency_requests.scene_photo, emergency_requests.evidence_requested, emergency_requests.status, emergency_requests.created_at, hospitals.hospital_name FROM emergency_requests LEFT JOIN hospitals ON emergency_requests.hospital_id = hospitals.id WHERE emergency_requests.patient_id = ? ORDER BY emergency_requests.created_at DESC");
$emergency_stmt->bind_param("i", $patient_id);
$emergency_stmt->execute();
$emergency_stmt->bind_result($emergency_id, $request_type, $victim_name, $location_address, $emergency_note, $scene_photo, $evidence_requested, $status, $created_at, $hospital_name);

while ($emergency_stmt->fetch()) {
    $emergencies[] = array(
        "id" => $emergency_id,
        "request_type" => $request_type,
        "victim_name" => $victim_name,
        "location_address" => $location_address,
        "emergency_note" => $emergency_note,
        "scene_photo" => $scene_photo,
        "evidence_requested" => $evidence_requested,
        "status" => $status,
        "created_at" => $created_at,
        "hospital_name" => $hospital_name,
    );
}

$emergency_stmt->close();

function emergency_value($value)
{
    if (!empty($value)) {
        return htmlspecialchars($value);
    }

    return "Not provided";
}

function emergency_status($value)
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

function emergency_type($value)
{
    if ($value == "other_person") {
        return "For another person";
    }

    return "Self emergency";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergency SOS | Medic</title>
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
                <form action="../auth/logout.php" method="post">
                    <button type="submit" class="primary-action">Logout</button>
                </form>
            </div>
        </header>

        <section class="profile-hero-card sos-hero">
            <div class="profile-avatar-large">
                <span>SOS</span>
            </div>

            <div>
                <span class="eyebrow">Emergency SOS</span>
                <h1>Send urgent help from your Medic account.</h1>
                <p>
                    Choose self emergency if you are logged in on the account of the person in emergency. Use another person if you are reporting for someone who cannot access their phone.
                </p>

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
                    <span>Send SOS</span>
                    <h2>Emergency request details</h2>
                </div>

                <form action="" method="post" enctype="multipart/form-data" class="record-form">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="requestType">Emergency type</label>
                            <select class="form-select" id="requestType" name="request_type" required>
                                <option value="self">Self emergency (if you are logged in on the account of the person in emergency)</option>
                                <option value="other_person">Emergency for another person</option>
                            </select>
                        </div>

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

                        <div class="col-md-6">
                            <label class="form-label" for="victimName">Person in emergency</label>
                            <input type="text" class="form-control" id="victimName" name="victim_name" placeholder="Required if reporting for another person">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="victimPhone">Person's phone</label>
                            <input type="text" class="form-control" id="victimPhone" name="victim_phone" placeholder="Optional">
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="locationAddress">Emergency location</label>
                            <textarea class="form-control" id="locationAddress" name="location_address" rows="3" placeholder="Enter address, landmark, street, or area" required></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="emergencyNote">Emergency note</label>
                            <textarea class="form-control" id="emergencyNote" name="emergency_note" rows="3" placeholder="What happened? What condition is the person in?"></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="reporterNote">Reporter note</label>
                            <textarea class="form-control" id="reporterNote" name="reporter_note" rows="3" placeholder="If reporting for another person, explain your relationship or what you saw"></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="scenePhoto">Scene photo</label>
                            <input type="file" class="form-control" id="scenePhoto" name="scene_photo" accept="image/jpeg,image/png,image/webp">
                            <p class="helper-text">
                                For an SOS about another person, a scene photo helps the hospital, clinic, or medical firm confirm that the emergency is real before sending an ambulance or care unit. This helps reduce prank SOS requests, prevents wasted fuel, and lets serious emergencies get attention faster.
                            </p>
                        </div>

                        <div class="col-12">
                            <button type="submit" name="send_sos" class="primary-action">Send SOS request</button>
                        </div>
                    </div>
                </form>
            </article>
        </section>

        <section class="profile-grid recent-records-grid">
            <article class="profile-card recent-records-card">
                <div class="profile-card-heading">
                    <span>SOS history</span>
                    <h2>Emergency requests from this account</h2>
                </div>

                <?php if (empty($emergencies)) { ?>
                    <p class="helper-text">No emergency requests have been sent from this account yet.</p>
                <?php } else { ?>
                    <div class="record-timeline">
                        <?php foreach ($emergencies as $emergency) { ?>
                            <article class="record-item">
                                <div class="record-item-header">
                                    <div>
                                        <span><?php echo emergency_type($emergency["request_type"]); ?></span>
                                        <h3><?php echo emergency_value($emergency["hospital_name"]); ?></h3>
                                    </div>
                                    <strong><?php echo emergency_status($emergency["status"]); ?></strong>
                                </div>

                                <div class="record-status-row">
                                    <span>Victim: <?php echo emergency_value($emergency["victim_name"]); ?></span>
                                    <span>Location: <?php echo emergency_value($emergency["location_address"]); ?></span>
                                    <span>Sent: <?php echo emergency_value($emergency["created_at"]); ?></span>
                                </div>

                                <p class="helper-text"><?php echo emergency_value($emergency["emergency_note"]); ?></p>

                                <?php if (!empty($emergency["scene_photo"])) { ?>
                                    <img src="<?php echo htmlspecialchars($emergency["scene_photo"]); ?>" alt="Emergency scene" class="scene-photo-preview">
                                <?php } ?>

                                <?php if ($emergency["evidence_requested"] == 1) { ?>
                                    <div class="checkup-reminder">
                                        <strong>Scene photo requested</strong>
                                        <p>The organization needs a photo of the scene before deciding whether to dispatch a care unit. This helps them confirm the emergency, reduce false reports, and respond faster when the situation is genuine.</p>
                                    </div>

                                    <form action="" method="post" enctype="multipart/form-data" class="record-update-form">
                                        <input type="hidden" name="emergency_id" value="<?php echo htmlspecialchars($emergency["id"]); ?>">
                                        <div class="row g-3">
                                            <div class="col-md-8">
                                                <input type="file" class="form-control" name="followup_scene_photo" accept="image/jpeg,image/png,image/webp" required>
                                            </div>
                                            <div class="col-md-4">
                                                <button type="submit" name="upload_scene_photo" class="secondary-action">Upload photo</button>
                                            </div>
                                        </div>
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
