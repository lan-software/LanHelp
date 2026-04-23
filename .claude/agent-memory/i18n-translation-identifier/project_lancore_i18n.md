---
name: LanCore i18n Setup
description: i18n library, locale file locations, key conventions, and refactor status for LanCore (app 5 of 5, April 2026)
type: project
---

## Translation library
- Frontend: vue-i18n v9 via `useI18n()` composition API; `t()` in scripts, `$t()` in templates
- Backend: Laravel `__()` / `trans()` with domain-named `lang/en/*.php` files

## Locale file locations
- Frontend JSON: `resources/js/locales/en.json` (and de/es/fr/ko/nds/sv/sxu/tlh/uk) — ~341 lines pre-refactor, ~410+ lines after April 2026 run
- Backend PHP: `lang/en/competition.php`, `lang/en/ticketing.php`, `lang/en/shop.php`, `lang/en/news.php` (all created April 2026), `lang/en/validation.php` (pre-existing)

## Key naming convention
- Frontend JSON: hierarchical dot-notation — `common.*`, `auth.*`, `settings.*`, `navigation.*`, `shop.*`, `tickets.*`, `dashboard.*`, `footer.*`, `cookies.*`, `legal.*`, `help.*`, `validation.*`
- Backend PHP: domain-named files with nested arrays: `competition.team.*`, `competition.notifications.*`, `ticketing.admin.*`, `ticketing.notifications.*`, `shop.cart.*`, `shop.order.*`, `shop.ticket_type.*`, `shop.notifications.*`, `news.notifications.*`
- Placeholders: `:name`-style in PHP, `{name}`-style in JSON

## Status after April 2026 refactor (sub-pass 1 + sub-pass 2, capped at 50 each)

### Sub-pass 1 — Backend PHP (50 strings converted)
Files modified:
- `app/Domain/Competition/Http/Controllers/TeamController.php` — 4 flash messages
- `app/Domain/Competition/Http/Controllers/TeamInviteController.php` — 2 flash messages
- `app/Domain/Ticketing/Http/Controllers/AdminTicketController.php` — 1 flash message
- `app/Domain/Ticketing/Notifications/TicketTokenRotatedNotification.php` — subject, body, 5 reason lines, action, instructions
- `app/Domain/Shop/Http/Controllers/CartController.php` — 5 error messages
- `app/Domain/Shop/Http/Controllers/OrderController.php` — 2 error messages
- `app/Domain/Shop/Http/Controllers/ShopController.php` — 3 unavailability reasons + 2 JSON voucher messages
- `app/Domain/Shop/Notifications/OrderConfirmationNotification.php` — 9 mail strings
- `app/Domain/Competition/Notifications/TeamJoinRequestNotification.php` — 5 mail strings
- `app/Domain/Competition/Notifications/JoinRequestResolvedNotification.php` — 7 mail strings
- `app/Domain/News/Notifications/NewsPublishedNotification.php` — 4 mail strings

### Sub-pass 2 — Frontend Vue (50 strings converted)
Files modified:
- `resources/js/pages/shop/Index.vue` — 21 strings (shop page)
- `resources/js/pages/cart/Index.vue` — 24 strings (cart page)
- `resources/js/pages/shop/CheckoutSuccess.vue` — 7 new strings (3 reuse shop.* keys)
- `resources/js/pages/tickets/Index.vue` — 9 strings

## Pre-existing i18n usage (before April 2026 run)
Only 6 of 296 Vue files used i18n:
- `pages/Dashboard.vue`, `pages/auth/TwoFactorChallenge.vue`
- `components/AppSidebar.vue`, `components/AskForHelpButton.vue`, `components/UserMenuContent.vue`, `components/LanguageSwitcher.vue`
Auth pages (Login, Register, ForgotPassword, ResetPassword, VerifyEmail, ConfirmPassword) were ALREADY fully translated with `$t()`.

## What remains after cap (largest untouched areas)
Backend PHP:
- All competition user-facing controllers (CompetitionController, UserCompetitionController, AdminTeamController)
- Announcement notifications (toMail partially — announcement subject)
- Program notifications
- All domain CRUD controllers have NO flash messages (clean redirect-only pattern) — very few backend strings remain
Frontend Vue (130 pages, 166 components with ~290 untouched):
- Entire admin domain pages (news, events, competitions, ticketing, seating, etc.)
- User-facing competition pages (competitions/user/*)
- orders/*, my-orders/*, admin-tickets/*, announcements/*, programs/*, integrations/*, orchestration/*, games/*, venues/*, sponsors/*, webhooks/*, vouchers/*, purchase-requirements/*, payment-provider-conditions/*, global-purchase-conditions/*, ticket-types/*, ticket-addons/*, sponsor-levels/*, seating/*
- ALL components (only 4/166 refactored pre-run)
- settings/Organization.vue, settings/Notifications.vue, settings/Achievements.vue, settings/TicketDiscovery.vue
- team-invites/Show.vue, competitions/user/TeamShow.vue, competitions/user/Teams.vue, competitions/user/Show.vue

## Cross-app shared string candidates — confirmed in LanCore en.json
These keys exist in LanCore's `resources/js/locales/en.json` already:
- `common.settings` = "Settings"
- `common.logout` = "Log out" (navigation.logout also)
- `auth.login.button` = "Log in"
- `common.cancel` = "Cancel"
- `common.save` = "Save" / `common.saved` = "Saved."
- `common.confirm` = "Confirm"
- `common.dashboard` = "Dashboard"
- `common.profile` = "Profile"
- `settings.appearance.light/dark/system`
- `auth.login.password` = "Password"
- `auth.login.email` = "Email address"
- `auth.login.forgotPassword` = "Forgot password?"
- `auth.login.rememberMe` = "Remember me"
- `auth.confirmPassword.title` = "Confirm your password"
All cross-app shared strings from prior apps ARE present in LanCore — strong centralization candidate.
LanCore does NOT yet have: "Sign in", "Something went wrong.", "Dismiss", "Platform", "Navigation menu", "Delete account" as standalone keys (they exist in nested settings/navigation groups).

**Why:** LanCore is app 5/5 in Lan* suite batch i18n refactor. April 2026.
**How to apply:** Do not re-scan already-refactored files; continue from sub-pass 2 Vue pages in next conversation, or start centralization effort.
