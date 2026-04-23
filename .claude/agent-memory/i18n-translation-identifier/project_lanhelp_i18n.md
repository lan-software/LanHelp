---
name: LanHelp i18n Setup
description: i18n library, locale file locations, key conventions, and refactor status for LanHelp
type: project
---

## Translation library
- Frontend: vue-i18n v9 via `useI18n()` composition API; `$t()` in templates
- Backend: Laravel `__()` / `trans()` with `lang/en/messages.php`

## Locale file locations
- Frontend JSON: `resources/js/locales/en.json` (and de/es/fr/ko/nds/sv/sxu/tlh/uk) — ~225 pre-existing keys plus ~150+ added in April 2026 run
- Backend PHP: `lang/en/messages.php` (created April 2026), `lang/en/validation.php` (pre-existing)
- Root `lang/validation.php` also exists (global fallback)

## Key naming convention
- Hierarchical dot-notation in JSON: `common.*`, `auth.login.*`, `auth.register.*`, `settings.profile.*`, `settings.security.*`, `tickets.*`, `kb.*`, `kb.admin.*`, `staff.board.*`, `components.userMenu.*`, `components.deleteUser.*`, `components.twoFactorSetup.*`, `components.twoFactorRecoveryCodes.*`, `components.appHeader.*`
- Backend PHP uses snake_case nested arrays: `messages.tickets.*`, `messages.kb.*`

## Status after April 2026 refactor
- All pages in `resources/js/pages/**` fully refactored (auth, settings, tickets, kb, staff, admin/kb)
- Key components refactored: UserMenuContent, DeleteUser, AppearanceTabs, TwoFactorSetupModal, TwoFactorRecoveryCodes, AppHeader, AnnouncementBanner
- AppSidebar already used `useI18n()` pre-refactor
- Dashboard.vue and Welcome.vue were already fully translated pre-refactor
- Backend: TicketController, TicketReplyController, TicketStatusController, TicketAssignmentController, Admin/KnowledgeBaseArticleController — all flash messages wrapped

## Existing locale bank
- The 10 language JSON files had ~225 keys pre-existing but components/pages were NOT using them (only Dashboard.vue and Welcome.vue did). The refactor wired up the existing keys and added new ones where missing.

## Cross-app shared-string candidates (confirmed matches with prior LanShout/LanEntrance/LanBrackets runs)
- "Settings", "Log out", "Dismiss", "Light"/"Dark"/"System", "Navigation menu", "Delete account", "Cancel", "Save"/"Saved.", "Password", "Email address", "Forgot password?", "Remember me", "Confirm password"
- All match prior apps — centralization into LanCore pending decision.

**Why:** Systematic i18n refactor of LanHelp as app 4 of 5 in Lan* suite batch.
**How to apply:** In future conversations, don't re-scan already-refactored files; focus on any new components added after April 2026.
