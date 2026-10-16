import json
from functools import wraps

from django.conf import settings
from django.http import JsonResponse
from django.views.decorators.csrf import csrf_exempt
from django.views.decorators.http import require_POST

from .services import TransitionError, mark_overdue, transition_task


def respond(success, message, data=None, errors=None, status=200):
    return JsonResponse(
        {'success': success, 'message': message, 'data': data, 'errors': errors},
        status=status,
    )


def internal_only(view):
    @wraps(view)
    def wrapper(request, *args, **kwargs):
        expected = settings.INTERNAL_API_TOKEN
        if not expected or request.headers.get('X-Internal-Token') != expected:
            return respond(False, 'Unauthorized.', status=401)
        return view(request, *args, **kwargs)

    return wrapper


@csrf_exempt
@require_POST
@internal_only
def sync_overdue(request):
    updated = mark_overdue()
    return respond(True, 'Overdue tasks synchronised.', {'updated': updated})


@csrf_exempt
@require_POST
@internal_only
def transition(request, task_id):
    try:
        payload = json.loads(request.body or '{}')
    except json.JSONDecodeError:
        return respond(False, 'Invalid JSON body.', status=400)

    status = payload.get('status')
    actor_role = payload.get('actor_role')

    errors = {}
    if not status:
        errors['status'] = ['This field is required.']
    if not actor_role:
        errors['actor_role'] = ['This field is required.']
    if errors:
        return respond(False, 'Validation failed.', errors=errors, status=422)

    try:
        task = transition_task(task_id, status, actor_role)
    except TransitionError as error:
        return respond(False, error.message, status=error.status_code)

    return respond(True, 'Task status updated.', {'id': task.pk, 'status': task.status})
