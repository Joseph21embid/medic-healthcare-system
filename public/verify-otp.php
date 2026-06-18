<?php include_once __DIR__ . "/../auth/forgot_password_process.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP | Medic</title>
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
                <span class="eyebrow">OTP verification</span>
                <h1>Confirm the code sent to your email.</h1>
                <p>After verification, you will be allowed to create a new password.</p>
            </div>
        </section>

        <section class="signup-form-section">
            <div class="form-shell reveal-right">
                <div class="form-heading">
                    <span class="section-label">Verify OTP</span>
                    <h2>Enter 6-digit code</h2>
                    <p>
                        <?php
                        if (isset($_SESSION["reset_email"])) {
                            echo "Code sent for " . htmlspecialchars($_SESSION["reset_email"]);
                        } else {
                            echo "Start from forgot password if you do not have a code.";
                        }
                        ?>
                    </p>
                </div>

                <form action="" method="post" class="signup-form">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="otp" class="form-label">OTP</label>
                            <input type="text" class="form-control" id="otp" name="otp" maxlength="6" placeholder="123456" value="<?php echo htmlspecialchars($otp); ?>" required>
                            <p class="field-error"><?php echo $errors["otp"]; ?></p>
                        </div>

                        <div class="col-12">
                            <button type="submit" name="verify_otp" class="btn submit-button w-100">Verify OTP</button>
                        </div>
                    </div>
                </form>

                <p class="signin-text">
                    Need a new OTP? <a href="forgot-password.php">Request again</a>
                </p>
            </div>
        </section>
    </main>
</body>
</html>
