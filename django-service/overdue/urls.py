from django.urls import path

from . import views

urlpatterns = [
    path('overdue/sync/', views.sync_overdue),
    path('tasks/<int:task_id>/transition/', views.transition),
]
