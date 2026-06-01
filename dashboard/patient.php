<?php
include_once __DIR__ . "/../includes/session.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "patient") {
    header("Location: ../public/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard | Medic</title>
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
                <a href="#"><span>My Profile</span></a>
                <a href="#"><span>Medical Records</span></a>
                <a href="#"><span>Appointments</span></a>
                <a href="#"><span>Medications</span></a>
                <a href="#"><span>Emergency SOS</span></a>
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
                    <p class="eyebrow">Patient dashboard</p>
                    <h1>Welcome back, <?php echo htmlspecialchars($_SESSION["name_tag"]); ?></h1>
                </div>

                <div class="topbar-profile">
                    <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=120&q=80" alt="Patient avatar">
                    <div>
                        <strong><?php echo htmlspecialchars($_SESSION["name_tag"]); ?></strong>
                        <span>Health ID: MED-2026-001</span>
                    </div>
                </div>
            </header>

            <section class="hero-panel">
                <div class="hero-copy">
                    <span>Health profile completion</span>
                    <h2>Your medical information is 62% complete</h2>
                    <p>Complete your blood group, allergies, emergency contact, and current medications to help hospitals respond faster during care or emergencies.</p>
                    <div class="profile-progress" aria-label="Profile completion">
                        <div class="profile-progress-bar"></div>
                    </div>
                    <a href="#" class="primary-action">Complete profile</a>
                </div>

                <img src="https://images.unsplash.com/photo-1579684385127-1ef15d508118?auto=format&fit=crop&w=900&q=80" alt="Doctor using tablet">
            </section>

            <section class="stat-grid">
                <article class="stat-card">
                    <span class="stat-icon blue">ID</span>
                    <div>
                        <p>Health ID</p>
                        <strong>MED-2026-001</strong>
                    </div>
                </article>

                <article class="stat-card">
                    <span class="stat-icon green">AP</span>
                    <div>
                        <p>Upcoming Appointment</p>
                        <strong>Jun 14, 2026</strong>
                    </div>
                </article>

                <article class="stat-card">
                    <span class="stat-icon teal">RX</span>
                    <div>
                        <p>Active Medications</p>
                        <strong>3</strong>
                    </div>
                </article>

                <article class="stat-card emergency">
                    <span class="stat-icon red">SOS</span>
                    <div>
                        <p>Emergency Access</p>
                        <strong>Ready</strong>
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
                        <a href="#">
                            <strong>Update Profile</strong>
                            <span>Add blood group, allergies, and emergency contact</span>
                        </a>
                        <a href="#">
                            <strong>Book Appointment</strong>
                            <span>Find a hospital and request consultation</span>
                        </a>
                        <a href="#">
                            <strong>View Records</strong>
                            <span>Open your medical history summary</span>
                        </a>
                    </div>
                </article>

                <article class="dashboard-card">
                    <div class="card-heading">
                        <div>
                            <p class="eyebrow">Medical summary</p>
                            <h3>Patient record snapshot</h3>
                        </div>
                        <a href="#">View all</a>
                    </div>

                    <div class="summary-list">
                        <div>
                            <span>Blood group</span>
                            <strong>O+</strong>
                        </div>
                        <div>
                            <span>Genotype</span>
                            <strong>AA</strong>
                        </div>
                        <div>
                            <span>Known allergies</span>
                            <strong>Penicillin</strong>
                        </div>
                        <div>
                            <span>Chronic condition</span>
                            <strong>None recorded</strong>
                        </div>
                    </div>
                </article>

                <article class="dashboard-card">
                    <div class="card-heading">
                        <div>
                            <p class="eyebrow">Appointments</p>
                            <h3>Next consultation</h3>
                        </div>
                        <a href="#">Book new</a>
                    </div>

                    <div class="appointment-card">
                        <img src="https://images.unsplash.com/photo-1587351021759-3e566b6af7cc?auto=format&fit=crop&w=300&q=80" alt="Hospital building">
                        <div>
                            <strong>St. Catherine Medical Centre</strong>
                            <span>General consultation</span>
                            <p>Friday, June 14 at 10:30 AM</p>
                        </div>
                    </div>
                </article>

                <article class="dashboard-card wide">
                    <div class="card-heading">
                        <div>
                            <p class="eyebrow">Medication reminders</p>
                            <h3>Today’s schedule</h3>
                        </div>
                        <a href="#">Manage</a>
                    </div>

                    <div class="timeline">
                        <div class="timeline-item">
                            <span>8:00 AM</span>
                            <strong>Vitamin D</strong>
                            <p>1 tablet after breakfast</p>
                        </div>
                        <div class="timeline-item">
                            <span>2:00 PM</span>
                            <strong>Amoxicillin</strong>
                            <p>1 capsule after meal</p>
                        </div>
                        <div class="timeline-item">
                            <span>9:00 PM</span>
                            <strong>Blood pressure check</strong>
                            <p>Record reading before sleep</p>
                        </div>
                    </div>
                </article>

                <article class="sos-card">
                    <div>
                        <p class="eyebrow">Emergency</p>
                        <h3>Need urgent medical help?</h3>
                        <span>Your location and medical summary can be sent to a nearby hospital.</span>
                    </div>
                    <a href="#">Send SOS</a>
                </article>
            </section>
        </main>
    </div>

    <script src="../public/assets/js/patient-dashboard.js"></script>
</body>
</html>
