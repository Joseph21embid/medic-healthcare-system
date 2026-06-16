# Medic Project Progress And Todo

This file tracks what has been completed, what should be done next, and what should be saved for later phases.

## Completed So Far

### Project Foundation

- Set up the project inside `C:\xampp\htdocs\national-healthcare-system`.
- Switched the project direction to procedural PHP.
- Cleaned the folder structure to match procedural PHP development.
- Added project documentation files.
- Added `.gitignore` to protect sensitive files.
- Added `google.example.php` as a safe config example.

### Database Planning

- Designed the first database schema.
- Included tables for users, patients, hospitals, appointments, medical records, emergency requests, medications, and lab results.
- Added support for Google authentication through `auth_provider` and `google_id`.
- Made local password nullable for Google-created accounts.

### Authentication

- Built patient signup.
- Built hospital signup.
- Split signup into a professional account-type chooser, patient signup page, and hospital/clinic/medical firm signup page.
- Built login.
- Built logout.
- Added role-based redirects.
- Fixed the patient/hospital signup role bug.
- Added Google authentication scaffold.
- Added Google setup documentation.
- Added password visibility toggle icons.
- Tested the full authentication flow successfully.

### Frontend Pages

- Created signup page.
- Created separate patient signup and hospital signup pages.
- Created login page.
- Created patient dashboard model.
- Made patient dashboard responsive.
- Added mobile sidebar toggle for the patient dashboard.
- Added placeholder dashboard cards, quick actions, medical summary, medication schedule, appointment preview, and SOS card.
- Created patient onboarding/profile completion page.

### Code Readability

- Rewrote major backend logic in a more readable style.
- Avoided shorthand PHP where possible.
- Added a PHP shorthand guide for learning/reference.

## Current Phase

### Connect Patient Dashboard To Real Database Data

Status: in progress.

The patient dashboard is being connected to real database values instead of placeholders.

Data now being pulled from the database:

- Patient name
- Health ID
- Blood group
- Genotype
- Allergies
- Chronic conditions
- Current medications
- Emergency contact status
- Active medication count
- Next appointment, where available

## Important Project Rules And Decisions

- Patients should be identified by their NHIS number when hospitals, clinics, or medical firms need to find or verify a patient.
- Patient signup and hospital signup should remain separate pages because both account types will eventually collect different information.
- Hospital, clinic, and medical firm accounts will require extra questionnaires and verification before full dashboard access.

## Recently Completed Phase

### Patient Onboarding/Profile Completion

Goal: collect important patient information after signup so the system can build a useful patient profile and medical summary.

Information to collect:

- Full name
- Phone number
- Gender
- Date of birth
- Blood group
- Genotype
- Home address
- Allergies
- Chronic conditions
- Current medications
- Emergency contact name
- Emergency contact phone

Backend behavior:

- New patient signup now redirects to onboarding.
- Google patient signup now redirects to onboarding.
- Onboarding saves patient information to the `patients` table.
- After successful onboarding, the patient is redirected to the dashboard.

## Next Best Phase After Dashboard Database Connection

### Patient Profile View/Edit Page

Build a dedicated profile page where patients can view and update the onboarding information later.

## Things To Do Later

- Forgot password and OTP reset flow.
- Remodel/refine the patient dashboard after onboarding is working.
- Add NHIS number field and validation to patient profile/onboarding when we are ready to connect real patient identification.
- Build appointment booking module.
- Build emergency SOS module.
- Build medical records module.
- Build medications and reminders module.
- Build hospital onboarding questionnaire.
- Build hospital verification process.
- Build hospital dashboard.
- Build admin login and admin dashboard.
- Build admin hospital approval.
- Build lab results portal.
- Build pharmacy/drug locator.
- Build telemedicine module.
- Build vaccination records module.
- Build blood bank and donor registry.
- Build health analytics dashboard.
- Add final UI polish.
- Add final testing and cleanup.
- Optionally rebuild the project in OOP after the procedural version is completed.
