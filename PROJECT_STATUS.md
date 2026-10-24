# OMDS Project Status

Last updated: 2026-10-24

## Current phase

**Admin organization management and demo data** provides a read-only organization list and organization member view, plus one dummy organization with three dummy members in the configured local database for UI testing.

### Files created

- `admin/organizations.php` — paginated list of existing organizations and their account status.
- `admin/organization_members.php` — paginated membership view with basic account details only; no medical record data.

### Files modified

- `includes/admin_layout.php` — enables the Organizations sidebar entry and keeps organization/member navigation highlighted.
- `PROJECT_STATUS.md` — records this phase and verification status.

### Tests actually run (this phase, 2026-10-24)

| Test | Result |
|---|---|
| Schema review | Passed — `organizations` and `organization_members` exist in the project schema |
| PHP syntax tests | Passed — `admin/organizations.php`, `admin/organization_members.php`, and `includes/admin_layout.php` have no syntax errors |
| Demo data creation | Passed — created `Demo Health Organization` (organization ID 1), its organization account, and 3 dummy member accounts in one transaction |
| Database verification | Passed — confirmed the organization account has the `organization` role and all 3 dummy members are linked with expected membership roles |
| Browser/admin workflow | Not run — open the admin page to confirm the live display |

### Manual test

Sign in as an administrator and open **Organizations**. Open **Demo Health Organization** with **View members** and confirm the three demo members and their roles appear. The page displays account and membership details only, not medical records. Use the browser Back button or **Back to Organizations** to return.

### Incomplete work and safety notes

- The configured local database now contains one clearly labeled dummy organization account and three dummy member accounts for UI testing. Their email addresses use the reserved `.invalid` domain, and random password hashes were generated without saving or distributing passwords; these are not provided as login accounts.
- No pre-existing user records were changed. Remove the demo rows only if you explicitly want to clean up the test data.
- Visual and logged-in administrator workflow testing remain to be done.

## Previous phase

**Admin list pagination** adds centered pagination with 20 rows per page to User Management and Request Inbox.

### Files modified

- `admin/users.php` — paginates the non-admin user list and shows the visible range and total count.
- `admin/inbox.php` — paginates personnel applications and shows the visible range and total count.
- `PROJECT_STATUS.md` — records the change and verification status.

### Tests actually run (this phase, 2026-10-24)

| Test | Result |
|---|---|
| Source review of page validation, count queries, and row limits | Passed — both pages validate/clamp page numbers and request at most 20 rows |
| PHP syntax/runtime tests | Not run |
| Browser/database pagination workflow | Not run |

### Manual test

Open **User Management** and **Request Inbox** as an admin. With more than 20 rows, use the centered page links and confirm the entries and displayed ranges change. Try a page number larger than the last page; it should show the last page.

## Previous phase

**Audit log pagination** displays 20 entries per page, with entry counts and previous/next/page links.

### Files modified

- `admin/audit.php` — counts audit rows, validates/clamps the requested page, and loads only that page of results.
- `css/admin.css` — styles the audit pagination controls.
- `PROJECT_STATUS.md` — records the feature and verification status.

### Tests actually run (this phase, 2026-10-24)

| Test | Result |
|---|---|
| Source review of query limits and page validation | Passed — page size is fixed at 20; page input is validated and clamped |
| PHP syntax/runtime tests | Not run |
| Browser/database pagination workflow | Not run |

### Manual test

Open the admin Audit Logs page. If there are more than 20 events, use the page links and Previous/Next controls; confirm each page shows a different set of entries and its displayed entry range is correct. Test a URL such as `audit.php?page=999` to confirm it safely opens the last page.

## Previous phase

**Admin user management** provides a user list with account disable/enable.

Account deletion actions are unavailable. The Restore action remains only for accounts that were soft-deleted before deletion was removed.

### Files created

- `admin/users.php` — lists non-admin accounts and provides account controls without showing medical record contents.
- `admin/user_action.php` — validates admin permission and CSRF, preserves role/approval status when toggling access, and records audit events. It does not support account deletion.
- `sql/user_management_migration.sql` — one-time additive migration for existing databases.

### Files modified

- `includes/admin_layout.php` — enables the User Management sidebar page and partial navigation.
- `includes/auth.php`, `login.php` — prevent disabled or soft-deleted accounts from using authenticated pages or signing in.
- `sql/schema.sql` — includes account-control columns for new installations.
- `css/admin.css` — styles account controls.
- `admin/users.php` — removed the Soft delete control and permanent-delete form.
- `PROJECT_STATUS.md` — records this phase and its verification limits.

### Tests actually run (this phase, 2026-10-24)

| Test | Result |
|---|---|
| PHP syntax/runtime tests | Not run |
| Checked `admin/users.php` and `admin/user_action.php` for account-delete actions | Passed — delete actions are not offered or accepted |
| Live MySQL schema inspection | Not available — the workspace database connector returned “Connection not found” |
| Browser/admin workflow | Not run |

### Setup and manual test

1. Back up the existing `omds` database.
2. For an existing installation, apply `sql/user_management_migration.sql` once in phpMyAdmin. Do not rerun it after the columns exist. New installations get the columns from `sql/schema.sql`.
3. Sign in as an administrator and open **User Management**.
4. Disable a test user and verify it cannot sign in; enable it and verify the account’s existing personnel approval status is unchanged.
5. If any account was soft-deleted earlier, confirm it can still be restored.

### Incomplete work and safety notes

- The migration has not been applied to a live database, and the workflow has not been browser-tested.
- Accounts cannot be deleted from the admin User Management page or its action endpoint.
- Administrator accounts are excluded from the list and rejected by the action endpoint.

## Previous phase

**Admin sidebar content navigation** now follows the same fetch-into-content pattern as the regular-user shell.

## What was completed in this phase

### File modified

- `includes/admin_layout.php` — added a `mainContent` section, partial responses for sidebar fetches, and fetch/history navigation for Dashboard, Request Inbox, and Audit Logs. Application Details and Back to Inbox links also load inside the content area. Added a mobile menu button and disabled User Management / Organizations because those admin pages do not exist.

### Tests actually run (this phase, 2026-10-24)

| Test | Result |
|---|---|
| PHP diagnostics for `includes/admin_layout.php` | Passed — no errors reported |
| PHP CLI syntax check | Not run — PHP CLI was not available on PATH |
| XAMPP browser navigation | Not run yet |

### Manual browser test

1. Log in as an administrator and open `admin/dashboard.php`.
2. Click **Request Inbox**, open an application’s **Details**, and use **Back to inbox**. These should replace only the content area without reloading the admin shell.
3. Click **Audit Logs**, then use the browser Back and Forward buttons to verify the content follows navigation history.
4. At a narrow browser width, use the menu icon to show/hide the sidebar.

## Previous phase

**Admin view of uploaded professional ID** on `admin/application.php` is implemented.

## What was completed in this phase

### File created

- `admin/view_id.php` — admin-only viewer for a personnel application’s `company_id_file`. Only serves files under `uploads/personnel_ids/`, rejects path tricks (`..`), supports JPG/PNG/PDF, opens inline in the browser, and writes an audit event `personnel_id_viewed`. This is separate from `view_file.php` (medical records).

### File modified

- `admin/application.php` — if an ID file path exists, shows a **View uploaded professional ID** button (existing `link-btn` style) linking to `view_id.php?id=...`. If no file is stored, shows “None on file”.

### Files not changed for this phase

Schema, CSS, medical file handling, review/approve/deny, and audit list pages were left as they were.

## Tests actually run (this phase, 2026-10-19)

| Test | Result |
|---|---|
| PHP syntax check of `admin/view_id.php` | Passed (`php -l` via Desktop XAMPP PHP 8.0) |
| PHP syntax check of `admin/application.php` | Passed |
| Not logged in → `admin/view_id.php` | Redirects to `login.php` (HTTP 302) via local PHP built-in server |
| Not logged in → `admin/application.php` | Redirects to `login.php` (HTTP 302) |
| Logged-in admin opens ID file / sees the button | **Not completed in this environment** — the MySQL `omds` database currently has **no** `admin` user and **0** `personnel_profiles` rows, so login and a real application page could not be exercised here |
| System Apache `http://localhost/OMDSAI/` | **404** — running Apache DocumentRoot is `C:\xampp\htdocs`, which does not contain this project folder (project lives under Desktop `xamppset\...\htdocs\OMDSAI`) |

## Previous phases (still complete)

- `admin/audit.php` Audit Logs (tested earlier on 2026-10-19)
- `admin/review.php` Approve / Deny (tested earlier on 2026-10-19)

## Not done (out of scope)

- `admin/users.php`, `admin/organizations.php`
- `org/dashboard.php`
- Organization account creation
- Settings pages saving to the database

## How to test professional ID viewing

1. Start Apache and MySQL for the XAMPP copy that actually serves this project (or open the project through that DocumentRoot).
2. Confirm you can log in as admin (username `omds_admin`, Account ID `1000000001`). If admin is missing, run `setup_admin.php` or `install.php` only if you intend to set up that database.
3. Register (or use) an authorized-personnel account that uploaded a Company / Professional ID.
4. Log in as admin → **Request Inbox** → open that application.
5. Click **View uploaded professional ID**. A new tab should show the image or PDF.
6. Optional: open **Audit Logs** and confirm a `personnel_id_viewed` row.

## Recommended next task (not started)

Add `admin/users.php` (read-only user list first), **or** add `admin/organizations.php`. Ask before starting either one.
