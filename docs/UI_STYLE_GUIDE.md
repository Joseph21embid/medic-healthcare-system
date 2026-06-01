# UI Style Guide

The visual style should feel like a serious healthcare platform: calm, clear, trustworthy, and easy to scan.

## Design Goals

- Clean enough for an academic defense.
- Simple enough to build quickly with Bootstrap.
- Professional enough to look like a real healthcare information system.
- Responsive on mobile, tablet, and desktop.
- Dashboard-first, not landing-page-first.

## Recommended Color Scheme

### Primary Colors

- Primary Blue: `#2563EB`
- Deep Navy: `#0F172A`
- Medical Teal: `#0F766E`
- Health Green: `#16A34A`

### Supporting Colors

- Page Background: `#F8FAFC`
- Card Background: `#FFFFFF`
- Border Color: `#E2E8F0`
- Muted Text: `#64748B`
- Main Text: `#1E293B`

### Status Colors

- Success: `#16A34A`
- Warning: `#F59E0B`
- Error/Emergency: `#DC2626`
- Info: `#0284C7`

## How Colors Should Be Used

Primary blue should be used for normal system actions such as saving records, logging in, and opening dashboards.

Medical teal should be used for healthcare-related highlights such as patient records, telemedicine, and hospital information.

Green should be used for successful states such as completed appointments, available doctors, and active records.

Red should be reserved for emergency actions such as SOS, urgent alerts, failed operations, and critical medical warnings.

## Layout Direction

The application should use:

- A fixed or sticky sidebar on desktop dashboards.
- A top navigation bar for mobile screens.
- Compact dashboard cards.
- Clear forms with labels and helpful error messages.
- Tables for records such as patients, hospitals, appointments, and emergency requests.
- Badges for statuses.
- Modals only when they make the workflow faster.

## Bootstrap Usage

Bootstrap will handle:

- Grid layout
- Forms
- Buttons
- Tables
- Modals
- Alerts
- Responsive behavior

Custom CSS will handle:

- Project colors
- Dashboard spacing
- Sidebar styling
- Healthcare-specific polish
- Small interface details Bootstrap does not cover well

