# Deploying to Hostinger (shared hosting)

This app is a Laravel 13 project, so it needs `vendor/` and compiled Vite
assets to exist on the server. Hostinger's shared plans give you no dependable
Composer or Node toolchain, so we build both in GitHub Actions and publish the
result to a **deploy branch** that hPanel's Git integration pulls.

```
  main ──▶ GitHub Actions ──▶ deploy/hostinger ──▶ hPanel Git pull ──▶ public_html
           (composer + vite)   (vendor + build       (manual, or via     │
                                committed)            webhook)          ▼
                                                            deploy/post-deploy.sh
                                                            (migrate + cache)
```

Deploys are **manual** — nothing reaches the live site until you click
*Run workflow*.

---

## Files this setup adds

| File | Purpose |
| --- | --- |
| `.github/workflows/deploy-hostinger.yml` | Builds the release, publishes `deploy/hostinger` |
| `.htaccess` (repo root) | Forwards requests into `public/`, blocks source dirs |
| `.env.hostinger.example` | Production env template |
| `deploy/post-deploy.sh` | Migrations + cache rebuild, run on the server |
| `deploy/gitignore-deploy` | `.gitignore` used on the deploy branch only |

---

## One-time setup

### 1. Publish the deploy branch first

hPanel can only track a branch that already exists, so run the workflow once
before touching Hostinger:

**GitHub → Actions → "Deploy to Hostinger" → Run workflow**

- **source_ref**: `main`
- **php_version**: match what you will set in step 3 (`8.3` is a safe default)
- **pull_on_hostinger**: leave on; it no-ops until the webhook secret exists

This creates `deploy/hostinger` carrying the full app plus `vendor/` and
`public/build`.

### 2. Create the MySQL database

**hPanel → Databases → Management → Create new database.** Note the database
name, user and password — Hostinger prefixes both name and user with your
account id (`u123456789_`).

Do **not** import `database/barangayapp.sql`. That dump is a local HeidiSQL
export; its `CREATE DATABASE barangayapp` / `USE barangayapp` header will fail
against a prefixed Hostinger database. The migrations in `database/migrations`
are the source of truth and `post-deploy.sh` runs them for you.

### 3. Set the PHP version

**hPanel → Advanced → PHP Configuration → PHP version.** Select the same
version you passed to the workflow. Under *PHP extensions* make sure
`pdo_mysql`, `mbstring`, `bcmath`, `gd`, `zip`, `intl` and `fileinfo` are on.

> The workflow pins Composer's `platform.php` to this version. If the server
> runs *older* PHP than the build, every request dies in
> `vendor/composer/platform_check.php`. Keep the two in sync.

### 4. Point hPanel Git at the deploy branch

**hPanel → Advanced → Git → Create a new repository.**

| Field | Value |
| --- | --- |
| Repository address | `https://github.com/mjaimesfromyt-sys/brgysanjose.git` |
| Branch | `deploy/hostinger` |
| Directory | `public_html` |

The target directory must be **empty** before hPanel will accept it — clear
`public_html` in File Manager first.

For a private repository, add the SSH key hPanel shows you as a deploy key on
GitHub (**Settings → Deploy keys → Add deploy key**, read access is enough).

Because the whole repository lands inside the web root, the root `.htaccess`
does two jobs: it rewrites requests into `public/`, and it hard-denies
`app/`, `config/`, `database/`, `storage/`, `vendor/` and every dotfile. Do not
delete it.

### 5. Create `.env` on the server

**File Manager → `public_html` → New file → `.env`**, then paste
`.env.hostinger.example` and fill it in. Generate the key locally with:

```bash
php artisan key:generate --show
```

`.env` is gitignored and is not on the deploy branch, so a deploy never
overwrites it — and you must create it by hand exactly once.

### 6. Run the first deploy script

Over SSH (**hPanel → Advanced → SSH Access** for the host, port and user):

```bash
ssh -p 65002 u123456789@your-server-ip
cd public_html
bash deploy/post-deploy.sh
php artisan db:seed --force        # first deploy only, creates the admin login
```

No SSH on your plan? Use **hPanel → Advanced → Cron Jobs** to run it once:

```
/usr/bin/bash /home/u123456789/public_html/deploy/post-deploy.sh
```

Set it a few minutes out, let it fire, then delete the entry.

> **Change the seeded admin password immediately.** `AdminUserSeeder` creates
> `admin@barangay.com` with a password committed in plain text in this repo.
> Log in and change it before announcing the site, or seed nothing and create
> the first admin by hand with `php artisan tinker`.

### 7. Background jobs

Receipt e-mails go through the database queue, so a worker has to drain it.
**hPanel → Advanced → Cron Jobs**, every minute:

```
/usr/bin/php /home/u123456789/public_html/artisan queue:work --stop-when-empty --tries=3
```

And for scheduled tasks:

```
/usr/bin/php /home/u123456789/public_html/artisan schedule:run
```

Use `--stop-when-empty` rather than a long-running worker; shared hosting kills
long processes and Hostinger's cron will simply restart it next minute.

### 8. HTTPS

**hPanel → Security → SSL** — install the free certificate and turn on *Force
HTTPS*. Then confirm `APP_URL` in `.env` uses `https://`, since
`SESSION_SECURE_COOKIE=true` means cookies are dropped over plain HTTP.

### 9. Optional: skip the hPanel click

**hPanel → Git → your repository → Auto deployment** gives you a webhook URL.
Save it as a GitHub secret named `HOSTINGER_DEPLOY_WEBHOOK`
(**Settings → Secrets and variables → Actions**) and the workflow will trigger
the pull itself.

---

## Routine deploys

1. Merge your work into `main`.
2. **Actions → "Deploy to Hostinger" → Run workflow** (pick the ref, confirm
   the PHP version).
3. If you skipped the webhook: **hPanel → Git → Deploy**.
4. Run `bash deploy/post-deploy.sh` on the server.

Step 4 is not optional whenever a release touches migrations, `config/`,
`routes/` or Blade views — hPanel only performs a `git pull`, and Laravel keeps
serving its previously compiled cache until `artisan optimize` re-runs.

`post-deploy.sh` puts the site into maintenance mode first and brings it back
up even if a migration fails, so residents never see a half-migrated schema.

---

## Troubleshooting

**500 error, blank page**
Check `storage/logs/laravel.log` in File Manager. With `APP_DEBUG=false` the
browser shows nothing useful by design.

**`Class "..." not found` / `vendor/autoload.php` missing**
hPanel is tracking `main` instead of `deploy/hostinger`. Only the deploy branch
carries `vendor/`.

**`Composer detected issues in your platform`**
Server PHP is older than the build PHP. Either raise it in *PHP Configuration*
or re-run the workflow with the lower `php_version`.

**Styles missing, 404 on `/build/assets/...`**
`public/build` did not survive the pull. Confirm the workflow's build step
succeeded and that `deploy/gitignore-deploy` — not the repo's normal
`.gitignore` — is the `.gitignore` on the deploy branch.

**`The stream or file "storage/logs/laravel.log" could not be opened`**
Permissions. Over SSH:

```bash
chmod -R 775 storage bootstrap/cache
```

**Directory listing or `.env` visible in the browser**
The root `.htaccess` is missing or Apache has `AllowOverride None`. Re-check
that `.htaccess` exists at the top of `public_html`.

**hPanel refuses the repository directory**
`public_html` is not empty. Clear it, then create the repository.

**Login works locally but not on the live site**
`APP_URL` does not match the real domain, or the site is being served over HTTP
while `SESSION_SECURE_COOKIE=true`.
