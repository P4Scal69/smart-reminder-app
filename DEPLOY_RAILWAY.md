# Deploy to Railway (First-time Guide)

This repo is configured for Railway using Nixpacks via [nixpacks.toml](nixpacks.toml).

## 1) Prerequisites

- Code is pushed to GitHub.
- You have a Railway account.

## 2) Create the Railway project

1. In Railway, click **New Project**.
2. Choose **Deploy from GitHub repo**.
3. Select your repository.
4. Railway will detect Nixpacks and run the install/build commands from `nixpacks.toml`.

What Railway will run (from `nixpacks.toml`):
- `composer install --no-dev --optimize-autoloader`
- `npm ci`
- `npm run build`

## 3) Add PostgreSQL (and PostGIS)

1. In your Railway project, click **New** → **Database** → **PostgreSQL**.
2. Wait until the database is created.

### Enable PostGIS

Your migrations attempt to enable PostGIS automatically. If the first migration fails with a PostGIS permission/extension error, enable it manually on the Railway database:

- Run this SQL on the database:

  `CREATE EXTENSION IF NOT EXISTS postgis;`

How you run the SQL depends on Railway UI/plan, but typically you can use the DB query console or connect with a SQL client.

## 4) Set Laravel environment variables

**CRITICAL**: Railway's Laravel service must use **PostgreSQL** (not SQLite). The error `SQLSTATE[HY000]: General error: 1 no such function: ST_GeomFromText` means the app is connecting to SQLite instead of PostgreSQL/PostGIS.

In Railway: open your **Laravel service** → **Variables** and set at least:

### Required

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_KEY=` (generate this locally; see below)

### Database (REQUIRED — do NOT skip this)

Railway auto-generates a PostgreSQL database. You **must** link it to your Laravel service:

1. In Railway, click **+ New** → **Database** → **PostgreSQL** (if not already added)
2. In your Laravel service → **Variables**, set:

   - `DB_CONNECTION=pgsql`
   - `DB_HOST=` (from Railway Postgres `PGHOST`)
   - `DB_PORT=5432` (from Railway Postgres `PGPORT`)
   - `DB_DATABASE=` (from Railway Postgres `PGDATABASE`)
   - `DB_USERNAME=` (from Railway Postgres `PGUSER`)
   - `DB_PASSWORD=` (from Railway Postgres `PGPASSWORD`)
   - `DB_SSLMODE=require` (Supabase requires SSL)

   **Do NOT set `DB_CONNECTION=sqlite`**. If you see `sqlite` in the connection error, it means these variables are missing or incorrect.

### Enable PostGIS

Your migrations attempt to enable PostGIS automatically. If the first migration fails with a PostGIS permission/extension error, enable it manually on the Railway database:

- Run this SQL on the database:

  `CREATE EXTENSION IF NOT EXISTS postgis;`

### Recommended

- `LOG_CHANNEL=stderr`

### After you get a Railway domain

- `APP_URL=https://YOUR_RAILWAY_DOMAIN`

If your built assets load from the wrong URL, also set:
- `ASSET_URL=https://YOUR_RAILWAY_DOMAIN`

### Generate APP_KEY (do this on your computer)

From the repo root:

- `php artisan key:generate --show`

Copy the output into Railway as `APP_KEY`.

## 5) First deploy + run migrations

After the first successful build/deploy, run migrations on Railway **once**:

- `php artisan migrate --force`

If your app uses `public/storage` (user uploads), also run:

- `php artisan storage:link`

## 6) Verify the app

- Open the Railway service URL.
- Check logs if you see a 500 error.

Common causes:
- Missing `APP_KEY`
- Wrong DB variables
- PostGIS extension not enabled

## 7) Notes (optional)

- If you later use queues/scheduled tasks, you’ll typically add a separate worker/cron process on Railway. If you tell me what jobs/scheduling you’re using, I can guide that setup too.
