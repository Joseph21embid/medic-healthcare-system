# Medic Project Progress And Todo

This file tracks what has been completed, what we should build next, and what should be saved for later phases.

The roadmap is split into two levels:

- Segment: a large part of the system, such as authentication, individual onboarding, or hospital verification.
- Task: a smaller piece of work needed to complete a segment, such as signup, login, or profile edit.

## Core Project Direction

- The first version of this system is for Nigeria.
- The public-facing language should use `individuals` instead of `patients`.
- The public-facing language should use `hospitals/clinics/medical firms` instead of only `hospitals`.
- The backend/database can still use `patient` internally for now so current logic does not break.
- Individuals should eventually be identifiable by NHIS number.
- NIN identification should be added later for stronger identity verification.
- Photo verification should be added later.
- Children under 18 should eventually be registerable by a parent or guardian.
- A future access path should be planned for children without parents or guardians, but this is an advanced policy/workflow feature and should not block the basic system.
- Advanced modules such as AI telemedicine, ambulance tracking, and complex emergency response should wait until the individual and organization flows are stable.

## Completed So Far

### Segment: Project Foundation

Tasks completed:

- Set up the project inside `C:\xampp\htdocs\national-healthcare-system`.
- Switched the project direction to procedural PHP.
- Cleaned the folder structure to match procedural PHP development.
- Added project documentation files.
- Added `.gitignore` to protect sensitive files.
- Added `google.example.php` as a safe config example.
- Added a PHP shorthand guide for learning/reference.

### Segment: Database Planning

Tasks completed:

- Designed the first database schema.
- Included tables for users, patients, hospitals, appointments, medical records, emergency requests, medications, and lab results.
- Added support for Google authentication through `auth_provider` and `google_id`.
- Made local password nullable for Google-created accounts.
- Planned future use of NHIS number as the individual identifier.

### Segment: Authentication

Tasks completed:

- Built individual signup using the existing `patient` backend role.
- Built hospital/clinic/medical firm signup using the existing `hospital` backend role.
- Split signup into a professional account-type chooser, individual signup page, and hospital/clinic/medical firm signup page.
- Built login.
- Built logout.
- Added role-based redirects.
- Fixed the individual/organization signup role bug.
- Added Google authentication scaffold.
- Added Google setup documentation.
- Added password visibility toggle icons.
- Tested the full authentication flow successfully.

### Segment: Individual Onboarding

Tasks completed:

- Created individual onboarding/profile completion page.
- New individual signup now redirects to onboarding.
- Google individual signup now redirects to onboarding.
- Onboarding saves individual information to the `patients` table.
- After successful onboarding, the individual is redirected to the dashboard.

Current onboarding fields:

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

### Segment: Individual Dashboard Foundation

Tasks completed:

- Created individual dashboard model.
- Made individual dashboard responsive.
- Added mobile sidebar toggle.
- Added placeholder dashboard cards, quick actions, medical summary, medication schedule, appointment preview, and SOS card.
- Connected some dashboard values to real database data.

Data currently being pulled from the database:

- Individual name
- Health ID
- Blood group
- Genotype
- Allergies
- Chronic conditions
- Current medications
- Emergency contact status
- Active medication count
- Next appointment, where available

## Current Phase

### Segment: Individual Foundation Completion

Status: in progress.

Goal:

Finish the basic individual-side foundation before moving deeply into hospital/clinic/medical firm features.

Tasks to complete next:

- Change visible frontend wording from `patient` to `individual`. Done for the main public pages, signup flow, onboarding page, and dashboard copy.
- Keep backend/database role as `patient` for now.
- Add NHIS number to individual onboarding/profile. Done as an optional field for now.
- Display NHIS number clearly on the individual dashboard. Done in the top profile chip and dashboard stat cards.
- Add optional individual profile photo upload and gender-based default avatar. Done as first version.
- Build individual profile view/edit page. Done as a profile view page with an edit link back to the profile form.
- Make sure individual profile data can be updated after onboarding. Done through the reusable profile form.
- Improve individual dashboard layout after real profile data is connected.

## Ordered Roadmap By Segment

The order below moves from easier/foundation work to tougher work, but still puts required workflow pieces early when the project depends on them.

### 1. Segment: Individual Foundation Completion

Purpose:

Make the individual side feel complete enough for normal use.

Tasks:

- Replace visible `patient` wording with `individual`. In progress; main user-facing pages have been updated.
- Add NHIS number field to onboarding/profile. Done as an optional field for now.
- Decide if NHIS number is required immediately or can be added later by profile edit.
- Display NHIS number on the dashboard. Done.
- Allow individuals to upload/take a profile photo.
- Use a gender-based default face when no profile photo is uploaded.
- Build profile view/edit page. Done.
- Allow update of phone, address, emergency contact, allergies, conditions, and medications. Done through the reusable profile form.
- Add basic validation to profile updates.
- Add clear success/error messages.

### 2. Segment: Forgot Password And OTP

Purpose:

Finish the authentication system properly.

Tasks:

- Create forgot password page. Done as first version.
- Generate OTP securely. Done as first version.
- Store OTP and expiry time. Done using existing `users` OTP columns.
- Send OTP by email. First version uses PHP `mail()` when configured and shows local development OTP when mail is unavailable.
- Verify OTP. Done as first version.
- Allow password reset after OTP verification. Done as first version.
- Clear OTP after successful reset. Done as first version.
- Add user-friendly error messages. Done as first version.
- Replace local development OTP with production SMTP email before deployment.

### Future Segment: Organization Staff Workflow

Purpose:

Move from one organization dashboard into a proper hospital workflow with CEO/admin, receptionist, nurse, doctor, and lab roles.

Planned direction:

- Organization CEO signs up the hospital/clinic/medical firm.
- After approval, CEO receives a registration key by email.
- CEO uses that key to register staff under the organization.
- Staff roles include receptionist, nurse, doctor, and lab scientist.
- Registered staff receive their own login key by email.
- Receptionist performs NHIS lookup and places individuals into a care queue.
- Nurse dashboard handles vitals, injections, and nurse-station checks.
- Doctor dashboard handles diagnosis, possible illness, treatment, and prescriptions.
- Lab dashboard handles lab tests and lab result updates.
- Organization should register medication inventory, so prescriptions/medication offered can be selected from available stock.
- Add a dropdown of common diseases in the possible illness section, starting with common conditions that cause hospital visits in Nigeria.
- This workflow should be built after authentication is complete and before advanced AI/ambulance modules.

### 3. Segment: Hospital/Clinic/Medical Firm Onboarding

Purpose:

Make organization signup professional and controlled before giving dashboard access.

Tasks:

- Create organization onboarding questionnaire. Done as first version.
- Collect organization name, type, address, state, LGA, official email, phone number, license number, and contact person. Done as first version.
- Add organization registration/license document upload later.
- Save organization onboarding details to the database. Done after required hospital columns are added.
- Redirect new organization accounts to onboarding before dashboard. Done.
- Show pending verification page after onboarding. Done.

### 4. Segment: Organization Verification Flow

Purpose:

Prevent unverified organizations from using sensitive features.

Tasks:

- Add organization status values. Current database uses `pending`, `active`, and `suspended`; `active` means the organization has been accepted.
- Block unverified organizations from full dashboard access. Done.
- Create a basic pending approval screen. Done.
- Create admin review list for organizations.
- Create admin review list for organizations. Done as first version.
- Allow admin to approve or reject organizations. Done as first version.
- Store approval date and approved-by admin ID later.
- Show one-time congratulations message to accepted organizations on first dashboard access. Done with `approval_seen`.

### 5. Segment: Basic Organization Dashboard

Purpose:

Give approved hospitals/clinics/medical firms a usable starting dashboard.

Tasks:

- Create responsive organization dashboard. Done as first version.
- Show organization verification status. Done as accepted organization profile summary.
- Add dashboard cards for individuals, appointments, records, and emergency cases. Done as first version.
- Add navigation for individual lookup, appointments, records, and settings. Done as first version.
- Keep advanced organization features locked until the basics are stable.

### 6. Segment: Individual Lookup By NHIS Number

Purpose:

Let approved organizations identify individuals safely.

Tasks:

- Build NHIS search form for approved organizations. Done as first version.
- Search individuals by NHIS number. Done as first version.
- Show limited individual profile summary. Done as first version.
- Decide what data organizations can view without extra permission.
- Add access logging later so the system records which organization viewed an individual.
- Add stronger consent/permission rules later.

### 7. Segment: Individual Medical Records

Purpose:

Start turning the system into a real healthcare records platform.

Tasks:

- Create records table/pages if more structure is needed.
- Let organizations add basic visit notes. Done as first version through NHIS lookup.
- Let individuals view their records. Done as first version through the individual medical records page.
- Add diagnosis, prescriptions, lab notes, and doctor/organization info. Diagnosis, treatment notes, prescription notes, doctor/staff name, visit date, individual, and organization are saved in first version.
- Add walk-in fields for nurse-station vitals, symptoms, possible illness, result status, result summary, and medication status. Done in code and added to the local database.
- Let organizations update lab/result summary and medication progress after the first visit record is created. Done as first version through the organization medical records page.
- Show medication-completed follow-up checkup reminder on the individual side. Done as first version.
- Show last checkup/result summary more prominently on the individual dashboard later.
- Add date filters.
- Add print/download option. Done as browser print/save PDF for individual medical summary and organization visit records.

### Stage-One Scenario: Walk-In Individual Care Flow

Purpose:

Prove the first real hospital workflow for a walk-in individual before adding advanced modules.

Tasks:

- Approved organization searches an individual by NHIS number. Done as first version.
- Organization views a limited individual summary before care starts. Done as first version.
- Nurse-station vitals are recorded, including heart rate, blood pressure, temperature, and weight. Done as first version.
- Organization records symptoms or complaint. Done as first version.
- Doctor/staff records possible illness before lab results are ready. Done as first version.
- Individual sees possible illness on their own side. Done as first version.
- Individual sees result status as `Awaiting result` until the organization updates it. Done as first version.
- Organization later updates result summary and medication status. Done as first version through the organization medical records page.
- Individual sees medication status from not started to in progress to completed. Done as first version.
- When medication is completed, individual sees a reminder to go for another checkup and confirm vitals. Done as first version.

### 8. Segment: Appointment Booking

Purpose:

Allow individuals and organizations to manage appointments.

Tasks:

- Build appointment request form for individuals.
- Let individuals select organization, date, preferred time, and reason. Done as first version.
- Let organizations accept, reject, or reschedule. Done as first version.
- Show upcoming appointments on individual dashboard. Done through existing dashboard data helper.
- Show organization appointment queue. Done as first version.
- Add department/service selection. Done as first version with `service_area`.
- Add appointment cancellation by individuals. Done for pending and approved appointments.
- Add organization appointment filtering by status. Done as first version.

### Stage-One Scenario: Online Appointment To Care Flow

Purpose:

Prove the second stage-one scenario where an individual books online, arrives for care, and the organization continues the same medical record flow.

Tasks:

- Convert approved appointment into a visit record.
- Mark whether the visit started from `walk-in` or `online appointment`. Done through `visit_type`.
- Reuse nurse-station vitals fields. Done after conversion through the medical records update page.
- Reuse possible illness, awaiting result, result summary, and medication status. Done after conversion through the medical records update page.
- Show appointment-based records on the individual medical records page. Done as first version.
- Prevent the same appointment from being converted twice. Done through `appointment_id` link.

### 9. Segment: Medication And Reminder Module

Purpose:

Make individual care management more useful.

Tasks:

- Add medication list page.
- Add medication name, dosage, frequency, start date, and end date.
- Show active medications on dashboard.
- Add simple reminder display.
- Allow individuals to mark medication as active, completed, or stopped. Done as first version.
- Save advanced notifications for later.

### Future Advanced Segment: AI Vitals Advice Sidebar

Purpose:

Add intelligent, supportive advice when routine checkup vitals are outside normal ranges.

Tasks:

- Define safe normal-range checks for vitals such as blood pressure, temperature, heart rate, and weight.
- Add an advice sidebar on the individual side when vitals need attention.
- Use an AI API later to explain possible concerns in simple language.
- Make the advice clearly educational and not a replacement for a medical professional.
- Keep this feature for a later advanced phase after the core individual and organization workflows are stable.

### 10. Segment: Emergency SOS Foundation

Purpose:

Build a simple emergency request flow before attempting advanced ambulance tracking.

Tasks:

- Create emergency request form/button. Done as first version.
- Save emergency request in database. Done as first version.
- Allow SOS for self or for another person. Done as first version.
- Self SOS is treated as immediate response by setting the request to `responding`. Done as first version.
- Third-party SOS can include victim details, reporter note, location, and scene photo. Done as first version.
- Allow organization to request scene photo evidence before dispatching a care unit. Done as first version.
- Capture emergency contact and basic location text/manual address. Done as first version.
- Show request status. Done as first version.
- Let organization view emergency requests. Done as first version.
- Let organization accept, dispatch/respond, resolve, or cancel SOS requests. Done as first version.
- Save live GPS, maps, and ambulance tracking for a later advanced phase.

### 11. Segment: Admin Foundation

Purpose:

Give the system owner control over organizations and critical data.

Tasks:

- Build admin login. First version done with configured demo admin credentials.
- Build admin dashboard. Placeholder exists and is protected by configured admin email.
- Show pending organizations.
- Approve/reject organizations.
- View system counts and recent activity.
- Add admin management later.

### 12. Segment: Nigerian Identity And Verification

Purpose:

Strengthen trust and identity after the basic flow works.

Tasks:

- Add NIN field for adults.
- Add NHIS number field and validation rules.
- Add photo upload/photo verification.
- Add guardian registration for individuals under 18.
- Add parent/guardian NIN and contact information.
- Plan special access workflow for children without parents or guardians.
- Decide which identity fields are required at signup and which can be completed later.

### 13. Segment: Advanced Individual Modules

Purpose:

Add high-value features only after the core system is stable.

Tasks:

- AI telemedicine assistant.
- Telemedicine chat/video planning.
- Ambulance tracking.
- Advanced emergency dispatch flow.
- Vaccination records.
- Lab results portal.
- Pharmacy/drug locator.
- Blood bank and donor registry.
- Health analytics dashboard.

### 14. Segment: Final UI, Testing, And Cleanup

Purpose:

Prepare the system for presentation and real review.

Tasks:

- Final UI polish across all pages.
- Mobile responsiveness pass.
- Form validation pass.
- Security cleanup.
- Remove unused files.
- Confirm secrets are not committed.
- Test individual signup/login/onboarding/dashboard.
- Test organization signup/onboarding/pending approval/dashboard.
- Test admin approval flow.
- Prepare demo data.
- Prepare project documentation.

## Immediate Next Best Step

Start with Segment 1: Individual Foundation Completion.

First task:

- Change visible wording from `patient` to `individual` across public-facing pages while keeping the backend role as `patient`.

Second task:

- Add NHIS number to individual onboarding/profile and dashboard.

## Saved For Later

- Full NIN verification integration.
- Photo verification workflow.
- Guardian registration for under-18 individuals.
- Special access workflow for children without parents or guardians.
- AI telemedicine.
- Ambulance tracking.
- Advanced emergency dispatch.
- OOP rebuild after the procedural version is completed.
