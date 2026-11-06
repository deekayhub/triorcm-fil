<?php

namespace App\Enums;

enum LoginEventStatus: string
{
    case Success = 'success';

    case Failed = 'failed';
}
