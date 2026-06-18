<?php include_once __DIR__ . "/../auth/forgot_password_process.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | Medic</title>
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
                <span class="eyebrow">Account recovery</span>
                <h1>Reset your password with a short OTP.</h1>
                <p>Enter your account email and Medic will generate a temporary code for password reset.</p>
            </div>
        </section>

        <section class="signup-form-section">
            <div class="form-shell reveal-right">
                <div class="form-heading">
                    <span class="section-label">Forgot password</span>
                    <h2>Request reset OTP</h2>
                    <p>The OTP expires after 10 minutes.</p>
                </div>

                <?php
                if (!empty($success_message)) {
                    echo "<p class=\"form-alert success-alert\">" . htmlspecialchars($success_message) . "</p>";
                    echo "<a href=\"verify-otp.php\" class=\"btn submit-button w-100 mb-3\">Continue to OTP verification</a>";
                }

                if (!empty($dev_message)) {
                    echo "<p class=\"form-alert success-alert\">" . htmlspecialchars($dev_message) . "</p>";
                }
                ?>

                <form action="" method="post" class="signup-form">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" value="<?php echo htmlspecialchars($email); ?>" required>
                            <p class="field-error"><?php echo $errors["email"]; ?></p>
                        </div>

                        <div class="col-12">
                            <button type="submit" name="request_otp" class="btn submit-button w-100">Send OTP</button>
                        </div>
                    </div>
                </form>

                <p class="signin-text">
                    Remember your password? <a href="login.php">Sign in</a>
                </p>
            </div>
        </section>
    </main>
</body>
</html>
