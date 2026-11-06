<?php

namespace App\Enums;

enum PayerType: string
{
    case Commercial = 'commercial';

    case Medicare = 'medicare';

    case Medicaid = 'medicaid';

    case Marketplace = 'marketplace';

    case Other = 'other';
}
