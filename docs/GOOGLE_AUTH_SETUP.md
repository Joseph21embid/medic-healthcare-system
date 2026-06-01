# Google Authentication Setup

This project uses Google OAuth for "Continue with Google".

## Files

```text
config/google.php
auth/google_redirect.php
auth/google_callback.php
```

## Redirect URI

Use this redirect URI in Google Cloud Console:

```text
http://localhost/national-healthcare-system/auth/google_callback.php
```

## Setup Steps

1. Go to Google Cloud Console.
2. Create or select a project.
3. Configure the OAuth consent screen.
4. Create OAuth Client credentials.
5. Choose Web application.
6. Add the redirect URI above.
7. Copy the Client ID and Client Secret.
8. Paste them into `config/google.php`.

## Signup Behavior

The signup page sends the selected role to Google authentication:

- Patient selected: creates/logs in as patient.
- Hospital selected: creates/logs in as hospital.

## Login Behavior

The login page uses Google only for existing Google-linked accounts. If the Google account does not exist yet, the user is sent back to signup.

