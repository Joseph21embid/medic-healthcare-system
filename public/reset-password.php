<?php include_once __DIR__ . "/../auth/forgot_password_process.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Medic</title>
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
                <span class="eyebrow">New password</span>
                <h1>Create a secure new password.</h1>
                <p>Use at least 8 characters with uppercase, lowercase, and a number.</p>
            </div>
        </section>

        <section class="signup-form-section">
            <div class="form-shell reveal-right">
                <div class="form-heading">
                    <span class="section-label">Reset password</span>
                    <h2>Set new password</h2>
                    <p>Your old password will stop working after this reset.</p>
                </div>

                <form action="" method="post" class="signup-form">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="password" class="form-label">New password</label>
                            <div class="password-field mt-2">
                                <input type="password" class="form-control" id="password" name="password" placeholder="Enter new password" required>
                                <button type="button" class="password-toggle" data-toggle-password="password" aria-label="Show password">
                                    <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                            </div>
                            <p class="field-error"><?php echo $errors["password"]; ?></p>
                        </div>

                        <div class="col-12">
                            <label for="confirmPassword" class="form-label">Confirm new password</label>
                            <div class="password-field mt-2">
                                <input type="password" class="form-control" id="confirmPassword" name="confirm_password" placeholder="Confirm new password" required>
                                <button type="button" class="password-toggle" data-toggle-password="confirmPassword" aria-label="Show password">
                                    <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                            </div>
                            <p class="field-error"><?php echo $errors["confirm_password"]; ?></p>
                        </div>

                        <div class="col-12">
                            <button type="submit" name="reset_password" class="btn submit-button w-100">Reset password</button>
                        </div>
                    </div>
                </form>

                <p class="signin-text">
                    Back to <a href="login.php">Sign in</a>
                </p>
            </div>
        </section>
    </main>

    <script src="assets/js/auth.js"></script>
</body>
</html>
