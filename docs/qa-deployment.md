# QA Deployment Guide

This guide outlines a lightweight way to spin up a temporary **QA (Quality Assurance)** environment without disturbing existing development or production configuration.

---

## 1. Environment Overview

| Component | Purpose | Suggested Temp Hosting | Notes |
|-----------|---------|------------------------|-------|
| Backend (Laravel API) | Expose REST endpoints used by the SPA | [Railway](https://railway.app/), [Render Free Web Service](https://render.com) | Both provide managed Postgres add-ons and simple deploy from GitHub |
| Frontend (Vue SPA) | Public QA site for testers | [Vercel](https://vercel.com), [Netlify](https://www.netlify.com) | Zero-config for Vite/PNPM builds, easy env-var management |
| Database | Persist QA data | Managed Postgres on Railway/Render | Use separate schema to keep QA data isolated |

---

## 2. Backend Preparation

1. **Environment File**
   - Copy your local `.env` to `.env.qa` (this file is ignored by Git).
   - Update the following keys for QA:
     ```env
     APP_ENV=qa
     APP_DEBUG=false
     APP_URL=https://your-qa-api.example.com
     
     # Database (example for Railway Postgres)
     DB_CONNECTION=pgsql
     DB_HOST=containers-us-west-xx.railway.app
     DB_PORT=xxxxx
     DB_DATABASE=student_apply_qa
     DB_USERNAME=postgres
     DB_PASSWORD=super-secure-password
     
     # Sanctum / CORS
     SANCTUM_STATEFUL_DOMAINS=qa.studentapply.test
     SESSION_DOMAIN=.studentapply.test
     
     # Frontend URL allowed by CORS
     FRONTEND_URL=https://qa.studentapply.test
     ```

2. **One-time QA Build Commands**
   ```bash
   cd backend
   composer install --no-dev --optimize-autoloader
   php artisan key:generate --env=qa --force
   php artisan migrate --env=qa --force
   php artisan db:seed --env=qa --force
   php artisan config:cache
   php artisan route:cache
   php artisan queue:work   # optional for async jobs
   ```

3. **Deployment Scripts**
   - For Railway: deploy directly from GitHub repository → set environment to “QA” and add `.env` entries through Railway dashboard.
   - For Render: create a new “Web Service”, point to `backend` directory, set build command to `composer install && php artisan migrate --force`.

4. **Storage (Optional)**
   - If you need file uploads, enable an S3 bucket (e.g. AWS S3, DigitalOcean Spaces), and set `FILESYSTEM_DISK=s3` with associated credentials.

---

## 3. Frontend Preparation

1. **QA Environment Variables**
   - Add a `.env.qa` at the project root (ignored by git):
     ```bash
     VITE_API_BASE_URL=https://your-qa-api.example.com
     VITE_APP_ENV=qa
     ```

2. **Build Commands**
   ```bash
   cd frontend
   pnpm install
   VITE_API_BASE_URL=https://your-qa-api.example.com pnpm build
   ```

3. **Hosting**
   - **Vercel**: Create a new project, point to `frontend`, set build command `pnpm build` and output `dist/`. Add `VITE_*` env vars in the dashboard.
   - **Netlify**: Similar, set build command `pnpm build`, publish directory `dist`.

4. **Routing**
   - Ensure the hosting platform rewrites all SPA routes to `/index.html`. (Vercel & Netlify handle this automatically for Vite.)

---

## 4. Suggested Hosting Flow

1. **GitHub Branch**: create a branch such as `qa`.
2. **CI/CD**:
   - Configure Vercel/Netlify & Railway/Render to deploy automatically from the `qa` branch.
   - Whenever you merge changes to `qa`, both frontend and backend QA environments redeploy.
3. **Feature Flags**:
   - You can conditionally enable QA-only features using `APP_ENV === 'qa'` (backend) or `import.meta.env.VITE_APP_ENV`.

---

## 5. Cleanup & Cost Management

| Platform | Free Tier Notes | Cleanup |
|----------|-----------------|---------|
| Railway | Free credit each month; service sleeps on inactivity | Delete the Railway project when finished |
| Render | Free instance sleeps after 15 minutes | Delete service/database when QA ends |
| Vercel | Free hobby tier good for QA | Remove branch deployment after testing |
| Netlify | Generous free tier | Remove QA app when finished |

---

## 6. Quick Checklist

- [ ] `.env.qa` (backend) with QA DB credentials.
- [ ] `.env.qa` (frontend) with QA API URL.
- [ ] QA database migrated & seeded.
- [ ] CORS/Sanctum domains set: QA frontend ↔ QA backend.
- [ ] Hosting services connected to `qa` branch and environment variables set.
- [ ] SPA domain whitelisted in backend config (`FRONTEND_URL`).
- [ ] Provide QA credentials to testers (e.g., seeded staff/student logins).

Once QA is complete, remove temporary services to avoid unnecessary cost. This approach isolates QA from dev/prod while remaining inexpensive and low maintenance. 

