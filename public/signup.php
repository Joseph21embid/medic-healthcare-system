<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Account Type | National Healthcare System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/signup.css">
</head>
<body>
    <main class="signup-choice-page">
        <section class="choice-hero">
            <nav class="brand-bar">
                <a href="index.php" class="brand-mark">
                    <img src="https://placehold.co/44x44/2563EB/FFFFFF?text=NH" alt="National Healthcare logo">
                    <span>NIHERS</span>
                </a>
                <a href="login.php" class="choice-login-link">Sign in</a>
            </nav>

            <div class="choice-content reveal-up">
                <span class="eyebrow">Create your account</span>
                <h1>Choose how you want to join the healthcare network.</h1>
                <p>
                    Individuals use their profile and NHIS number to access healthcare services. Hospitals, clinics, and medical firms register separately for verification and professional access.
                </p>

                <div class="signup-selector" data-signup-selector>
                    <button type="button" class="selector-trigger" data-selector-trigger aria-expanded="false">
                        <span>
                            <strong>Select signup type</strong>
                            <small>Individual or medical organization</small>
                        </span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="m6 9 6 6 6-6"></path>
                        </svg>
                    </button>

                    <div class="selector-menu" data-selector-menu>
                        <a href="patient-signup.php" class="selector-option">
                            <span class="option-icon patient-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M20 21a8 8 0 0 0-16 0"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                            </span>
                            <span>
                                <strong>Sign up as an individual</strong>
                                <small>Create an individual account for records, appointments, NHIS identity, and emergency access.</small>
                            </span>
                        </a>

                        <a href="hospital-signup.php" class="selector-option">
                            <span class="option-icon hospital-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M3 21h18"></path>
                                    <path d="M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"></path>
                                    <path d="M9 9h6"></path>
                                    <path d="M12 6v6"></path>
                                    <path d="M10 21v-5h4v5"></path>
                                </svg>
                            </span>
                            <span>
                                <strong>Sign up as a hospital, clinic, or medical firm</strong>
                                <small>Register an organization account for verification before accessing organization tools.</small>
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="choice-panel reveal-right">
            <div class="choice-image">
                <img src="https://images.unsplash.com/photo-1584982751601-97dcc096659c?auto=format&fit=crop&w=1200&q=80" alt="Healthcare team reviewing individual records">
            </div>

            <div class="identity-note">
                <span>Individual identification</span>
                <h2>NHIS number stays central.</h2>
                <p>
                    As we build the individual profile and organization tools, the individual's NHIS number will serve as the key identification approved medical organizations use to find and verify records.
                </p>
            </div>

            <div class="choice-stat-grid">
                <div>
                    <strong>01</strong>
                    <span>Choose account type</span>
                </div>
                <div>
                    <strong>02</strong>
                    <span>Complete profile details</span>
                </div>
                <div>
                    <strong>03</strong>
                    <span>Access the right dashboard</span>
                </div>
            </div>
        </section>
    </main>

    <script src="assets/js/auth.js"></script>
</body>
</html>
