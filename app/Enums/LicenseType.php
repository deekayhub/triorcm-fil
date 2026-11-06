<?php

namespace App\Enums;

enum LicenseType: string
{
    case StateLicense = 'state_license';

    case Dea = 'dea';

    case Cds = 'cds';

    case BoardCert = 'board_cert';
}
