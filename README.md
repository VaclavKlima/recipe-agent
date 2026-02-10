# Recipe Agent

A Laravel 12 app that helps generate and manage recipes with a Livewire-powered assistant.

## Requirements

- PHP 8.4
- Composer
- Node.js + npm
- Laravel Herd (recommended)

## Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Configure your `.env` as needed (database, Prism/LLM provider, etc.).

## Database

```bash
php artisan migrate
php artisan db:seed
```

## Development

```bash
npm run dev
php artisan serve
```

If you use Laravel Herd, you can open the app at the Herd URL instead of running `php artisan serve`.

## Tests

```bash
php artisan test --compact
```
