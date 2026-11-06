<?php

namespace App\Enums;

enum PracticeType: string
{
    case Solo = 'solo';

    case Group = 'group';

    case Hospital = 'hospital';
}
