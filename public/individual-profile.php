<?php
include_once __DIR__ . "/../includes/session.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "patient") {
    header("Location: login.php");
    exit;
}

include_once __DIR__ . "/../includes/patient_dashboard_data.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Individual Profile | Medic</title>
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
                <a href="print-individual-summary.php" class="secondary-action">Print summary</a>
                <a href="patient-onboarding.php" class="primary-action">Edit profile</a>
            </div>
        </header>

        <section class="profile-hero-card">
            <div class="profile-avatar-large">
                <?php
                if ($avatar_type == "image") {
                    echo "<img src=\"" . htmlspecialchars($avatar_value) . "\" alt=\"" . htmlspecialchars($avatar_alt) . "\">";
                } else {
                    echo "<span aria-label=\"" . htmlspecialchars($avatar_alt) . "\">" . $avatar_value . "</span>";
                }
                ?>
            </div>

            <div>
                <span class="eyebrow">Individual profile</span>
                <h1><?php echo htmlspecialchars($patient["full_name"]); ?></h1>
                <p>
                    This profile contains the personal identity and medical summary that will help approved medical organizations support care safely.
                </p>

                <div class="profile-progress compact-progress" aria-label="Profile completion">
                    <div class="profile-progress-bar" style="width: <?php echo htmlspecialchars($profile_completion); ?>%;"></div>
                </div>
                <strong class="profile-percent"><?php echo htmlspecialchars($profile_completion); ?>% complete</strong>
            </div>
        </section>

        <section class="profile-grid">
            <article class="profile-card">
                <div class="profile-card-heading">
                    <span>Identity</span>
                    <h2>Core details</h2>
                </div>

                <div class="profile-detail-list">
                    <div>
                        <span>Health ID</span>
                        <strong><?php echo htmlspecialchars($patient["health_id"]); ?></strong>
                    </div>
                    <div>
                        <span>NHIS number</span>
                        <strong><?php echo htmlspecialchars($patient["nhis_number"]); ?></strong>
                    </div>
                    <div>
                        <span>Gender</span>
                        <strong>
                            <?php
                            if (!empty($patient["gender"])) {
                                echo htmlspecialchars(ucfirst($patient["gender"]));
                            } else {
                                echo "Not provided";
                            }
                            ?>
                        </strong>
                    </div>
                </div>
            </article>

            <article class="profile-card">
                <div class="profile-card-heading">
                    <span>Medical summary</span>
                    <h2>Health information</h2>
                </div>

                <div class="profile-detail-list">
                    <div>
                        <span>Blood group</span>
                        <strong><?php echo htmlspecialchars($patient["blood_group"]); ?></strong>
                    </div>
                    <div>
                        <span>Genotype</span>
                        <strong><?php echo htmlspecialchars($patient["genotype"]); ?></strong>
                    </div>
                    <div>
                        <span>Known allergies</span>
                        <strong><?php echo htmlspecialchars($patient["allergies"]); ?></strong>
                    </div>
                    <div>
                        <span>Chronic conditions</span>
                        <strong><?php echo htmlspecialchars($patient["chronic_conditions"]); ?></strong>
                    </div>
                    <div>
                        <span>Current medications</span>
                        <strong><?php echo htmlspecialchars($patient["current_medications"]); ?></strong>
                    </div>
                </div>
            </article>

            <article class="profile-card">
                <div class="profile-card-heading">
                    <span>Emergency</span>
                    <h2>Contact readiness</h2>
                </div>

                <div class="profile-detail-list">
                    <div>
                        <span>Emergency contact</span>
                        <strong>
                            <?php
                            if (!empty($patient["emergency_contact_name"])) {
                                echo htmlspecialchars($patient["emergency_contact_name"]);
                            } else {
                                echo "Not provided";
                            }
                            ?>
                        </strong>
                    </div>
                    <div>
                        <span>Emergency phone</span>
                        <strong>
                            <?php
                            if (!empty($patient["emergency_contact_phone"])) {
                                echo htmlspecialchars($patient["emergency_contact_phone"]);
                            } else {
                                echo "Not provided";
                            }
                            ?>
                        </strong>
                    </div>
                </div>
            </article>
        </section>
    </main>
</body>
</html>
