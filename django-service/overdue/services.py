from datetime import datetime, timezone as dt_timezone

from django.db import transaction

from .models import Task


def utc_now():
    return datetime.now(dt_timezone.utc).replace(tzinfo=None)


def utc_today():
    return utc_now().date()


class TransitionError(Exception):
    def __init__(self, message, status_code=422):
        super().__init__(message)
        self.message = message
        self.status_code = status_code


def mark_overdue(task_id=None):
    queryset = Task.objects.filter(due_date__lt=utc_today()).exclude(
        status__in=[Task.DONE, Task.OVERDUE, Task.CLOSED]
    )
    if task_id is not None:
        queryset = queryset.filter(pk=task_id)
    return queryset.update(status=Task.OVERDUE, updated_at=utc_now())


@transaction.atomic
def transition_task(task_id, target, actor_role):
    if target not in Task.STATUSES:
        raise TransitionError('Invalid status.')

    try:
        task = Task.objects.select_for_update().get(pk=task_id)
    except Task.DoesNotExist:
        raise TransitionError('Task not found.', 404)

    mark_overdue(task_id)
    task.refresh_from_db()

    if target == Task.OVERDUE:
        raise TransitionError('OVERDUE is assigned automatically and cannot be set manually.')

    if task.status == Task.CLOSED:
        raise TransitionError('Closed tasks cannot be changed.')

    if task.status == target:
        return task

    past_due = task.due_date < utc_today()

    if past_due and target in (Task.TODO, Task.IN_PROGRESS):
        raise TransitionError(
            'Overdue tasks cannot move back to IN_PROGRESS.'
            if target == Task.IN_PROGRESS
            else 'Overdue tasks cannot move back to TODO.'
        )

    if target == Task.CLOSED:
        if task.status != Task.OVERDUE:
            raise TransitionError('Only overdue tasks can be closed.')
        if actor_role != 'admin':
            raise TransitionError('Only admins can close overdue tasks.', 403)

    task.status = target
    task.updated_at = utc_now()
    task.save(update_fields=['status', 'updated_at'])
    return task
