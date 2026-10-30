# MarketGames

MarketGames is a web application with a Vue 3 frontend and a Laravel API backend.

## Project layout

- `frontend/` - Vue 3 application built with Vite
- `backend/laravel/` - Laravel API

## Requirements

- Node.js 22.18 or newer (or 24.12 or newer)
- PHP 8.3 or newer
- Composer

## Run locally

Install the frontend dependencies:

```sh
cd frontend
npm install
npm run dev
```

In a separate terminal, install and configure the backend:

```sh
cd backend/laravel
composer install
```

Copy `.env.example` to `.env`, configure the database and other settings for your environment, then generate an application key and run migrations:

```sh
php artisan key:generate
php artisan migrate
php artisan serve
```

The frontend API client uses `http://127.0.0.1:8000/api` by default.

## Tests and production build

Run backend tests from `backend/laravel`:

```sh
php artisan test
```

Build the frontend from `frontend`:

```sh
npm run build
```

Local environment files, databases, dependencies, and build output are excluded from Git. Configure secrets in your own `.env` file; do not commit them.
