<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Todo = 'TODO';
    case InProgress = 'IN_PROGRESS';
    case Done = 'DONE';
    case Overdue = 'OVERDUE';
    case Closed = 'CLOSED';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
