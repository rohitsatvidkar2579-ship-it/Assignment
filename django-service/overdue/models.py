from django.db import models


class Task(models.Model):
    TODO = 'TODO'
    IN_PROGRESS = 'IN_PROGRESS'
    DONE = 'DONE'
    OVERDUE = 'OVERDUE'
    CLOSED = 'CLOSED'

    STATUSES = [TODO, IN_PROGRESS, DONE, OVERDUE, CLOSED]

    project_id = models.BigIntegerField()
    assigned_to = models.BigIntegerField(null=True)
    created_by = models.BigIntegerField()
    title = models.CharField(max_length=255)
    description = models.TextField(null=True)
    status = models.CharField(max_length=20)
    priority = models.CharField(max_length=10)
    due_date = models.DateField()
    created_at = models.DateTimeField(null=True)
    updated_at = models.DateTimeField(null=True)

    class Meta:
        managed = False
        db_table = 'tasks'
