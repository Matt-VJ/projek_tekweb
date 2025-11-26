# Tophers feature - Category & Public Homepage

Files added by this branch (do not modify other team files):

- `db.php` — simple DB connection (edit DB name if needed).
- `migrations/topher_categories.sql` — SQL to create `topher_categories` and `topher_posts` and sample rows.
- `home.php` — public homepage (grid) + filters (category + search). Does not modify `index.php`.
- `fetch_posts.php` — JSON endpoint used by `home.php` for filtering.
- `assets/css/topher.css` — minimal styles for the grid.

Setup & usage

1. Import the migration into your project database (phpMyAdmin or CLI). Example in phpMyAdmin:
   - Open your `mini-cms` (or `mini_cms`) database.
   - Import `migrations/topher_categories.sql`.

2. Confirm the database name in `db.php` matches your phpMyAdmin database name. The default is `mini-cms`.
   - If your database is named `mini_cms` (underscore) set `'name' => 'mini_cms'` in `db.php`. This branch uses `mini_cms` by default.

3. Open `home.php` in your browser (for XAMPP it might be `http://localhost/projek_tekweb/home.php`).

Notes & safety

- Table names are prefixed with `topher_` to avoid affecting other team members' tables.
- I did not change `index.php` or any existing files used by other teammates.
- If you want this homepage to be the site root later, we can rename or integrate it with the project's `index.php` after coordinating with the team.

Troubleshooting import errors (foreign key name collisions)

If you got an error like `errno: 121 "Duplicate key on write or update"` when importing, follow these steps:

1. Check if a constraint with the old name exists (run in phpMyAdmin -> SQL):

   ```sql
   SELECT TABLE_NAME, CONSTRAINT_NAME
   FROM information_schema.TABLE_CONSTRAINTS
   WHERE CONSTRAINT_SCHEMA = 'mini_cms'
     AND CONSTRAINT_NAME = 'topher_posts_fk_category';
   ```

2. If that query returns a `TABLE_NAME`, drop the foreign key from that table first:

   ```sql
   ALTER TABLE `that_table` DROP FOREIGN KEY `topher_posts_fk_category`;
   ```

3. If a partial import left `topher_posts`, drop it before re-importing:

   ```sql
   DROP TABLE IF EXISTS `topher_posts`;
   ```

4. Re-import `migrations/topher_categories.sql`.

If you'd like, I can generate a one-off migration that names the FK with a random suffix to avoid collisions automatically.

Admin area (basic)

I added a minimal admin area at `admin/` to manage `topher_categories` and `topher_posts`:

- `admin/login.php` — login page (simple password form).
- `admin/logout.php` — logout.
- `admin/categories.php` — list, create, delete categories.
- `admin/posts.php` — list, create, delete posts.
- `admin/_auth.php` — small helper that provides session-based protection.

Default admin password (change it): `topher123`. To change it, edit `admin/_auth.php` and replace the value for `TOPHER_ADMIN_PASSWORD`.

Usage:
1. Open `http://localhost/projek_tekweb/admin/login.php` in your browser.
2. Login with the password above, then manage categories or posts.

This admin area is intentionally minimal and only intended for local/testing use on your `topher` branch. If you want, I can strengthen auth, add CSRF protection, paging, or more robust edit UIs.

