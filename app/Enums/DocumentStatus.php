<?php

namespace App\Enums;

enum DocumentStatus: string
{
    case Valid = 'valid';

    case Expiring = 'expiring';

    case Expired = 'expired';

    case Missing = 'missing';

    case UnderReview = 'under_review';
}
