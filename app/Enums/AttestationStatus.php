<?php

namespace App\Enums;

enum AttestationStatus: string
{
    case Current = 'current';

    case DueSoon = 'due_soon';

    case Overdue = 'overdue';
}
