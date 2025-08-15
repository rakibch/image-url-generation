# Image URL Thumbnail Queue – Laravel 12 + Sanctum + Vue 3 (Vite 7)

A Laravel 12 + Sanctum + Vue 3 application for bulk image URL submission with tier-based quotas and queued processing.

---

## Prerequisites
- PHP 8.2+, Composer
- Node.js 20+ (required for Vite 7)
- MySQL/MariaDB/PostgreSQL/SQLite
- npm

---

## Setup

```bash
# Backend
composer install
cp .env.example .env
php artisan key:generate
# Configure DB & QUEUE_CONNECTION=database in .env
php artisan migrate

# Sanctum
composer require laravel/sanctum
php artisan sanctum:install

# Frontend
npm install
npm install vue@3 bootstrap @popperjs/core
npm install -D @vitejs/plugin-vue@6.0.0
```

`vite.config.js`:
```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
  plugins: [
    laravel({ input: ['resources/js/app.js'], refresh: true }),
    vue(),
  ],
});
```

---

## Running

Open three terminals:

```bash
# 1. Laravel
php artisan serve

# 2. Queue worker
php artisan queue:work --queue=enterprise,pro,free

# 3. Vite dev server
npm run dev
```

Visit: [http://127.0.0.1:8000](http://127.0.0.1:8000)

---

## Usage
1. Register/login (plan: free=50, pro=100, enterprise=200 URLs/request).
2. Paste image URLs (1 per line) → Submit.
3. Jobs are queued by priority; status auto-updates.

---

## Production
```bash
npm run build
php artisan config:cache
php artisan route:cache
php artisan queue:work --daemon --queue=enterprise,pro,free
```

---

## Notes
- Requires Node 20+ to avoid Vite `crypto.hash` errors.
- Blade must include:
```blade
@vite('resources/js/app.js')
<div id="app"><bulk-uploader></bulk-uploader></div>
```
