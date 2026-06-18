<?php
include_once __DIR__ . "/../includes/session.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "patient") {
    header("Location: ../public/login.php");
    exit;
}

include_once __DIR__ . "/../includes/patient_dashboard_data.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Individual Dashboard | Medic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../public/assets/css/patient-dashboard.css">
</head>
<body>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="dashboard-shell">
        <aside class="sidebar" id="patientSidebar">
            <a href="patient.php" class="brand">
                <img src="https://placehold.co/44x44/2563EB/FFFFFF?text=M" alt="Medic logo">
                <span>Medic</span>
            </a>

            <nav class="sidebar-nav">
                <a href="#" class="active"><span>Overview</span></a>
                <a href="../public/individual-profile.php"><span>My Profile</span></a>
                <a href="../public/individual-medical-records.php"><span>Medical Records</span></a>
                <a href="../public/individual-appointments.php"><span>Appointments</span></a>
                <a href="../public/individual-medications.php"><span>Medications</span></a>
                <a href="../public/individual-emergency-sos.php"><span>Emergency SOS</span></a>
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
                    <p class="eyebrow">Individual dashboard</p>
                    <h1>Welcome back, <?php echo htmlspecialchars($patient["full_name"]); ?></h1>
                </div>

                <div class="topbar-profile">
                    <?php
                    if ($avatar_type == "image") {
                        echo "<img src=\"" . htmlspecialchars($avatar_value) . "\" alt=\"" . htmlspecialchars($avatar_alt) . "\">";
                    } else {
                        echo "<span class=\"avatar-face\" aria-label=\"" . htmlspecialchars($avatar_alt) . "\">" . $avatar_value . "</span>";
                    }
                    ?>
                    <div>
                        <strong><?php echo htmlspecialchars($patient["full_name"]); ?></strong>
                        <span>NHIS: <?php echo htmlspecialchars($patient["nhis_number"]); ?></span>
                    </div>
                </div>
            </header>

            <section class="hero-panel">
                <div class="hero-copy">
                    <span>Health profile completion</span>
                    <h2>Your medical information is <?php echo htmlspecialchars($profile_completion); ?>% complete</h2>
                    <p>Complete your NHIS number, blood group, allergies, emergency contact, and current medications to help approved medical organizations respond faster during care or emergencies.</p>
                    <div class="profile-progress" aria-label="Profile completion">
                        <div class="profile-progress-bar" style="width: <?php echo htmlspecialchars($profile_completion); ?>%;"></div>
                    </div>
                    <a href="../public/patient-onboarding.php" class="primary-action">Update profile</a>
                </div>

                <img src="https://images.unsplash.com/photo-1579684385127-1ef15d508118?auto=format&fit=crop&w=900&q=80" alt="Doctor using tablet">
            </section>

            <section class="stat-grid">
                <article class="stat-card">
                    <span class="stat-icon blue">ID</span>
                    <div>
                        <p>Health ID</p>
                        <strong><?php echo htmlspecialchars($patient["health_id"]); ?></strong>
                    </div>
                </article>

                <article class="stat-card">
                    <span class="stat-icon teal">NH</span>
                    <div>
                        <p>NHIS Number</p>
                        <strong><?php echo htmlspecialchars($patient["nhis_number"]); ?></strong>
                    </div>
                </article>

                <article class="stat-card">
                    <span class="stat-icon green">AP</span>
                    <div>
                        <p>Upcoming Appointment</p>
                        <strong><?php echo htmlspecialchars($next_appointment_text); ?></strong>
                    </div>
                </article>

                <article class="stat-card">
                    <span class="stat-icon teal">RX</span>
                    <div>
                        <p>Active Medications</p>
                        <strong><?php echo htmlspecialchars($active_medications_count); ?></strong>
                    </div>
                </article>

                <article class="stat-card emergency">
                    <span class="stat-icon red">SOS</span>
                    <div>
                        <p>Emergency Contact</p>
                        <strong>
                            <?php
                            if (!empty($patient["emergency_contact_phone"])) {
                                echo "Ready";
                            } else {
                                echo "Incomplete";
                            }
                            ?>
                        </strong>
                    </div>
                </article>
            </section>

            <section class="content-grid">
                <article class="quick-actions dashboard-card wide">
                    <div class="card-heading">
                        <div>
                            <p class="eyebrow">Quick actions</p>
                            <h3>What would you like to do?</h3>
                        </div>
                    </div>

                    <div class="action-grid">
                        <a href="../public/patient-onboarding.php">
                            <strong>Update Profile</strong>
                            <span>Add blood group, allergies, and emergency contact</span>
                        </a>
                        <a href="../public/individual-profile.php">
                            <strong>View Profile</strong>
                            <span>Review your identity and medical summary</span>
                        </a>
                        <a href="../public/individual-appointments.php">
                            <strong>Book Appointment</strong>
                            <span>Find a hospital, clinic, or medical firm and request consultation</span>
                        </a>
                        <a href="../public/individual-medical-records.php">
                            <strong>View Records</strong>
                            <span>Open your medical history summary</span>
                        </a>
                        <a href="../public/individual-medications.php">
                            <strong>Medication Reminders</strong>
                            <span>Track active medications and completion</span>
                        </a>
                        <a href="../public/individual-emergency-sos.php">
                            <strong>Emergency SOS</strong>
                            <span>Send urgent help for yourself or report for another person</span>
                        </a>
                    </div>
                </article>

                <article class="dashboard-card">
                    <div class="card-heading">
                        <div>
                            <p class="eyebrow">Medical summary</p>
                            <h3>Individual record snapshot</h3>
                        </div>
                        <a href="../public/individual-medical-records.php">View all</a>
                    </div>

                    <div class="summary-list">
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
                            <span>Chronic condition</span>
                            <strong><?php echo htmlspecialchars($patient["chronic_conditions"]); ?></strong>
                        </div>
                    </div>
                </article>

                <article class="dashboard-card">
                    <div class="card-heading">
                        <div>
                            <p class="eyebrow">Appointments</p>
                            <h3>Next consultation</h3>
                        </div>
                        <a href="../public/individual-appointments.php">Book new</a>
                    </div>

                    <div class="appointment-card">
                        <img src="<?php echo htmlspecialchars($next_appointment_image); ?>" alt="Hospital building">
                        <div>
                            <strong><?php echo htmlspecialchars($next_appointment_hospital); ?></strong>
                            <span><?php echo htmlspecialchars($next_appointment_reason); ?></span>
                            <p><?php echo htmlspecialchars($next_appointment_text); ?></p>
                        </div>
                    </div>
                </article>

                <article class="dashboard-card wide">
                    <div class="card-heading">
                        <div>
                            <p class="eyebrow">Latest checkup</p>
                            <h3>Last recorded care result</h3>
                        </div>
                        <a href="../public/individual-medical-records.php">View record</a>
                    </div>

                    <div class="summary-list">
                        <div>
                            <span>Organization</span>
                            <strong><?php echo htmlspecialchars($latest_checkup_hospital); ?></strong>
                        </div>
                        <div>
                            <span>Date</span>
                            <strong><?php echo htmlspecialchars($latest_checkup_date); ?></strong>
                        </div>
                        <div>
                            <span>Possible illness</span>
                            <strong><?php echo htmlspecialchars($latest_checkup_possible_illness); ?></strong>
                        </div>
                        <div>
                            <span>Result status</span>
                            <strong><?php echo htmlspecialchars($latest_checkup_result_status); ?></strong>
                        </div>
                        <div>
                            <span>Medication status</span>
                            <strong><?php echo htmlspecialchars($latest_checkup_medication_status); ?></strong>
                        </div>
                        <div>
                            <span>Vitals</span>
                            <strong><?php echo htmlspecialchars($latest_checkup_vitals); ?></strong>
                        </div>
                    </div>
                </article>

                <article class="dashboard-card wide">
                    <div class="card-heading">
                        <div>
                            <p class="eyebrow">Medication reminders</p>
                            <h3>Current medication summary</h3>
                        </div>
                        <a href="../public/individual-medications.php">Manage</a>
                    </div>

                    <div class="timeline">
                        <div class="timeline-item">
                            <span>Current medications</span>
                            <strong><?php echo htmlspecialchars($patient["current_medications"]); ?></strong>
                            <p>This will become a full reminder schedule when we build the medication module.</p>
                        </div>
                    </div>
                </article>

                <article class="sos-card">
                    <div>
                        <p class="eyebrow">Emergency</p>
                        <h3>Need urgent medical help?</h3>
                        <span>Your location and medical summary can be sent to a nearby approved medical organization.</span>
                    </div>
                    <a href="../public/individual-emergency-sos.php">Send SOS</a>
                </article>
            </section>
        </main>
    </div>

    <script src="../public/assets/js/patient-dashboard.js"></script>
</body>
</html>
