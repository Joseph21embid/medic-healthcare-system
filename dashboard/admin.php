<?php
include_once __DIR__ . "/../includes/session.php";
include_once __DIR__ . "/../config/db.php";

$allowed_admin_email = "itisadminjay@gmail.com";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../public/login.php");
    exit;
}

if (!isset($_SESSION["email"]) || $_SESSION["email"] != $allowed_admin_email) {
    header("Location: ../public/login.php");
    exit;
}

$total_individuals = 0;
$total_organizations = 0;
$pending_organizations = 0;
$approved_organizations = 0;
$pending_list = array();
$message = "";

if (isset($_GET["message"])) {
    $message = $_GET["message"];
}

$count_stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE role = 'patient'");
$count_stmt->execute();
$count_stmt->bind_result($total_individuals);
$count_stmt->fetch();
$count_stmt->close();

$count_stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE role = 'hospital'");
$count_stmt->execute();
$count_stmt->bind_result($total_organizations);
$count_stmt->fetch();
$count_stmt->close();

$count_stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE role = 'hospital' AND status = 'pending'");
$count_stmt->execute();
$count_stmt->bind_result($pending_organizations);
$count_stmt->fetch();
$count_stmt->close();

$count_stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE role = 'hospital' AND status = 'active'");
$count_stmt->execute();
$count_stmt->bind_result($approved_organizations);
$count_stmt->fetch();
$count_stmt->close();

$column_stmt = $conn->prepare("SHOW COLUMNS FROM hospitals LIKE 'onboarding_completed'");
$column_stmt->execute();
$column_stmt->store_result();
$hospital_columns_ready = false;

if ($column_stmt->num_rows > 0) {
    $hospital_columns_ready = true;
}

$column_stmt->close();

if ($hospital_columns_ready) {
    $pending_stmt = $conn->prepare("SELECT hospitals.id, hospitals.hospital_name, hospitals.organization_type, hospitals.email, hospitals.phone, hospitals.state, hospitals.lga, hospitals.license_number, hospitals.contact_person, hospitals.onboarding_completed, users.status FROM hospitals INNER JOIN users ON hospitals.user_id = users.id WHERE users.role = 'hospital' AND users.status = 'pending' ORDER BY hospitals.id DESC");
    $pending_stmt->execute();
    $pending_stmt->bind_result($hospital_id, $hospital_name, $organization_type, $email, $phone, $state, $lga, $license_number, $contact_person, $onboarding_completed, $status);

    while ($pending_stmt->fetch()) {
        $pending_list[] = array(
            "id" => $hospital_id,
            "hospital_name" => $hospital_name,
            "organization_type" => $organization_type,
            "email" => $email,
            "phone" => $phone,
            "state" => $state,
            "lga" => $lga,
            "license_number" => $license_number,
            "contact_person" => $contact_person,
            "onboarding_completed" => $onboarding_completed,
            "status" => $status,
        );
    }

    $pending_stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Medic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f6f8fb;
            color: #1e293b;
            font-family: Arial, Helvetica, sans-serif;
        }

        .admin-shell {
            max-width: 1180px;
            margin: 0 auto;
            padding: 32px 18px;
        }

        .admin-topbar,
        .admin-card {
            background: #ffffff;
            border: 1px solid #dce5ef;
            border-radius: 8px;
            box-shadow: 0 14px 36px rgba(15, 23, 42, 0.06);
        }

        .admin-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 20px;
            margin-bottom: 18px;
        }

        .admin-topbar h1 {
            margin: 0;
            color: #0f172a;
            font-size: 1.7rem;
            font-weight: 800;
        }

        .admin-topbar p {
            margin: 4px 0 0;
            color: #64748b;
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 18px;
        }

        .stat-card {
            padding: 18px;
            background: #ffffff;
            border: 1px solid #dce5ef;
            border-radius: 8px;
        }

        .stat-card span,
        .stat-card strong {
            display: block;
        }

        .stat-card span {
            color: #64748b;
            font-size: 0.9rem;
        }

        .stat-card strong {
            margin-top: 8px;
            color: #2563eb;
            font-size: 1.8rem;
            line-height: 1;
        }

        .admin-card {
            padding: 20px;
        }

        .admin-card h2 {
            margin: 0 0 14px;
            color: #0f172a;
            font-size: 1.25rem;
            font-weight: 800;
        }

        .status-pill {
            display: inline-flex;
            padding: 5px 9px;
            color: #92400e;
            background: #fef3c7;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        .action-row {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .alert-note {
            padding: 12px 14px;
            margin-bottom: 14px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            color: #1d4ed8;
            font-weight: 700;
        }

        @media (max-width: 900px) {
            .stat-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .admin-topbar {
                align-items: flex-start;
                flex-direction: column;
            }
        }

        @media (max-width: 560px) {
            .stat-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <main class="admin-shell">
        <section class="admin-topbar">
            <div>
                <h1>Admin Dashboard</h1>
                <p>Review organization applications and monitor basic platform activity.</p>
            </div>

            <form action="../auth/logout.php" method="post">
                <button type="submit" class="btn btn-primary">Logout</button>
            </form>
        </section>

        <?php
        if (!empty($message)) {
            echo "<div class=\"alert-note\">" . htmlspecialchars(str_replace("_", " ", ucfirst($message))) . "</div>";
        }
        ?>

        <section class="stat-grid">
            <article class="stat-card">
                <span>Individuals</span>
                <strong><?php echo htmlspecialchars($total_individuals); ?></strong>
            </article>
            <article class="stat-card">
                <span>Total organizations</span>
                <strong><?php echo htmlspecialchars($total_organizations); ?></strong>
            </article>
            <article class="stat-card">
                <span>Pending organizations</span>
                <strong><?php echo htmlspecialchars($pending_organizations); ?></strong>
            </article>
            <article class="stat-card">
                <span>Accepted organizations</span>
                <strong><?php echo htmlspecialchars($approved_organizations); ?></strong>
            </article>
        </section>

        <section class="admin-card">
            <h2>Pending Hospital / Clinic / Medical Firm Applications</h2>

            <?php if (!$hospital_columns_ready) { ?>
                <p class="text-muted mb-0">Run the hospital onboarding database update to view detailed organization applications.</p>
            <?php } elseif (empty($pending_list)) { ?>
                <p class="text-muted mb-0">No pending organization applications right now.</p>
            <?php } else { ?>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Organization</th>
                                <th>Type</th>
                                <th>Location</th>
                                <th>License</th>
                                <th>Contact</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending_list as $organization) { ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($organization["hospital_name"]); ?></strong><br>
                                        <span class="text-muted"><?php echo htmlspecialchars($organization["email"]); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars(ucwords(str_replace("_", " ", $organization["organization_type"]))); ?></td>
                                    <td><?php echo htmlspecialchars($organization["state"] . " / " . $organization["lga"]); ?></td>
                                    <td><?php echo htmlspecialchars($organization["license_number"]); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($organization["contact_person"]); ?><br>
                                        <span class="text-muted"><?php echo htmlspecialchars($organization["phone"]); ?></span>
                                    </td>
                                    <td><span class="status-pill"><?php echo htmlspecialchars($organization["status"]); ?></span></td>
                                    <td>
                                        <div class="action-row">
                                            <form action="../auth/admin_organization_action.php" method="post">
                                                <input type="hidden" name="hospital_id" value="<?php echo htmlspecialchars($organization["id"]); ?>">
                                                <input type="hidden" name="action" value="approve">
                                                <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                            </form>

                                            <form action="../auth/admin_organization_action.php" method="post">
                                                <input type="hidden" name="hospital_id" value="<?php echo htmlspecialchars($organization["id"]); ?>">
                                                <input type="hidden" name="action" value="reject">
                                                <button type="submit" class="btn btn-outline-danger btn-sm">Reject</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>
        </section>
    </main>
</body>
</html>
