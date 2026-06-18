<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medic | Connected Healthcare And Emergency Response</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
    <header class="site-header" id="siteHeader">
        <a href="index.php" class="brand" aria-label="Medic home">
            <img src="https://placehold.co/44x44/2563EB/FFFFFF?text=M" alt="Medic logo">
            <span>Medic</span>
        </a>

        <button class="nav-toggle" type="button" id="navToggle" aria-label="Toggle navigation">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <nav class="main-nav" id="mainNav" aria-label="Main navigation">
            <a href="#platform">Platform</a>
            <a href="#records">Records</a>
            <a href="#emergency">Emergency</a>
            <a href="#workflow">Workflow</a>
            <a href="login.php">Sign in</a>
        </nav>

        <a href="signup.php" class="header-action">Get started</a>
    </header>

    <main>
        <section class="hero-section">
            <div class="hero-shade"></div>

            <div class="hero-inner">
                <div class="hero-copy">
                    <span class="hero-kicker">National healthcare access system</span>
                    <h1>Medic</h1>
                    <p>
                        A connected healthcare platform for individual records, organization access, appointment coordination,
                        medication awareness, and emergency response readiness.
                    </p>

                    <div class="hero-actions">
                        <a href="signup.php" class="primary-action">Create individual account</a>
                        <a href="signup.php" class="secondary-action">Register organization</a>
                    </div>
                </div>

                <aside class="hero-panel" aria-label="Medic system snapshot">
                    <div class="panel-top">
                        <div>
                            <span>Live profile</span>
                            <strong>62% complete</strong>
                        </div>
                        <span class="status-pill">Ready</span>
                    </div>

                    <div class="profile-mini">
                        <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=160&q=80" alt="Individual profile">
                        <div>
                            <strong>Individual MED-2026-001</strong>
                            <span>Blood group: O+ | Allergy: Penicillin</span>
                        </div>
                    </div>

                    <div class="panel-grid">
                        <div>
                            <span>Appointments</span>
                            <strong>2</strong>
                        </div>
                        <div>
                            <span>Medications</span>
                            <strong>3</strong>
                        </div>
                        <div>
                            <span>Emergency</span>
                            <strong>SOS</strong>
                        </div>
                    </div>
                </aside>
            </div>

            <div class="hero-metrics">
                <article>
                    <strong>1</strong>
                    <span>Unified individual profile</span>
                </article>
                <article>
                    <strong>24/7</strong>
                    <span>Emergency-ready design</span>
                </article>
                <article>
                    <strong>3-way</strong>
                    <span>Individual, organization, admin flow</span>
                </article>
            </div>
        </section>

        <section class="platform-section" id="platform">
            <div class="section-heading">
                <span>Platform modules</span>
                <h2>Designed around the healthcare journeys individuals and medical organizations repeat every day.</h2>
            </div>

            <div class="module-grid">
                <button class="module-card active" type="button" data-module="patients">
                    <strong>Individuals</strong>
                    <span>Profiles, records, medication summaries, emergency contacts.</span>
                </button>
                <button class="module-card" type="button" data-module="hospitals">
                    <strong>Hospitals / Clinics / Medical Firms</strong>
                    <span>Registration, verification, appointments, and future record updates.</span>
                </button>
                <button class="module-card" type="button" data-module="emergency">
                    <strong>Emergency</strong>
                    <span>SOS flows, location details, and urgent medical summaries.</span>
                </button>
            </div>

            <div class="module-preview">
                <img id="moduleImage" src="https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=1200&q=80" alt="Medic individual module preview">
                <div class="module-copy">
                    <span id="moduleLabel">Individual workspace</span>
                    <h3 id="moduleTitle">A dashboard that begins with the individual profile.</h3>
                    <p id="moduleText">
                        Medic starts by collecting individual information that can later power medical summaries,
                        appointments, emergency response, and medication awareness.
                    </p>
                </div>
            </div>
        </section>

        <section class="records-section" id="records">
            <div class="records-copy">
                <span>Medical records</span>
                <h2>Information that is structured enough to support real care decisions.</h2>
                <p>
                    The individual profile captures the details that matter first: health ID, blood group, genotype,
                    allergies, chronic conditions, medications, and emergency contact information.
                </p>

                <div class="records-list">
                    <div>
                        <strong>Health identity</strong>
                        <span>Unique Medic health profile for every individual.</span>
                    </div>
                    <div>
                        <strong>Medical summary</strong>
                        <span>Important details displayed cleanly on the dashboard.</span>
                    </div>
                    <div>
                        <strong>Future records</strong>
                        <span>Ready for appointments, lab results, prescriptions, and organization updates.</span>
                    </div>
                </div>
            </div>

            <div class="records-media">
                <img src="https://images.unsplash.com/photo-1581056771107-24ca5f033842?auto=format&fit=crop&w=1200&q=80" alt="Doctor using a tablet">
                <div class="floating-card">
                    <span>Profile completion</span>
                    <strong>Connects directly to the individual dashboard</strong>
                </div>
            </div>
        </section>

        <section class="emergency-section" id="emergency">
            <div class="emergency-copy">
                <span>Emergency layer</span>
                <h2>Built for moments where access to individual health details should not be delayed.</h2>
                <p>
                    Medic’s emergency module will later combine medical summaries, location details,
                    individual contacts, and nearby organization routing.
                </p>
            </div>

            <div class="emergency-card">
                <strong>SOS readiness</strong>
                <p>Emergency contact and medical summary status can be prepared before an urgent situation happens.</p>
                <a href="signup.php">Prepare profile</a>
            </div>
        </section>

        <section class="workflow-section" id="workflow">
            <div class="section-heading compact">
                <span>Workflow</span>
                <h2>The first experience stays simple and defensible.</h2>
            </div>

            <div class="workflow-grid">
                <article>
                    <span>01</span>
                    <h3>Create account</h3>
                    <p>Individuals sign up normally or with Google, then enter the system through role-based authentication.</p>
                </article>
                <article>
                    <span>02</span>
                    <h3>Complete profile</h3>
                    <p>Individuals fill onboarding details that become dashboard-ready medical information.</p>
                </article>
                <article>
                    <span>03</span>
                    <h3>Use dashboard</h3>
                    <p>The dashboard displays health ID, profile completion, medical summary, medications, and appointments.</p>
                </article>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div>
            <strong>Medic</strong>
            <span>National Integrated Healthcare Information and Emergency Response System</span>
        </div>
        <a href="signup.php">Start now</a>
    </footer>

    <script src="assets/js/landing.js"></script>
</body>
</html>
