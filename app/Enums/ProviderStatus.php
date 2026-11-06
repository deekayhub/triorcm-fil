<?php

namespace App\Enums;

enum ProviderStatus: string
{
    case Active = 'active';

    case Pending = 'pending';

    case Inactive = 'inactive';
}
