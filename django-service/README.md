# Overdue Task Service – Django

Handles overdue task rules against the MySQL `tasks` table created by the Laravel migrations (unmanaged model).

Rules:
- `due_date` in the past and status not DONE → OVERDUE
- Overdue tasks cannot move back to IN_PROGRESS
- Only admins can close (CLOSED) overdue tasks

## Setup

```
python -m venv venv
venv\Scripts\activate
pip install -r requirements.txt
copy .env.example .env
python manage.py runserver 8001
```

The schema is managed only by the Laravel migrations (`php artisan migrate`). This service has no migrations of its own and `manage.py migrate` is not needed. Run the Laravel migrations first. Requests need the `X-Internal-Token` header matching `INTERNAL_API_TOKEN`.

## Endpoints

- `POST /api/overdue/sync/` – mark all past-due tasks OVERDUE
- `POST /api/tasks/{id}/transition/` – body `{ "status": "...", "actor_role": "admin|user" }`

Management command: `python manage.py mark_overdue`
