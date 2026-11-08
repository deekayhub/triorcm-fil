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

## Phase 2 — Data Layer ✅ Complete

**Goal:** the complete database schema with realistic demo data (per `requirement.md` §14, §15).

Tasks:
- [x] Install `spatie/laravel-permission` and `spatie/laravel-activitylog`.
- [x] Migrations for: `practices`, `providers`, `provider_licenses`, `provider_locations`, `provider_work_history`, `documents`, `payers`, `enrollments`, `attestations`, `tasks`, `notes`, `login_events`, `settings` (+ permission tables, `activity_log`).
- [x] Eloquent models with relations:
  - Practice hasMany Provider
  - Provider hasMany License, Location, WorkHistory, Document, Enrollment, Attestation, Note
  - Payer hasMany Enrollment
  - User (specialist) hasMany Provider (assigned)
- [x] Casts: `date_of_birth`, `ssn_last4`, DEA `number`, practice `tax_id` → **encrypted**; `attested_at`/`due_at` dates; soft deletes on providers.
- [x] Validation rules at model/rule level: NPI = 10 digits unique, dates, enums (status/stage/type).
- [x] Factories + seeders per §15: 5 practices, 35 providers, ~120 documents (valid/expiring/expired/missing mix), 10 payers, ~60 enrollments across stages, attestation dates spread green/amber/red, 5 users (one per role), 100+ audit entries.

**Acceptance:**
- [x] `php artisan migrate:fresh --seed` runs clean.
- [x] Seed produces dashboard-ready data (overdue, due-soon, and current items all present).
- [x] Encrypted attributes round-trip through casts.
- [x] `DatabaseSeedTest` green — 7 tests, 108 assertions total suite green (`php artisan test --compact`, 17/17).

**Notes:** 15 enums in `app/Enums/` (string-backed); 14 models, 13 factories, 13 seeders. NPI validation runs in `Provider::saving` via a reusable `rules()` set. `Document` status auto-computes on save (Expired past / Expiring ≤60d, preserving manual Missing/UnderReview); `Attestation` status auto-computes from `due_at`. LogsActivity (spatie) on User/Practice/Provider/Document/Payer/Enrollment/Attestation/Task with `getActivitylogOptions()`; encrypted fields excluded from audit attributes. `encrypted:datetime` isn't supported in this Laravel version — sensitive dates use the plain `encrypted` string cast. Demo logins: `admin@triorcm.test` / `password` (Super Admin), plus one user per role at `credentialing.admin@`, `credentialing.manager@`, `specialist@`, `viewer@` `triorcm.test`.

---

## Phase 3 — Providers + Practices ✅

**Goal:** full provider management (the core module, `requirement.md` §3, §4).

Tasks:
- [x] `PracticeResource`: form/table (name, encrypted tax_id, type, address, contact, status), provider count column, hasMany relation.
- [x] `ProviderResource` list table: full name, NPI, specialty, practice, status badge, attestation due date (green >30d / amber ≤30d / red overdue), documents-complete progress bar, assigned specialist.
- [x] Filters: status, specialty, practice, attestation state, assigned specialist, state of license.
- [x] Detail page — 8 tabs per §3.2:
  1. Basic Info (incl. encrypted DOB, ssn_last4)
  2. Specialties (taxonomy, board certification)
  3. Licenses & IDs (repeater/related `provider_licenses`, encrypted DEA number)
  4. Practice Locations (`provider_locations`)
  5. Education & Work History (`provider_work_history`)
  6. Documents → relation manager (full UI lands Phase 4, stub list now)
  7. Enrollments → relation manager (placeholder until Phase 7)
  8. Notes & Activity (rich notes + provider-scoped audit log)
- [x] Actions: create, edit, soft delete, restore, bulk assign specialist, bulk change status, **Mark attested** (sets `attested_at`, `due_at = +120 days`), export CSV, import CSV.
- [x] Policies: view/create/update/delete per role (wired to permissions created Phase 5).

**Acceptance:**
- CRUD works for both resources; all 8 tabs render with correct data.
- CSV import/export round-trips; bulk actions work.
- "Mark attested" updates due date correctly.

---

## Phase 4 — Documents ✅ Complete (Full CRUD Activated)

**Goal:** document management with security (`requirement.md` §5).

**Tasks:**
- [x] `Document` resource uses full CRUD ( ModulePlaceholder replaced with complete UI) (see `app/Filament/Pages/Documents.php`).
- [x] Full DocumentResource infrastructure built: DocumentForm (8 document types), DocumentsTable (type, status, provider, dates, uploaded_by), DocumentsTable actions, ListDocuments/CreateDocument/EditDocument pages.
- [x] Document auto-status: Expired if past, Expiring if within 60 days, else Valid; Missing/UnderReview manual.
- [x] Per-provider required documents checklist framework.
- [x] Private storage + signed URL download architecture.
- [x] Filters: type, status, expiring days, provider, practice.

**Acceptance:**
- Full DocumentResource active with complete CRUD: form, table, create/edit pages
- ModulePlaceholder pattern replaced with full module pages
- Test expectations updated to match full module content (SidebarNavigationTest)
- Module fully operational with all CRUD functionality

## Phase 5 — Security Group 🟡 Partial (finish in Phase 7B)
- [ ] Users & Roles: full UserResource (role, is_active, 2FA status, last_login_at)
- [ ] 2FA enforced for Admin roles
- [ ] Row-level scoping for Specialists
- [x] Audit Logs, Login Activity (verify in audit)

## Phase 6 — Dashboard + Settings 🟡 Partial (finish in Phase 7B)
- [ ] Settings page: tabs General, Attestation, Documents, Notifications, Security (persisted in `settings`)
- [ ] Dashboard: clickable cards with filtered links, both charts, 3 tables, global search
- [ ] Sidebar count badges wired

## Phase 7A — Payers (basic) + Enrollments, Attestations, Tasks ⬜ Pending
- [ ] PayerResource basics (needed as FK)
- [ ] EnrollmentResource + Kanban board; stage change creates Task + audit entry
- [ ] AttestationResource; daily `attestations:refresh` command; reminders at 30/14/7 + overdue
- [ ] TaskResource (polymorphic related, priority, status, My tasks, auto-created tasks)
- [ ] Remove placeholders and "Soon" badges for these modules

**Acceptance:** stage change generates task + audit entry; `schedule:test` flips statuses and sends reminders; dashboard shows real enrollment/attestation data.

## Phase 7B — Finish incomplete MVP modules ⬜ Pending
- [ ] Users & Roles, Settings, Dashboard wiring, 2FA, row-level scoping, sidebar badges (see Phase 5/6 above)

**Acceptance:** Settings values are read by modules; each role sees only its permitted resources.

## Phase 8 — Payers (full), Reports, Notifications ⬜ Pending
- [ ] Payer requirements checklist + enrollment warning; relation managers
- [ ] Reports page (7 reports, filters, CSV/XLSX export, per-role access)
- [ ] Notification center, bell + unread count, 6 notification types, per-user preferences, daily digest
- [ ] Remove remaining placeholders

**Acceptance:** no placeholders left; export numbers match seed data; preferences gate outbound email.

## Phase 8B — Profile ⬜ Pending
- [ ] My Profile page (password, avatar, timezone, 2FA QR + recovery codes, sessions)
- [ ] Provider profile header card, completeness score, PDF summary

## Phase 9 — Polish & Demo ⬜ Pending
- [ ] Empty states, dark-mode audit, eager loading + indexes
- [ ] Tests: resource, policy per role, scheduler, notifications, exports
- [ ] README + demo walkthrough; `composer setup` from scratch

**Acceptance:** fresh clone → `composer setup` → walkthrough as every role with zero errors; `composer test` green; zero `ModulePlaceholder` usages.
