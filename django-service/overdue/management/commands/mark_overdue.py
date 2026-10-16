from django.core.management.base import BaseCommand

from overdue.services import mark_overdue


class Command(BaseCommand):
    help = 'Mark tasks past their due date as OVERDUE'

    def handle(self, *args, **options):
        self.stdout.write(f'{mark_overdue()} task(s) marked overdue')
