# Deploying Volamani on Namecheap cPanel (shared hosting)

Target: **primary domain**, served from `public_html`, with **SSH access**.

> ⚠️ **Hard requirement — check this first.** The app requires **PHP 8.3+**
> (`composer.json` → `"php": "^8.3"`). In cPanel open **Select PHP Version**
> (a.k.a. MultiPHP Manager) and confirm 8.3 is selectable for your domain. If the
> newest option is 8.2 or lower, the app will not run on that server — stop here
> and ask Namecheap to move you to a server with PHP 8.3, or use a VPS.

---

## 1. Pre-flight (cPanel UI)

1. **PHP version** → **Select PHP Version** → set **8.3**. Enable extensions:
   `bcmath, intl, gd, pdo_mysql, mysqli, mbstring, zip, fileinfo, curl,
   openssl, exif`. Set `memory_limit` ≥ `256M` (PHP Options on the same page).
2. **Database** → **MySQL Databases**:
   - Create a database, e.g. `volamani` (cPanel prefixes it → `youruser_volamani`).
   - Create a DB user + strong password.
   - **Add the user to the database with ALL PRIVILEGES.**
   - Note the final `youruser_volamani` / `youruser_dbuser` names.
3. **SSL** → **SSL/TLS Status** → run **AutoSSL** so `https://` works
   (required — the app forces HTTPS in production and uses secure cookies).
4. **SSH**: if the Terminal isn't in cPanel, enable SSH in the Namecheap
   dashboard (**Manage → enable SSH Access**).

---

## 2. Get the code (SSH)

```bash
cd ~
git clone https://github.com/ipigbeitsenes/volamani.git volamani
cd volamani
```

Find your PHP 8.3 CLI and Composer (paths vary per server):

```bash
which php        # may be /usr/local/bin/php or ea-php83; note the path
php -v            # must report 8.3.x — if not, use the ea-php83 binary
which composer   # if missing: curl -sS https://getcomposer.org/installer | php
                 # then use:  php composer.phar  instead of  composer
```

---

## 3. Install & configure

```bash
composer install --no-dev --optimize-autoloader

cp .env.cpanel.example .env
nano .env            # fill in APP_URL, DB_*, MAIL_*, PAYSTACK_* (see the file's comments)

php artisan key:generate
php artisan migrate --force
php artisan db:seed --class=SettingsSeeder --force     # currency + feature flags + policy copy
# create your admin/staff accounts (rotate the seeded passwords after first login):
php artisan db:seed --class=AdminUserSeeder --force
php artisan db:seed --class=StaffSeeder --force

php artisan storage:link
chmod -R 775 storage bootstrap/cache
```

---

## 4. Build front-end assets

The app uses Vite — the production site needs `public/build/manifest.json` or every
page 500s ("Vite manifest not found").

**If Node/npm is available on the server:**
```bash
npm install
npm run build
```
(If `npm` isn't found, enable it in cPanel → **Setup Node.js App**, or use the
CloudLinux Node selector, then re-run.)

**If Node is NOT available on the server:** build on your own machine and upload —
```bash
# on your local machine, in the project:
npm install && npm run build
# then upload the generated  public/build/  folder to  ~/volamani/public/build/
# (cPanel File Manager or SFTP).  public/build is gitignored, so git won't carry it.
```

---

## 5. Point the primary domain at Laravel's `public/`

The primary domain's document root is `~/public_html`, but Laravel must serve from
`~/volamani/public`. Symlink it (SSH):

```bash
cd ~
# back up whatever is in public_html first, then replace it with a symlink:
mv public_html public_html_old 2>/dev/null
ln -s ~/volamani/public public_html
```

If your host doesn't follow symlinks for the docroot (rare on Namecheap), use the
**fallback** instead: keep `public_html`, copy the *contents* of `~/volamani/public`
into `~/public_html`, then edit `~/public_html/index.php` so the two `require`
paths point to `~/volamani` (`__DIR__.'/../volamani/...'`).

---

## 6. Cache & optimize (SSH)

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

Re-run these after any `.env` or code change. To undo: `php artisan optimize:clear`.

---

## 7. Cron jobs (cPanel → Cron Jobs)

Use your PHP 8.3 CLI path from step 2 in place of `php` below.

**Scheduler** (every minute — drives escrow release, subscriptions, backups, SLAs):
```
* * * * * cd ~/volamani && php artisan schedule:run >> /dev/null 2>&1
```

**Queue worker** (every minute — processes webhook fulfilment, emails, notifications):
```
* * * * * cd ~/volamani && php artisan queue:work --stop-when-empty --max-time=55 >> ~/volamani/storage/logs/worker.log 2>&1
```

> Because there's no Redis/daemon, the queue is drained once a minute. The buyer's
> return-from-Paystack page verifies the payment **synchronously**, so they see
> success immediately; the webhook path just adds a redundant, retry-safe backstop.

---

## 8. Paystack

- Put **live** keys in `.env` (`PAYSTACK_PUBLIC_KEY`, `PAYSTACK_SECRET_KEY`).
- In the Paystack dashboard set the **webhook URL** to
  `https://YOURDOMAIN.com/webhooks/paystack`.
- Test in Paystack **test mode** end-to-end before flipping to live.

---

## 9. Verify

- `https://YOURDOMAIN.com/up` → `200`
- `https://YOURDOMAIN.com/health` → JSON `{"status":"ok", ...}`
- Home page loads **with styling** (confirms assets built + symlink correct).
- Register a test user, run a test-mode checkout, confirm the order + escrow.
- Check `storage/logs/laravel.log` if anything 500s.

---

## Notes & gotchas

- **APP_DEBUG=false** in production, always (leaks secrets otherwise).
- **HTTPS first:** don't set `SESSION_SECURE_COOKIE=true` until AutoSSL works, or
  logins break. (It's pre-set in the template — enable SSL before you go live.)
- **Backups:** `db:backup` runs nightly and needs `mysqldump` (present on cPanel).
  It writes to `storage/app/private/backups`. For real disaster recovery, set
  `BACKUP_DISK` to S3 — a backup on the same account isn't off-box.
- **Rotate** the seeded admin/staff passwords after first login.
- **Repricing:** displayed prices are still the original amounts shown in the new
  currency — reprice the catalog/plans before taking real payments.
- The `Dockerfile` and `docker-compose.yml` are for local dev only — ignore them here.
- After deploy, updates are: `git pull && composer install --no-dev -o &&
  npm run build && php artisan migrate --force && php artisan optimize:clear &&
  php artisan config:cache route:cache view:cache`.
