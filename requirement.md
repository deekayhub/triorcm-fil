
# Provider Credentialing & Enrollment CRM: Requirements Document

Stack: Laravel (latest stable) + Filament v3 admin panel, MySQL/PostgreSQL, Redis.
Purpose: internal CRM to manage healthcare providers, their credentials, documents, payer enrollments and attestation deadlines. Inspired by the CAQH / DataSpring provider data workflow, with our own design and wording.
Goal of this build: a polished MVP demo with seeded data, 5 working modules plus "Coming soon" modules in the sidebar.

---

## 1. Sidebar structure (Filament navigation groups)

| Order | Group | Item | Route / Filament Resource | Icon (heroicon) | MVP status |
|---|---|---|---|---|---|
| 1 | (none) | Dashboard | /admin | heroicon-o-home | Working |
| 2 | Provider Management | Providers | /admin/providers (ProviderResource) | heroicon-o-user-group | Working |
| 3 | Provider Management | Practices | /admin/practices (PracticeResource) | heroicon-o-building-office | Working (basic) |
| 4 | Credentialing | Documents | /admin/documents (DocumentResource) | heroicon-o-document-text | Working |
| 5 | Credentialing | Enrollments | /admin/enrollments (EnrollmentResource) | heroicon-o-clipboard-document-check | Coming soon (or basic) |
| 6 | Credentialing | Attestations | /admin/attestations (AttestationResource) | heroicon-o-check-badge | Coming soon (or basic) |
| 7 | Credentialing | Tasks | /admin/tasks (TaskResource) | heroicon-o-list-bullet | Coming soon |
| 8 | Network | Payers | /admin/payers (PayerResource) | heroicon-o-building-library | Coming soon |
| 9 | Insights | Reports | /admin/reports (custom page) | heroicon-o-chart-bar | Coming soon |
| 10 | Insights | Notifications | /admin/notifications (custom page) | heroicon-o-bell | Coming soon |
| 11 | Security | Users & Roles | /admin/users (UserResource) | heroicon-o-shield-check | Working |
| 12 | Security | Audit Logs | /admin/audit-logs (ActivityResource) | heroicon-o-clock | Working |
| 13 | Security | Login Activity | /admin/login-activity (custom page) | heroicon-o-key | Working (basic) |
| 14 | Settings | Settings | /admin/settings (custom page) | heroicon-o-cog-6-tooth | Working (basic) |

"Coming soon" items: show a Filament page with a placeholder card ("This module is in the roadmap") and a badge on the sidebar item.

---

## 2. Dashboard

Route: /admin. Purpose: one screen showing what needs attention today. Every card/widget is clickable and jumps to the matching module with filters pre-applied (query string filters).

| Widget | Shows | Click goes to |
|---|---|---|
| Stats: Total Providers | count, +/- vs last month | Providers (no filter) |
| Stats: Attestations Due in 30 days | count | Providers filtered by attestation_due_at <= today+30 |
| Stats: Overdue Attestations | count (red) | Providers filtered by attestation status = overdue |
| Stats: Documents Expiring in 60 days | count | Documents filtered by expires_at <= today+60 |
| Stats: Open Enrollments | count | Enrollments filtered by status != approved |
| Chart: Providers by Status | doughnut (Active, Pending, Inactive) | Providers filtered by status |
| Chart: Enrollments by Stage | bar (Intake, Verification, Submitted, Approved, Denied) | Enrollments filtered by stage |
| Table: Expiring Documents | next 10 documents by expiry | Document edit page |
| Table: My Open Tasks | tasks assigned to current user | Tasks |
| Feed: Recent Activity | latest 10 audit log entries | Audit Logs |

Quick actions (header buttons): Add Provider, Upload Document, Import Providers (CSV).
Global search (Filament): searches providers by name, NPI, email.

---

## 3. Module: Providers

Resource: ProviderResource. List view with search, filters, bulk actions, CSV import/export. Detail page uses tabs.

### 3.1 Table columns
Full name, NPI, specialty, practice, status (badge), attestation due date (colored: green >30d, amber <=30d, red overdue), documents complete % (progress), assigned specialist.

Filters: status, specialty, practice, attestation state, assigned specialist, state of license.

### 3.2 Profile tabs and fields

**Tab 1: Basic Info**
- first_name, middle_name, last_name, suffix
- date_of_birth (encrypted)
- gender
- ssn_last4 (encrypted)
- npi (10 digits, unique)
- caqh_id (optional, string)
- primary_email, phone
- status (Active, Pending, Inactive)
- assigned_user_id (specialist)
- practice_id

**Tab 2: Specialties**
- primary_specialty, secondary_specialty
- taxonomy_code
- board_certified (bool), board_name, certification_date, expiry_date

**Tab 3: Licenses and IDs** (repeater / related table `provider_licenses`)
- type (State License, DEA, CDS, Board Cert)
- number (encrypted for DEA)
- state, issued_at, expires_at, status

**Tab 4: Practice Locations** (`provider_locations`)
- address_line1, address_line2, city, state, zip
- phone, fax, is_primary
- accepting_new_patients (bool)

**Tab 5: Education and Work History** (`provider_work_history`)
- type (Education, Training, Employment)
- institution, from_date, to_date, notes

**Tab 6: Documents** (relation manager to Documents)

**Tab 7: Enrollments** (relation manager to Enrollments)

**Tab 8: Notes and Activity**
- notes (rich text, author, timestamp) and that provider's audit log

### 3.3 Actions
Create, edit, soft delete, restore, assign specialist (bulk), change status (bulk), export CSV, import CSV, "Mark attested" (sets attested_at, due_at = +120 days).

---

## 4. Module: Practices

Resource: PracticeResource.
Fields: name, tax_id (encrypted), type (Solo, Group, Hospital), address, city, state, zip, phone, email, contact_person, status.
Relation: has many Providers. Table shows provider count.

---

## 5. Module: Documents

Resource: DocumentResource (also shown as a relation manager on Provider).

Fields: provider_id, type (Medical License, DEA Certificate, CDS Certificate, W-9, Malpractice Insurance, Board Certificate, CV, Other), file (private disk, FileUpload), issued_at, expires_at, status (Valid, Expiring, Expired, Missing, Under Review), uploaded_by, notes.

Rules:
- Status auto-computes from expires_at (Expired if past, Expiring if within 60 days).
- Per-provider "required documents" checklist; missing types show as red rows.
- Files stored on a private disk; download via signed URL; every view/download is written to the audit log.

Filters: type, status, expiring in 30/60/90 days, provider, practice.

---

## 6. Module: Enrollments (basic or Coming soon)

Fields: provider_id, payer_id, stage (Intake, Verification, Submitted, Approved, Denied), submitted_at, approved_at, effective_date, reference_no, assigned_user_id, notes.
Views: table plus Kanban-by-stage (optional plugin). Stage change creates a task and an audit entry.

## 7. Module: Attestations (basic or Coming soon)

Fields: provider_id, attested_at, due_at (attested_at + 120 days), status (Current, Due Soon, Overdue), attested_by.
Scheduler job runs daily: marks status, sends reminders at 30, 14 and 7 days before due and when overdue.

## 8. Module: Tasks (Coming soon)
Fields: title, related (provider or enrollment), assigned_user_id, due_at, priority, status.

## 9. Module: Payers (Coming soon)
Fields: name, type, portal_url, contact_email, contact_phone, notes, requirements checklist.

## 10. Module: Reports (Coming soon)
Planned: turnaround time per payer, approval rate, expiring items, workload per specialist, CSV/XLSX export.

## 11. Module: Notifications (Coming soon)
In-app list plus preferences (email, in-app) for attestation, expiry and task events.

---

## 12. Security group (working in MVP)

### 12.1 Users & Roles
- Package: spatie/laravel-permission with filament-shield (or manual policies).
- Roles: Super Admin, Admin, Credentialing Manager, Specialist, Read-only.
- User fields: name, email, password, role, is_active, last_login_at, two_factor enabled.
- Policies: every resource has view/create/update/delete permissions per role.
- 2FA: Filament's built-in or Laravel Fortify TOTP; required for Admin roles.

### 12.2 Audit Logs
- Package: spatie/laravel-activitylog.
- Logs: model created/updated/deleted with old and new values, user, IP, timestamp; also document view/download and login/logout.
- UI: read-only table with filters (user, model, event, date range) and a diff view of changed fields.

### 12.3 Login Activity
- Table `login_events`: user_id, ip, user_agent, status (success/failed), created_at.
- Hook into Laravel Login, Logout and Failed events. Show failed attempts in red, with a "last 7 days" summary card.

### 12.4 Security baseline
- Encrypted casts for DOB, SSN last4, DEA number, tax ID.
- Rate limiting on login, session timeout (configurable), password rules.
- Private file storage, signed URLs, no public document links.
- Row-level scoping: users only see providers assigned to their teams if the role requires it.

---

## 13. Settings

Page: /admin/settings. Tabs:
- General: company name, logo, support email, timezone.
- Attestation: cycle length (default 120 days), reminder days (30, 14, 7).
- Documents: required document types per provider type, expiry warning window (default 60 days).
- Notifications: email sender, enable/disable per event.
- Security: session timeout, enforce 2FA, password policy.
Store in a `settings` table (key, value JSON), e.g. via spatie/laravel-settings.

---

## 14. Data model (MVP tables)

users, roles, permissions, practices, providers, provider_licenses, provider_locations, provider_work_history, documents, payers, enrollments, attestations, tasks, notes, activity_log, login_events, settings.

Key relations:
- Practice hasMany Provider
- Provider hasMany License, Location, WorkHistory, Document, Enrollment, Attestation, Note
- Payer hasMany Enrollment
- User (specialist) hasMany Provider (assigned)

---

## 15. Seed data for the demo

Factories and seeders: 5 practices, 35 providers, ~120 documents (mix of valid, expiring, expired, missing), 10 payers, ~60 enrollments across stages, attestation dates spread so the dashboard shows green, amber and red items, 5 users (one per role), 100+ audit log entries.

---

## 16. Design guidelines (for the "looks advanced" goal)

- Filament custom theme: brand color, logo in sidebar, rounded cards, consistent spacing.
- Collapsible sidebar with navigation groups, icons and count badges (e.g. overdue attestations in red).
- Dark mode enabled.
- Colored status badges everywhere; progress bars for document completeness.
- Dashboard widgets with charts and clickable cards.
- Empty states with an icon and a call-to-action.
- Do not copy CAQH / DataSpring logos, text or screens.

---

## 17. Build order (1-day plan)

1. Install Filament, create admin panel, theme, navigation groups and placeholder "Coming soon" pages.
2. Migrations, models, factories, seeders.
3. ProviderResource with tabs and relation managers.
4. DocumentResource with status logic and signed downloads.
5. Dashboard widgets with click-through filters.
6. Users & Roles, Audit Logs, Login Activity.
7. Settings page.
8. Polish: theme, badges, empty states, demo walkthrough.

## 18. Out of scope for the MVP
OCR, exclusion screening (OIG/SAM), primary source verification automation, client/provider self-service portal, directory accuracy outreach, e-signature, live CAQH or payer integrations.