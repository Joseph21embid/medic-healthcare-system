<?php include_once __DIR__ . "/../auth/patient_onboarding_process.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Patient Profile | Medic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/patient-onboarding.css">
</head>
<body>
    <main class="onboarding-page">
        <section class="onboarding-intro">
            <nav class="brand-row">
                <a href="../dashboard/patient.php" class="brand">
                    <img src="https://placehold.co/44x44/2563EB/FFFFFF?text=M" alt="Medic logo">
                    <span>Medic</span>
                </a>
            </nav>

            <div class="intro-content">
                <span class="eyebrow">Patient onboarding</span>
                <h1>Complete your health profile before using the full dashboard.</h1>
                <p>
                    This information helps hospitals understand your basic medical background, emergency contacts,
                    allergies, and current health needs faster.
                </p>

                <div class="info-card">
                    <strong>Why this matters</strong>
                    <span>During emergency care, even simple details like blood group, allergies, and emergency contact can make response safer and faster.</span>
                </div>
            </div>
        </section>

        <section class="onboarding-form-section">
            <div class="form-shell">
                <div class="form-heading">
                    <span class="eyebrow">Step 1 of 1</span>
                    <h2>Patient profile details</h2>
                    <p>Fill in the details you know now. You can update them later from your profile page.</p>
                </div>

                <?php
                if (!empty($errors["general"])) {
                    echo "<p class=\"form-alert\">" . htmlspecialchars($errors["general"]) . "</p>";
                }
                ?>

                <form action="" method="post" class="onboarding-form">
                    <div class="form-section">
                        <h3>Personal Information</h3>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="fullName" class="form-label">Full name</label>
                                <input type="text" class="form-control" id="fullName" name="full_name" placeholder="Enter full name" value="<?php echo htmlspecialchars($full_name); ?>" required>
                                <p class="field-error"><?php echo htmlspecialchars($errors["full_name"]); ?></p>
                            </div>

                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" placeholder="Enter phone number" value="<?php echo htmlspecialchars($phone); ?>" required>
                                <p class="field-error"><?php echo htmlspecialchars($errors["phone"]); ?></p>
                            </div>

                            <div class="col-md-6">
                                <label for="gender" class="form-label">Gender</label>
                                <select class="form-select" id="gender" name="gender" required>
                                    <option value="">Select gender</option>
                                    <option value="male" <?php if ($gender == "male") { echo "selected"; } ?>>Male</option>
                                    <option value="female" <?php if ($gender == "female") { echo "selected"; } ?>>Female</option>
                                    <option value="other" <?php if ($gender == "other") { echo "selected"; } ?>>Other</option>
                                </select>
                                <p class="field-error"><?php echo htmlspecialchars($errors["gender"]); ?></p>
                            </div>

                            <div class="col-md-6">
                                <label for="dateOfBirth" class="form-label">Date of birth</label>
                                <input type="date" class="form-control" id="dateOfBirth" name="date_of_birth" value="<?php echo htmlspecialchars($date_of_birth); ?>" required>
                                <p class="field-error"><?php echo htmlspecialchars($errors["date_of_birth"]); ?></p>
                            </div>

                            <div class="col-12">
                                <label for="address" class="form-label">Home address</label>
                                <textarea class="form-control" id="address" name="address" rows="3" placeholder="Enter home address" required><?php echo htmlspecialchars($address); ?></textarea>
                                <p class="field-error"><?php echo htmlspecialchars($errors["address"]); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>Medical Information</h3>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="bloodGroup" class="form-label">Blood group</label>
                                <select class="form-select" id="bloodGroup" name="blood_group" required>
                                    <option value="">Select blood group</option>
                                    <option value="A+" <?php if ($blood_group == "A+") { echo "selected"; } ?>>A+</option>
                                    <option value="A-" <?php if ($blood_group == "A-") { echo "selected"; } ?>>A-</option>
                                    <option value="B+" <?php if ($blood_group == "B+") { echo "selected"; } ?>>B+</option>
                                    <option value="B-" <?php if ($blood_group == "B-") { echo "selected"; } ?>>B-</option>
                                    <option value="AB+" <?php if ($blood_group == "AB+") { echo "selected"; } ?>>AB+</option>
                                    <option value="AB-" <?php if ($blood_group == "AB-") { echo "selected"; } ?>>AB-</option>
                                    <option value="O+" <?php if ($blood_group == "O+") { echo "selected"; } ?>>O+</option>
                                    <option value="O-" <?php if ($blood_group == "O-") { echo "selected"; } ?>>O-</option>
                                </select>
                                <p class="field-error"><?php echo htmlspecialchars($errors["blood_group"]); ?></p>
                            </div>

                            <div class="col-md-6">
                                <label for="genotype" class="form-label">Genotype</label>
                                <select class="form-select" id="genotype" name="genotype">
                                    <option value="">Select genotype</option>
                                    <option value="AA" <?php if ($genotype == "AA") { echo "selected"; } ?>>AA</option>
                                    <option value="AS" <?php if ($genotype == "AS") { echo "selected"; } ?>>AS</option>
                                    <option value="SS" <?php if ($genotype == "SS") { echo "selected"; } ?>>SS</option>
                                    <option value="AC" <?php if ($genotype == "AC") { echo "selected"; } ?>>AC</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label for="allergies" class="form-label">Known allergies</label>
                                <textarea class="form-control" id="allergies" name="allergies" rows="3" placeholder="Example: Penicillin, peanuts, dust. Use none if not applicable."><?php echo htmlspecialchars($allergies); ?></textarea>
                            </div>

                            <div class="col-12">
                                <label for="chronicConditions" class="form-label">Chronic conditions</label>
                                <textarea class="form-control" id="chronicConditions" name="chronic_conditions" rows="3" placeholder="Example: Asthma, diabetes, hypertension. Use none if not applicable."><?php echo htmlspecialchars($chronic_conditions); ?></textarea>
                            </div>

                            <div class="col-12">
                                <label for="currentMedications" class="form-label">Current medications</label>
                                <textarea class="form-control" id="currentMedications" name="current_medications" rows="3" placeholder="List current medications and dosage if known."><?php echo htmlspecialchars($current_medications); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>Emergency Contact</h3>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="emergencyName" class="form-label">Emergency contact name</label>
                                <input type="text" class="form-control" id="emergencyName" name="emergency_contact_name" placeholder="Enter contact name" value="<?php echo htmlspecialchars($emergency_contact_name); ?>" required>
                                <p class="field-error"><?php echo htmlspecialchars($errors["emergency_contact_name"]); ?></p>
                            </div>

                            <div class="col-md-6">
                                <label for="emergencyPhone" class="form-label">Emergency contact phone</label>
                                <input type="tel" class="form-control" id="emergencyPhone" name="emergency_contact_phone" placeholder="Enter contact phone" value="<?php echo htmlspecialchars($emergency_contact_phone); ?>" required>
                                <p class="field-error"><?php echo htmlspecialchars($errors["emergency_contact_phone"]); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="../dashboard/patient.php" class="secondary-action">Skip for now</a>
                        <button type="submit" name="save_onboarding" class="primary-action">Save and continue</button>
                    </div>
                </form>
            </div>
        </section>
    </main>
</body>
</html>
