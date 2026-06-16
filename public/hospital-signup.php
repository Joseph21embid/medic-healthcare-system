<?php include_once __DIR__ . "/../auth/signup_process.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Signup | National Healthcare System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/signup.css">
</head>
<body>
    <main class="signup-page">
        <section class="signup-visual hospital-visual">
            <nav class="brand-bar">
                <a href="index.php" class="brand-mark">
                    <img src="https://placehold.co/44x44/2563EB/FFFFFF?text=NH" alt="National Healthcare logo">
                    <span>NIHERS</span>
                </a>
            </nav>

            <div class="visual-content reveal-left">
                <span class="eyebrow">Hospital and clinic access</span>
                <h1>Register your medical organization for verified platform access.</h1>
                <p>
                    Hospital accounts will go through extra profile and verification questions before full dashboard access is approved.
                </p>
            </div>

            <div class="image-panel reveal-up">
                <img src="https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=1200&q=80" alt="Modern hospital ward">
                <div class="metric-card hospital-metric">
                    <strong>Review</strong>
                    <span>Organization verification required</span>
                </div>
            </div>
        </section>

        <section class="signup-form-section">
            <div class="form-shell reveal-right">
                <div class="form-heading">
                    <span class="section-label">Organization signup</span>
                    <h2>Create hospital account</h2>
                    <p>Use your official organization details. More verification forms will be added before the hospital dashboard is opened fully.</p>
                </div>

                <button type="button" class="google-button" data-google-auth="signup">
                    <span>G</span>
                    Continue with Google
                </button>

                <div class="divider">
                    <span></span>
                    <p>or use email</p>
                    <span></span>
                </div>

                <form action="" method="post" class="signup-form">
                    <input type="hidden" name="role" value="hospital">

                    <div class="row g-3">
                        <div class="col-12">
                            <label for="hospitalName" class="form-label">Hospital, clinic, or medical firm name</label>
                            <input type="text" class="form-control" id="hospitalName" name="name" placeholder="Enter organization name" value="<?php echo htmlspecialchars($name); ?>" required>
                            <p class="field-error"><?php echo $errors["name"]; ?></p>
                        </div>

                        <div class="col-12">
                            <label for="email" class="form-label">Official email address</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="admin@hospital.com" value="<?php echo htmlspecialchars($email); ?>" required>
                            <p class="field-error"><?php echo $errors["email"]; ?></p>
                        </div>

                        <div class="col-md-6">
                            <label for="password" class="form-label">Password</label>
                            <div class="password-field">
                                <input type="password" class="form-control" id="password" name="password" placeholder="Create password" required>
                                <button type="button" class="password-toggle" data-toggle-password="password" aria-label="Show password">
                                    <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                    <svg class="eye-closed" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M10.7 5.1A10.9 10.9 0 0 1 12 5c6.5 0 10 7 10 7a16.1 16.1 0 0 1-3.2 4.2"></path>
                                        <path d="M6.6 6.6C3.6 8.6 2 12 2 12s3.5 7 10 7a10.8 10.8 0 0 0 5.4-1.4"></path>
                                        <path d="M14.1 14.1A3 3 0 0 1 9.9 9.9"></path>
                                        <path d="M3 3l18 18"></path>
                                    </svg>
                                </button>
                            </div>
                            <p class="field-error"><?php echo $errors["password"]; ?></p>
                        </div>

                        <div class="col-md-6">
                            <label for="confirmPassword" class="form-label">Confirm password</label>
                            <div class="password-field">
                                <input type="password" class="form-control" id="confirmPassword" name="confirm_password" placeholder="Repeat password" required>
                                <button type="button" class="password-toggle" data-toggle-password="confirmPassword" aria-label="Show confirm password">
                                    <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                    <svg class="eye-closed" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M10.7 5.1A10.9 10.9 0 0 1 12 5c6.5 0 10 7 10 7a16.1 16.1 0 0 1-3.2 4.2"></path>
                                        <path d="M6.6 6.6C3.6 8.6 2 12 2 12s3.5 7 10 7a10.8 10.8 0 0 0 5.4-1.4"></path>
                                        <path d="M14.1 14.1A3 3 0 0 1 9.9 9.9"></path>
                                        <path d="M3 3l18 18"></path>
                                    </svg>
                                </button>
                            </div>
                            <p class="field-error"><?php echo $errors["confirm_password"]; ?></p>
                        </div>

                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to responsible use of the healthcare platform.
                                </label>
                            </div>
                            <p class="field-error"><?php echo $errors["terms"]; ?></p>
                        </div>

                        <div class="col-12">
                            <button type="submit" name="signup_submit" class="btn submit-button w-100">Create organization account</button>
                        </div>
                    </div>
                </form>

                <p class="signin-text">
                    Signing up as a patient? <a href="patient-signup.php">Use individual signup</a>
                </p>
                <p class="signin-text mt-2">
                    Already have an account? <a href="login.php">Sign in</a>
                </p>
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/auth.js"></script>
</body>
</html>
