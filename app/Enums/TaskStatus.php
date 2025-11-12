<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Pending = 'pending';
    case Cancelled = 'cancelled';
    case Completed = 'completed';
}
