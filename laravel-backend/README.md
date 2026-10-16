# Task & Project Management – Laravel API

Laravel 13 + Sanctum + MySQL. Overdue-status rules are delegated to the Django service (`../django-service`).

## Setup

```
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve --port=8010
```

`.env` must point to the same MySQL database as the Django service and contain `DJANGO_SERVICE_URL` and `DJANGO_SERVICE_TOKEN` (equal to Django's `INTERNAL_API_TOKEN`).

## Test credentials

| Role  | Email             | Password  |
|-------|-------------------|-----------|
| Admin | admin@example.com | Admin@123 |
| User  | user@example.com  | User@123  |
| User  | other@example.com | User@123  |

## Endpoints (prefix `/api`)

| Method | Path                       | Access        |
|--------|----------------------------|---------------|
| POST   | /register, /login          | public        |
| POST   | /logout, GET /me           | authenticated |
| GET    | /projects, /projects/{id}  | authenticated (users see only their tasks) |
| GET    | /tasks                     | authenticated (users see only their tasks) |
| PATCH  | /tasks/{id}/status         | admin or assignee |
| GET    | /users                     | admin         |
| POST/PUT/DELETE | /projects, /projects/{id} | admin |
| POST   | /projects/{id}/tasks       | admin         |
| PUT/DELETE | /tasks/{id}            | admin         |

All responses use `{ success, message, data, errors }`.
