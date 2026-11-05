# Development Plan — Provider Credentialing & Enrollment CRM

> Source: [`requirement.md`](requirement.md)
> Stack: **Laravel 13 + Filament 5** (admin panel), MySQL/PostgreSQL, Redis.
> Approach: **phase by phase** — each phase ends with something working and demoable. Do not start the next phase until the current phase's acceptance checklist passes.

---

## Ground rules

- One phase at a time; each phase gets its own branch (e.g. `phase/03-providers`) merged after review.
- Every phase ends with: migrations run, tests green (`composer test`), demo data intact, sidebar updated.
- Encrypted fields, private files, and audit logging are not "polish" — they are built in from the phase where the field first appears.
- "Coming soon" modules stay as placeholder pages until their phase; remove the placeholder badge only when the real module lands.
- No copying of CAQH / DataSpring logos, text, or screens.

---

## Phase overview

| Phase | Name | Deliverable | Depends on |
|---|---|---|---|
| 1 | Foundation & Shell | App shell: theme, nav groups, placeholder pages | — |
| 2 | Data Layer | All tables, models, relations, factories/seeders | 1 |
| 3 | Providers + Practices | Core module (8 tabs, relation managers, CSV) | 2 |
| 4 | Documents | Document management, signed downloads, checklist | 3 |
| 5 | Security Group | Users & Roles, Audit Logs, Login Activity | 2 |
| 6 | Dashboard + Settings | **MVP demo complete** | 3, 4, 5 |
| 7 | Enrollments, Attestations, Tasks | Credentialing modules live | 3, 4 |
| 8 | Payers, Reports, Notifications | Full sidebar complete | 7 |
| 9 | Polish & Demo | Shippable demo | all |

---

## Phase 1 — Foundation & Shell ✅ Complete

**Goal:** the app looks and feels like the target product; every sidebar item is clickable.

Tasks:
- [x] Verify environment (`php -v`, `composer -V`), install Laravel Boost, read generated guidelines.
- [x] Confirm Filament 5 admin panel (`AdminPanelProvider`) boots; fix login page branding.
- [x] Custom Filament theme: brand color, logo in sidebar, rounded cards, consistent spacing, **dark mode enabled**.
- [x] Build the sidebar navigation exactly per `requirement.md` §1 (14 items, groups, heroicon names, order).
- [x] Add count-badge support on nav items (red overdue attestations, etc.) — wire in Phase 6, stub now.
- [x] Create placeholder "Coming soon" pages for: Enrollments, Attestations, Tasks, Payers, Reports, Notifications (card: *"This module is in the roadmap"* + sidebar badge).
- [x] Register nav icons and route names for all items.

**Acceptance:**
- [x] All 14 sidebar items render in order with correct groups/icons; 6 show "Coming soon" badge (`SidebarNavigationTest`, 10/10 green).
- [x] Dark mode toggles cleanly; brand theme applied (Blue primary, logo, `rounded-2xl` sections).
- [x] `composer test` green.

**Notes:** `User` model now implements `FilamentUser::canAccessPanel()` (required by Filament 5 outside `local` env). MVP modules (Providers, Practices, Documents, Users & Roles, Audit Logs, Login Activity, Settings) show "under construction" placeholders until their phase; roadmap modules show the "Soon" badge.

---

## Phase 2 — Data Layer

**Goal:** the complete database schema with realistic demo data (per `requirement.md` §14, §15).

Tasks:
- [ ] Install `spatie/laravel-permission` and `spatie/laravel-activitylog`.
- [ ] Migrations for: `practices`, `providers`, `provider_licenses`, `provider_locations`, `provider_work_history`, `documents`, `payers`, `enrollments`, `attestations`, `tasks`, `notes`, `login_events`, `settings` (+ permission tables, `activity_log`).
- [ ] Eloquent models with relations:
  - Practice hasMany Provider
  - Provider hasMany License, Location, WorkHistory, Document, Enrollment, Attestation, Note
  - Payer hasMany Enrollment
  - User (specialist) hasMany Provider (assigned)
- [ ] Casts: `date_of_birth`, `ssn_last4`, DEA `number`, practice `tax_id` → **encrypted**; `attested_at`/`due_at` dates; soft deletes on providers.
- [ ] Validation rules at model/rule level: NPI = 10 digits unique, dates, enums (status/stage/type).
- [ ] Factories + seeders per §15: 5 practices, 35 providers, ~120 documents (valid/expiring/expired/missing mix), 10 payers, ~60 enrollments across stages, attestation dates spread green/amber/red, 5 users (one per role), 100+ audit entries.

**Acceptance:**
- `php artisan migrate:fresh --seed` runs clean.
- Seed produces dashboard-ready data (overdue, due-soon, and current items all present).
- Encrypted attributes round-trip through casts.

---

## Phase 3 — Providers + Practices

**Goal:** full provider management (the core module, `requirement.md` §3, §4).

Tasks:
- [ ] `PracticeResource`: form/table (name, encrypted tax_id, type, address, contact, status), provider count column, hasMany relation.
- [ ] `ProviderResource` list table: full name, NPI, specialty, practice, status badge, attestation due date (green >30d / amber ≤30d / red overdue), documents-complete progress bar, assigned specialist.
- [ ] Filters: status, specialty, practice, attestation state, assigned specialist, state of license.
- [ ] Detail page — 8 tabs per §3.2:
  1. Basic Info (incl. encrypted DOB, ssn_last4)
  2. Specialties (taxonomy, board certification)
  3. Licenses & IDs (repeater/related `provider_licenses`, encrypted DEA number)
  4. Practice Locations (`provider_locations`)
  5. Education & Work History (`provider_work_history`)
  6. Documents → relation manager (full UI lands Phase 4, stub list now)
  7. Enrollments → relation manager (placeholder until Phase 7)
  8. Notes & Activity (rich notes + provider-scoped audit log)
- [ ] Actions: create, edit, soft delete, restore, bulk assign specialist, bulk change status, **Mark attested** (sets `attested_at`, `due_at = +120 days`), export CSV, import CSV.
- [ ] Policies: view/create/update/delete per role (wired to permissions created Phase 5).

**Acceptance:**
- CRUD works for both resources; all 8 tabs render with correct data.
- CSV import/export round-trips; bulk actions work.
- "Mark attested" updates due date correctly.

---

## Phase 4 — Documents

**Goal:** document management with security (`requirement.md` §5).

Tasks:
- [ ] `DocumentResource` + relation manager on Provider: provider, type (8 types), file (private disk `FileUpload`), issued_at, expires_at, status, uploaded_by, notes.
- [ ] Auto status: **Expired** if past, **Expiring** if within 60 days (window configurable later in Settings), else Valid; Missing/Under Review manual.
- [ ] Per-provider **required documents checklist** — missing types shown as red rows.
- [ ] Private storage + **signed URL** downloads; every view/download written to audit log.
- [ ] Filters: type, status, expiring in 30/60/90 days, provider, practice.

**Acceptance:**
- Expiry status recomputes correctly; red missing rows appear for incomplete providers.
- Files are not publicly accessible; signed download works and is audited.

---

## Phase 5 — Security Group

**Goal:** the Security section works (`requirement.md` §12).

Tasks:
- [ ] **Users & Roles**
  - [ ] `UserResource`: name, email, password, role, is_active, last_login_at, 2FA flag.
  - [ ] Roles: Super Admin, Admin, Credentialing Manager, Specialist, Read-only (spatie permissions).
  - [ ] Policies on every resource: view/create/update/delete per role.
  - [ ] 2FA enforcement for Admin roles (Filament built-in / Fortify TOTP).
- [ ] **Audit Logs**
  - [ ] ActivityResource: read-only table, filters (user, model, event, date range), **diff view** of changed fields.
  - [ ] Log model events with old/new values, user, IP, timestamp + document view/download + login/logout.
- [ ] **Login Activity**
  - [ ] `login_events` table + listeners on Login/Logout/Failed events.
  - [ ] Page with failed attempts in red + "last 7 days" summary card.
- [ ] **Security baseline:** rate-limited login, configurable session timeout, password rules, row-level scoping (team-assigned providers) where role requires it.

**Acceptance:**
- Each role sees exactly its permitted resources/actions.
- Audit diff view shows old → new values; login events captured including failures.
- 2FA enforced for Admin role.

---

## Phase 6 — Dashboard + Settings → **MVP demo complete**

**Goal:** the §2 dashboard and §13 settings; demo-ready MVP (5 working modules).

Dashboard tasks:
- [ ] Stats widgets: Total Providers (± vs last month), Attestations Due 30d, Overdue (red), Documents Expiring 60d, Open Enrollments — **each card clickable** with pre-applied query-string filters.
- [ ] Charts: Providers by Status (doughnut), Enrollments by Stage (bar).
- [ ] Tables: Expiring Documents (next 10), My Open Tasks, Recent Activity feed (latest 10 audit entries).
- [ ] Quick actions: Add Provider, Upload Document, Import Providers (CSV).
- [ ] Global search: providers by name, NPI, email.
- [ ] Sidebar count badges wired (overdue attestations red, etc.).

Settings tasks:
- [ ] `settings` table (key, value JSON) or spatie/laravel-settings.
- [ ] Settings page tabs: General, Attestation (cycle 120d, reminders 30/14/7), Documents (required types per provider type, 60d window), Notifications, Security (session timeout, enforce 2FA, password policy).

**Acceptance:**
- Every dashboard widget links to the filtered module it advertises.
- Settings persist and are read by the modules that use them (attestation cycle, expiry window).
- **MVP walkthrough possible end-to-end: login → dashboard → provider → document → audit trail.**

---

## Phase 7 — Enrollments, Attestations, Tasks

**Goal:** credentialing roadmap modules (`requirement.md` §6–§8).

Tasks:
- [ ] `PayerResource` basics first (name, type, portal_url, contact, requirements checklist) — needed as FK for enrollments. *(Full Payer module is Phase 8.)*
- [ ] `EnrollmentResource`: provider, payer, stage (Intake → Verification → Submitted → Approved → Denied), submitted/approved dates, effective_date, reference_no, assigned user, notes. Table + optional Kanban-by-stage. Stage change creates a task + audit entry.
- [ ] `AttestationResource`: provider, attested_at, due_at (+120d from settings), status (Current/Due Soon/Overdue), attested_by.
  - [ ] **Daily scheduler job**: recompute statuses; send reminders at 30, 14, 7 days before due and when overdue.
- [ ] `TaskResource`: title, related provider/enrollment, assigned user, due_at, priority, status.
- [ ] Replace their "Coming soon" placeholders; remove sidebar badges.

**Acceptance:**
- Stage change on enrollment generates task + audit entry.
- Scheduler command runs via `php artisan schedule:test` and flips attestation statuses/sends reminders.
- Dashboard "Open Enrollments" and attestation stats now show real data.

---

## Phase 8 — Payers, Reports, Notifications

**Goal:** complete the full §1 sidebar.

Tasks:
- [ ] **Payers:** finish `PayerResource` (full fields + requirements checklist), remove placeholder.
- [ ] **Reports** (custom page): turnaround time per payer, approval rate, expiring items, workload per specialist; CSV/XLSX export.
- [ ] **Notifications:** in-app list + preferences (email, in-app) for attestation, expiry, and task events; email sender settings from Phase 6.
- [ ] Replace remaining placeholders; remove their badges.

**Acceptance:**
- All 14 sidebar items are real modules — no placeholders remain.
- Reports export downloads a file with correct numbers (spot-check against seed data).
- Notification preferences save and gate outbound email.

---

## Phase 9 — Polish & Demo

**Goal:** the "looks advanced" bar (`requirement.md` §16).

Tasks:
- [ ] Visual pass: badges, progress bars, rounded cards, spacing consistency, dark-mode audit of every page.
- [ ] Empty states with icon + call-to-action on every list page.
- [ ] Sidebar count badges verified against live data.
- [ ] Performance pass: eager-load relations, index filters/sort columns.
- [ ] Full test suite: feature tests per resource, policy tests per role, signed-URL and audit tests.
- [ ] Demo walkthrough script + README (setup, roles, seed data overview).
- [ ] Final `composer setup` from-scratch verification.

**Acceptance:**
- Fresh clone → `composer setup` → login as each seeded role → full walkthrough with zero errors.
- `composer test` green; no placeholder pages left; dark mode clean everywhere.

---

## Out of scope (MVP)

OCR, exclusion screening (OIG/SAM), primary source verification automation, client/provider self-service portal, directory accuracy outreach, e-signature, live CAQH or payer integrations.

---

## Suggested cadence

If working ~1 day per checkpoint: **Phases 1–2 = Day 1 setup**, **3–4 = Day 2 core**, **5–6 = Day 3 MVP demo**, **7–8 = Day 4 roadmap modules**, **9 = Day 5 polish**. Phases can be resequenced (e.g. 5 before 4) but never skipped — each builds on the prior phase's schema and policies.
