<?php include_once __DIR__ . "/../auth/login_process.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | National Healthcare System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/signup.css">
</head>
<body>
    <main class="signup-page login-page">
        <section class="signup-visual">
            <nav class="brand-bar">
                <a href="index.php" class="brand-mark">
                    <img src="https://placehold.co/44x44/2563EB/FFFFFF?text=NH" alt="National Healthcare logo">
                    <span>NIHERS</span>
                </a>
            </nav>

            <div class="visual-content reveal-left">
                <span class="eyebrow">Welcome back</span>
                <h1>Continue your healthcare journey from one secure dashboard.</h1>
                <p>
                    Sign in to manage your medical profile, monitor appointments, and access emergency healthcare services when needed.
                </p>
            </div>

            <div class="image-panel reveal-up">
                <img src="https://images.unsplash.com/photo-1551190822-a9333d879b1f?auto=format&fit=crop&w=1200&q=80" alt="Doctor reviewing healthcare record">
                <div class="metric-card">
                    <strong>Secure</strong>
                    <span>Access to your health records</span>
                </div>
            </div>
        </section>

        <section class="signup-form-section">
            <div class="form-shell reveal-right">
                <div class="form-heading">
                    <span class="section-label">Sign in</span>
                    <h2>Welcome back</h2>
                    <p>Use your email and password to access your account.</p>
                </div>

                <?php
                if (isset($_SESSION["password_reset_success"])) {
                    echo "<p class=\"form-alert success-alert\">" . htmlspecialchars($_SESSION["password_reset_success"]) . "</p>";
                    unset($_SESSION["password_reset_success"]);
                }
                ?>

                <button type="button" class="google-button" data-google-auth="login">
                    <span>G</span>
                    Continue with Google
                </button>

                <div class="divider">
                    <span></span>
                    <p>or use email</p>
                    <span></span>
                </div>

                <form action="" method="post" class="signup-form">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" value="<?php echo htmlspecialchars($email); ?>" required>
                            <p class="field-error"><?php echo $errors["email"]; ?></p>
                        </div>

                        <div class="col-12">
                            <div class="d-flex align-items-center justify-content-between gap-3">
                                <label for="password" class="form-label mb-0">Password</label>
                                <a href="forgot-password.php" class="small-link">Forgot password?</a>
                            </div>
                            <div class="password-field mt-2">
                                <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
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

                        <div class="col-12">
                            <button type="submit" name="login_submit" class="btn submit-button w-100">Sign in</button>
                        </div>
                    </div>
                </form>

                <p class="signin-text">
                    Do not have an account? <a href="signup.php">Create account</a>
                </p>
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/auth.js"></script>
</body>
</html>
