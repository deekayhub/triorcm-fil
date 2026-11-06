<?php

namespace App\Enums;

enum EnrollmentStage: string
{
    case Intake = 'intake';

    case Verification = 'verification';

    case Submitted = 'submitted';

    case Approved = 'approved';

    case Denied = 'denied';
}
