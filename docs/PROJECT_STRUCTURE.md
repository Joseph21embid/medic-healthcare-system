# Project Folder Structure

The project uses procedural PHP with organized folders. This is close to the Outreach project style, but cleaner and easier to scale.

```text
national-healthcare-system/
|-- auth/
|-- config/
|-- dashboard/
|-- database/
|-- docs/
|-- includes/
|-- modules/
|   |-- analytics/
|   |-- appointments/
|   |-- emergency/
|   |-- emr/
|   |-- hospitals/
|   |-- laboratory/
|   |-- patients/
|   |-- pharmacy/
|   `-- telemedicine/
`-- public/
    `-- assets/
        |-- css/
        |-- js/
        `-- images/
```

## Folder Roles

`auth/`
Stores backend scripts for registration, login, logout, password reset, and role-based access.

`config/`
Stores database connection and project configuration.

`dashboard/`
Stores dashboard pages for different user roles such as patient, hospital admin, doctor, emergency responder, and system administrator.

`database/`
Stores SQL files for creating tables and sample records.

`docs/`
Stores project notes, module descriptions, design decisions, and thesis-support documentation.

`includes/`
Stores reusable procedural files such as header, footer, sidebar, session checks, validation helpers, and common functions.

`modules/`
Stores feature pages and backend handlers for each major healthcare module.

`public/`
This is the browser-facing folder. It contains the main entry file and public assets like CSS, JavaScript, and images.

## Development Style

The system will not be over-engineered. Each module should be understandable, testable, and easy to explain.

Pages may include backend logic files the same way Outreach did, for example:

```php
<?php include "../auth/login_process.php"; ?>
```

Shared layout files may be included like this:

```php
<?php include "../includes/header.php"; ?>
<?php include "../includes/sidebar.php"; ?>
<?php include "../includes/footer.php"; ?>
```

The first version will focus on:

- Authentication
- User roles
- Dashboard layout
- Patient records
- Hospital records
- Appointment booking
- Emergency SOS workflow

## Why Procedural PHP First?

Procedural PHP is the best fit for the first build because it allows the project to move quickly while staying understandable. Once the system is complete, the same modules can be refactored into OOP later.
