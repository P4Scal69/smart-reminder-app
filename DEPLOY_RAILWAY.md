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

In Railway: open your **Laravel service** → **Variables** and set at least:

### Required

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_KEY=` (generate this locally; see below)

### Database (map from the Railway Postgres variables)

Set Laravel DB vars to match the Railway Postgres values:

- `DB_CONNECTION=pgsql`
- `DB_HOST=` (Railway host)
- `DB_PORT=5432` (or Railway port)
- `DB_DATABASE=`
- `DB_USERNAME=`
- `DB_PASSWORD=`

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
