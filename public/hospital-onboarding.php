<?php include_once __DIR__ . "/../auth/hospital_onboarding_process.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organization Onboarding | Medic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/patient-onboarding.css">
</head>
<body>
    <main class="onboarding-page organization-onboarding-page">
        <section class="onboarding-intro organization-intro">
            <nav class="brand-row">
                <a href="index.php" class="brand">
                    <img src="https://placehold.co/44x44/2563EB/FFFFFF?text=M" alt="Medic logo">
                    <span>Medic</span>
                </a>
            </nav>

            <div class="intro-content">
                <span class="eyebrow">Organization onboarding</span>
                <h1>Complete your hospital, clinic, or medical firm profile.</h1>
                <p>
                    Organization accounts must be reviewed before full dashboard access is opened. This helps protect individual health records and keeps the platform trustworthy.
                </p>

                <div class="info-card">
                    <strong>Verification required</strong>
                    <span>After submitting these details, your account will remain pending until an admin approves the organization.</span>
                </div>
            </div>
        </section>

        <section class="onboarding-form-section">
            <div class="form-shell">
                <div class="form-heading">
                    <span class="eyebrow">Organization profile</span>
                    <h2>Verification details</h2>
                    <p>Use official organization information. You can update these details later before approval.</p>
                </div>

                <?php
                if (!empty($errors["general"])) {
                    echo "<p class=\"form-alert\">" . htmlspecialchars($errors["general"]) . "</p>";
                }
                ?>

                <form action="" method="post" class="onboarding-form">
                    <div class="form-section">
                        <h3>Organization Information</h3>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="hospitalName" class="form-label">Organization name</label>
                                <input type="text" class="form-control" id="hospitalName" name="hospital_name" placeholder="Enter hospital, clinic, or firm name" value="<?php echo htmlspecialchars($hospital_name); ?>" required>
                                <p class="field-error"><?php echo htmlspecialchars($errors["hospital_name"]); ?></p>
                            </div>

                            <div class="col-md-6">
                                <label for="organizationType" class="form-label">Organization type</label>
                                <select class="form-select" id="organizationType" name="organization_type" required>
                                    <option value="">Select type</option>
                                    <option value="hospital" <?php if ($organization_type == "hospital") { echo "selected"; } ?>>Hospital</option>
                                    <option value="clinic" <?php if ($organization_type == "clinic") { echo "selected"; } ?>>Clinic</option>
                                    <option value="medical_firm" <?php if ($organization_type == "medical_firm") { echo "selected"; } ?>>Medical firm</option>
                                    <option value="diagnostic_center" <?php if ($organization_type == "diagnostic_center") { echo "selected"; } ?>>Diagnostic center</option>
                                </select>
                                <p class="field-error"><?php echo htmlspecialchars($errors["organization_type"]); ?></p>
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">Official email</label>
                                <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($email); ?>" disabled>
                                <p class="helper-text">Email comes from signup and cannot be edited here.</p>
                            </div>

                            <div class="col-md-6">
                                <label for="phone" class="form-label">Official phone number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" placeholder="Enter official phone" value="<?php echo htmlspecialchars($phone); ?>" required>
                                <p class="field-error"><?php echo htmlspecialchars($errors["phone"]); ?></p>
                            </div>

                            <div class="col-md-6">
                                <label for="licenseNumber" class="form-label">License number</label>
                                <input type="text" class="form-control" id="licenseNumber" name="license_number" placeholder="Enter medical license number" value="<?php echo htmlspecialchars($license_number); ?>" required>
                                <p class="field-error"><?php echo htmlspecialchars($errors["license_number"]); ?></p>
                            </div>

                            <div class="col-md-6">
                                <label for="registrationNumber" class="form-label">Registration number</label>
                                <input type="text" class="form-control" id="registrationNumber" name="registration_number" placeholder="CAC or internal registration number" value="<?php echo htmlspecialchars($registration_number); ?>">
                            </div>

                            <div class="col-md-6">
                                <label for="state" class="form-label">State</label>
                                <input type="text" class="form-control" id="state" name="state" placeholder="Example: Lagos" value="<?php echo htmlspecialchars($state); ?>" required>
                                <p class="field-error"><?php echo htmlspecialchars($errors["state"]); ?></p>
                            </div>

                            <div class="col-md-6">
                                <label for="lga" class="form-label">LGA</label>
                                <input type="text" class="form-control" id="lga" name="lga" placeholder="Enter LGA" value="<?php echo htmlspecialchars($lga); ?>" required>
                                <p class="field-error"><?php echo htmlspecialchars($errors["lga"]); ?></p>
                            </div>

                            <div class="col-12">
                                <label for="address" class="form-label">Organization address</label>
                                <textarea class="form-control" id="address" name="address" rows="3" placeholder="Enter full organization address" required><?php echo htmlspecialchars($address); ?></textarea>
                                <p class="field-error"><?php echo htmlspecialchars($errors["address"]); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>Contact Person</h3>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="contactPerson" class="form-label">Contact name</label>
                                <input type="text" class="form-control" id="contactPerson" name="contact_person" placeholder="Enter contact name" value="<?php echo htmlspecialchars($contact_person); ?>" required>
                                <p class="field-error"><?php echo htmlspecialchars($errors["contact_person"]); ?></p>
                            </div>

                            <div class="col-md-4">
                                <label for="contactRole" class="form-label">Contact role</label>
                                <input type="text" class="form-control" id="contactRole" name="contact_role" placeholder="Example: Administrator" value="<?php echo htmlspecialchars($contact_role); ?>">
                            </div>

                            <div class="col-md-4">
                                <label for="contactPhone" class="form-label">Contact phone</label>
                                <input type="tel" class="form-control" id="contactPhone" name="contact_phone" placeholder="Enter contact phone" value="<?php echo htmlspecialchars($contact_phone); ?>" required>
                                <p class="field-error"><?php echo htmlspecialchars($errors["contact_phone"]); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="hospital-pending.php" class="secondary-action">View status</a>
                        <button type="submit" name="save_hospital_onboarding" class="primary-action">Submit for verification</button>
                    </div>
                </form>
            </div>
        </section>
    </main>
</body>
</html>
