<?php
include_once __DIR__ . "/../includes/session.php";
include_once __DIR__ . "/../config/db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "hospital") {
    header("Location: ../public/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$account_status = "pending";
$onboarding_completed = 0;
$approval_seen = 1;
$show_congratulations = false;

$organization = array(
    "id" => 0,
    "hospital_name" => $_SESSION["name_tag"],
    "organization_type" => "Medical organization",
    "email" => $_SESSION["email"],
    "phone" => "Not provided",
    "address" => "Not provided",
    "state" => "Not provided",
    "lga" => "Not provided",
    "license_number" => "Not provided",
    "contact_person" => "Not provided",
    "contact_phone" => "Not provided",
    "verification_status" => "pending",
);

$total_individuals = 0;
$total_appointments = 0;
$pending_appointments = 0;
$emergency_requests = 0;
$medical_records_count = 0;

$status_stmt = $conn->prepare("SELECT status FROM users WHERE id = ? LIMIT 1");
$status_stmt->bind_param("i", $user_id);
$status_stmt->execute();
$status_stmt->bind_result($saved_status);

if ($status_stmt->fetch()) {
    $account_status = $saved_status;
}

$status_stmt->close();

$hospital_stmt = $conn->prepare("SELECT id, hospital_name, organization_type, email, phone, address, state, lga, license_number, contact_person, contact_phone, onboarding_completed, approval_seen, verification_status FROM hospitals WHERE user_id = ? LIMIT 1");
$hospital_stmt->bind_param("i", $user_id);
$hospital_stmt->execute();
$hospital_stmt->bind_result($hospital_id, $hospital_name, $organization_type, $email, $phone, $address, $state, $lga, $license_number, $contact_person, $contact_phone, $saved_onboarding_completed, $saved_approval_seen, $verification_status);

if ($hospital_stmt->fetch()) {
    $organization["id"] = $hospital_id;

    if (!empty($hospital_name)) {
        $organization["hospital_name"] = $hospital_name;
    }

    if (!empty($organization_type)) {
        $organization["organization_type"] = ucwords(str_replace("_", " ", $organization_type));
    }

    if (!empty($email)) {
        $organization["email"] = $email;
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

    if (!empty($license_number)) {
        $organization["license_number"] = $license_number;
    }

    if (!empty($contact_person)) {
        $organization["contact_person"] = $contact_person;
    }

    if (!empty($contact_phone)) {
        $organization["contact_phone"] = $contact_phone;
    }

    if (!empty($verification_status)) {
        $organization["verification_status"] = $verification_status;
    }

    $onboarding_completed = $saved_onboarding_completed;
    $approval_seen = $saved_approval_seen;
}

$hospital_stmt->close();

if ($onboarding_completed != 1) {
    header("Location: ../public/hospital-onboarding.php");
    exit;
}

if ($account_status != "active") {
    header("Location: ../public/hospital-pending.php");
    exit;
}

if ($approval_seen == 0) {
    $show_congratulations = true;

    $seen_stmt = $conn->prepare("UPDATE hospitals SET approval_seen = 1 WHERE user_id = ?");
    $seen_stmt->bind_param("i", $user_id);
    $seen_stmt->execute();
    $seen_stmt->close();
}

$count_stmt = $conn->prepare("SELECT COUNT(*) FROM patients");
$count_stmt->execute();
$count_stmt->bind_result($total_individuals);
$count_stmt->fetch();
$count_stmt->close();

if ($organization["id"] > 0) {
    $count_stmt = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE hospital_id = ?");
    $count_stmt->bind_param("i", $organization["id"]);
    $count_stmt->execute();
    $count_stmt->bind_result($total_appointments);
    $count_stmt->fetch();
    $count_stmt->close();

    $count_stmt = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE hospital_id = ? AND status = 'pending'");
    $count_stmt->bind_param("i", $organization["id"]);
    $count_stmt->execute();
    $count_stmt->bind_result($pending_appointments);
    $count_stmt->fetch();
    $count_stmt->close();

    $count_stmt = $conn->prepare("SELECT COUNT(*) FROM emergency_requests WHERE hospital_id = ? AND status IN ('pending', 'accepted', 'responding')");
    $count_stmt->bind_param("i", $organization["id"]);
    $count_stmt->execute();
    $count_stmt->bind_result($emergency_requests);
    $count_stmt->fetch();
    $count_stmt->close();

    $count_stmt = $conn->prepare("SELECT COUNT(*) FROM medical_records WHERE hospital_id = ?");
    $count_stmt->bind_param("i", $organization["id"]);
    $count_stmt->execute();
    $count_stmt->bind_result($medical_records_count);
    $count_stmt->fetch();
    $count_stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organization Dashboard | Medic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../public/assets/css/patient-dashboard.css">
</head>
<body>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="dashboard-shell">
        <aside class="sidebar" id="patientSidebar">
            <a href="hospital.php" class="brand">
                <img src="https://placehold.co/44x44/2563EB/FFFFFF?text=M" alt="Medic logo">
                <span>Medic</span>
            </a>

            <nav class="sidebar-nav">
                <a href="#" class="active"><span>Overview</span></a>
                <a href="../public/hospital-onboarding.php"><span>Organization Profile</span></a>
                <a href="../public/hospital-nhis-lookup.php"><span>NHIS Lookup</span></a>
                <a href="../public/hospital-appointments.php"><span>Appointments</span></a>
                <a href="../public/hospital-medical-records.php"><span>Medical Records</span></a>
                <a href="../public/hospital-emergency-queue.php"><span>Emergency Requests</span></a>
            </nav>

            <form action="../auth/logout.php" method="post" class="logout-form">
                <button type="submit">Logout</button>
            </form>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <button class="menu-toggle" type="button" id="sidebarToggle" aria-label="Open sidebar">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>

                <div>
                    <p class="eyebrow">Organization dashboard</p>
                    <h1><?php echo htmlspecialchars($organization["hospital_name"]); ?></h1>
                </div>

                <div class="topbar-profile">
                    <span class="avatar-face" aria-label="Organization avatar">+</span>
                    <div>
                        <strong><?php echo htmlspecialchars($organization["organization_type"]); ?></strong>
                        <span><?php echo htmlspecialchars($organization["state"] . " / " . $organization["lga"]); ?></span>
                    </div>
                </div>
            </header>

            <?php if ($show_congratulations) { ?>
                <section class="dashboard-card wide" style="margin-bottom: 18px; background: #f0fdf4; border-color: #86efac;">
                    <p class="eyebrow">Application accepted</p>
                    <h3>Congratulations, your organization has been approved.</h3>
                    <p style="margin: 8px 0 0; color: #166534;">You now have access to the organization dashboard. This message will only show once.</p>
                </section>
            <?php } ?>

            <section class="hero-panel">
                <div class="hero-copy">
                    <span>Verified organization access</span>
                    <h2>Manage healthcare workflows from one organization workspace.</h2>
                    <p>
                        Start with individual lookup by NHIS number, appointment review, medical record updates, and emergency response queues as the next modules come online.
                    </p>
                    <a href="../public/hospital-nhis-lookup.php" class="primary-action">Search individual by NHIS</a>
                </div>

                <img src="https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=900&q=80" alt="Hospital reception area">
            </section>

            <section class="stat-grid">
                <article class="stat-card">
                    <span class="stat-icon blue">IN</span>
                    <div>
                        <p>Registered Individuals</p>
                        <strong><?php echo htmlspecialchars($total_individuals); ?></strong>
                    </div>
                </article>

                <article class="stat-card">
                    <span class="stat-icon green">AP</span>
                    <div>
                        <p>Total Appointments</p>
                        <strong><?php echo htmlspecialchars($total_appointments); ?></strong>
                    </div>
                </article>

                <article class="stat-card">
                    <span class="stat-icon teal">PN</span>
                    <div>
                        <p>Pending Appointments</p>
                        <strong><?php echo htmlspecialchars($pending_appointments); ?></strong>
                    </div>
                </article>

                <article class="stat-card emergency">
                    <span class="stat-icon red">ER</span>
                    <div>
                        <p>Open Emergencies</p>
                        <strong><?php echo htmlspecialchars($emergency_requests); ?></strong>
                    </div>
                </article>
            </section>

            <section class="content-grid">
                <article class="quick-actions dashboard-card wide">
                    <div class="card-heading">
                        <div>
                            <p class="eyebrow">Quick actions</p>
                            <h3>What would you like to handle?</h3>
                        </div>
                    </div>

                    <div class="action-grid">
                        <a href="../public/hospital-nhis-lookup.php">
                            <strong>Find Individual</strong>
                            <span>Search using NHIS number and view a limited care summary</span>
                        </a>
                        <a href="../public/hospital-medical-records.php">
                            <strong>Update Medical Records</strong>
                            <span>Add lab results, treatment updates, and medication progress</span>
                        </a>
                        <a href="../public/hospital-appointments.php">
                            <strong>Review Appointments</strong>
                            <span>Manage pending and upcoming appointment requests</span>
                        </a>
                        <a href="../public/hospital-emergency-queue.php">
                            <strong>Emergency Queue</strong>
                            <span>Review urgent requests assigned to this organization</span>
                        </a>
                    </div>
                </article>

                <article class="dashboard-card">
                    <div class="card-heading">
                        <div>
                            <p class="eyebrow">Organization profile</p>
                            <h3>Verification summary</h3>
                        </div>
                        <a href="../public/hospital-onboarding.php">Edit</a>
                    </div>

                    <div class="summary-list">
                        <div>
                            <span>License number</span>
                            <strong><?php echo htmlspecialchars($organization["license_number"]); ?></strong>
                        </div>
                        <div>
                            <span>Contact person</span>
                            <strong><?php echo htmlspecialchars($organization["contact_person"]); ?></strong>
                        </div>
                        <div>
                            <span>Contact phone</span>
                            <strong><?php echo htmlspecialchars($organization["contact_phone"]); ?></strong>
                        </div>
                        <div>
                            <span>Status</span>
                            <strong>Accepted</strong>
                        </div>
                    </div>
                </article>

                <article class="dashboard-card">
                    <div class="card-heading">
                        <div>
                            <p class="eyebrow">Records</p>
                            <h3>Medical records handled</h3>
                        </div>
                        <a href="../public/hospital-medical-records.php">Open</a>
                    </div>

                    <div class="timeline">
                        <div class="timeline-item">
                            <span>Total records</span>
                            <strong><?php echo htmlspecialchars($medical_records_count); ?></strong>
                            <p>Use NHIS lookup to create a visit record, then update results and medication progress here.</p>
                        </div>
                    </div>
                </article>

                <article class="sos-card">
                    <div>
                        <p class="eyebrow">Emergency response</p>
                        <h3>Keep your emergency queue visible.</h3>
                        <span>Open emergency requests assigned to this organization will appear here as the SOS module grows.</span>
                    </div>
                    <a href="../public/hospital-emergency-queue.php">View Queue</a>
                </article>
            </section>
        </main>
    </div>

    <script src="../public/assets/js/patient-dashboard.js"></script>
</body>
</html>
