<?php
include_once __DIR__ . "/../includes/session.php";
include_once __DIR__ . "/../config/db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "hospital") {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$organization_name = $_SESSION["name_tag"];
$account_status = "pending";
$onboarding_completed = 0;
$database_ready = false;

$status_stmt = $conn->prepare("SELECT status FROM users WHERE id = ? LIMIT 1");
$status_stmt->bind_param("i", $user_id);
$status_stmt->execute();
$status_stmt->bind_result($saved_status);

if ($status_stmt->fetch()) {
    $account_status = $saved_status;
}

$status_stmt->close();

$column_stmt = $conn->prepare("SHOW COLUMNS FROM hospitals LIKE 'onboarding_completed'");
$column_stmt->execute();
$column_stmt->store_result();

if ($column_stmt->num_rows > 0) {
    $database_ready = true;
}

$column_stmt->close();

if ($database_ready) {
    $hospital_stmt = $conn->prepare("SELECT hospital_name, onboarding_completed FROM hospitals WHERE user_id = ? LIMIT 1");
    $hospital_stmt->bind_param("i", $user_id);
    $hospital_stmt->execute();
    $hospital_stmt->bind_result($saved_organization_name, $saved_onboarding_completed);

    if ($hospital_stmt->fetch()) {
        if (!empty($saved_organization_name)) {
            $organization_name = $saved_organization_name;
        }

        $onboarding_completed = $saved_onboarding_completed;
    }

    $hospital_stmt->close();
}

if ($account_status == "active" && $onboarding_completed == 1) {
    header("Location: ../dashboard/hospital.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organization Verification Pending | Medic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/patient-onboarding.css">
</head>
<body>
    <main class="pending-page">
        <section class="pending-card">
            <a href="index.php" class="brand">
                <img src="https://placehold.co/44x44/2563EB/FFFFFF?text=M" alt="Medic logo">
                <span>Medic</span>
            </a>

            <span class="pending-badge">Verification pending</span>
            <h1>Your application is being processed and reviewed.</h1>
            <p>
                If the wait list is long, come back to check if your hospital, clinic, or medical firm has been accepted in a day.
            </p>
            <p class="pending-organization-name"><?php echo htmlspecialchars($organization_name); ?></p>

            <div class="pending-status-grid">
                <div>
                    <span>Profile details</span>
                    <strong>
                        <?php
                        if ($onboarding_completed == 1) {
                            echo "Submitted";
                        } else {
                            echo "Incomplete";
                        }
                        ?>
                    </strong>
                </div>
                <div>
                    <span>Account status</span>
                    <strong>
                        <?php
                        if ($account_status == "active") {
                            echo "Accepted";
                        } else {
                            echo htmlspecialchars(ucfirst($account_status));
                        }
                        ?>
                    </strong>
                </div>
            </div>

            <div class="profile-actions">
                <a href="hospital-onboarding.php" class="secondary-action">Update details</a>
                <form action="../auth/logout.php" method="post">
                    <button type="submit" class="primary-action">Logout</button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>
