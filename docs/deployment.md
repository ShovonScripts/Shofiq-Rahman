# Deployment and verification

This application uses Laravel 12, Inertia 2, and Vue 3. Use PHP 8.2+ (with the extensions required by Composer, your PDO database driver, and GD for image-upload tests), Composer 2, and a supported Node.js release compatible with Vite 7 (20.19+ or 22.12+; prefer a supported LTS release).

## Verify a checkout

Configure a local `.env` and application key before running the backend tests. Never reuse a production database for tests; `phpunit.xml` selects an in-memory SQLite database.

```sh
composer install
# First local setup only; preserve an existing environment/key:
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate
fi

npm ci
npm test
npm run build
php artisan test
vendor/bin/pint --dirty --test
npm audit
composer audit
```

The Node tests compile the actual Vue component scripts and exercise analytics validation, edit-form payloads, role-specific navigation, and the JSON like interaction. PHPUnit covers authorization, verification, moderation visibility, taxonomy/metadata preservation, link history, migration reconciliation, and scheduled publishing in addition to the existing suite.

## Upgrade an existing installation

1. Back up the database and uploaded files, and verify the backup can be restored. Rehearse the migration on a staging copy using the same database driver as production.
2. Enter maintenance mode and install the locked dependencies/build assets using your normal deployment procedure.
3. Run the forward migrations and clear old application caches:

   ```sh
   php artisan migrate --force
   php artisan optimize:clear
   php artisan queue:restart
   ```

4. Verify a book edit, a purchase-link redirect/click count, a post edit with existing categories/genres, and the email-verification flow before leaving maintenance mode.
5. Ensure the Laravel scheduler runs every minute (see below).

Do **not** use `migrate:fresh` or reseed an existing production database.

### Book-link reconciliation

`2026_09_29_000001_reconcile_book_link_clicks.php` makes `book_links` and `book_link_clicks.book_link_id` canonical on both fresh and upgraded databases.

- Existing canonical link IDs and click rows are retained.
- Legacy purchase links are matched by book and URL or imported with new canonical IDs. Their mapping is retained in `book_purchase_links.canonical_book_link_id`.
- A legacy `book_purchase_link_id` column is preserved when present. Numeric IDs from the two old tables are never assumed to identify the same seller.
- Ambiguous or orphaned legacy clicks are retained with a null canonical key and a warning in the application log. Review their original identifiers and a pre-upgrade backup before assigning a seller manually. Fresh installations do not have this legacy column.
- Removing a seller link retains its click records at book level; its nullable canonical foreign key becomes null.

This is a **forward-only data migration**. Its `down()` intentionally refuses automatic reversal because the merged ID spaces cannot be safely split again. Roll back this release with a tested database backup and matching application code, not by running `migrate:rollback` blindly.

The regression suite exercises a fresh SQLite schema and simulated legacy layouts, including colliding IDs. Also test an actual MySQL upgrade in staging if that is your production driver.

## Scheduled publishing

Configure one scheduler runner for the deployment, for example this cron entry (replace the paths):

```cron
* * * * * cd /path/to/application && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

For local development, run `php artisan schedule:work`. To publish due posts manually:

```sh
php artisan posts:publish-scheduled
```

Only posts with `status = scheduled` and a non-null `published_at` at or before the current time are published. Future schedules and drafts are unchanged. The command is safe to repeat and clears the public post/category/feed/sitemap caches when it publishes anything. Publication times use the application's configured timezone (`config/app.php`, UTC by default).

## Email verification

Users must verify their email before accessing the dashboard, commenting, or liking content. Registration sends a verification notification, and changing an email address resets verification. Configure and test outbound mail before deployment; existing unverified accounts will also need to verify. Users can request a new verification link at `/verify-email`.
