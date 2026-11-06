<?php

namespace App\Enums;

enum DocumentType: string
{
    case MedicalLicense = 'medical_license';

    case DeaCertificate = 'dea_certificate';

    case CdsCertificate = 'cds_certificate';

    case W9 = 'w9';

    case MalpracticeInsurance = 'malpractice_insurance';

    case BoardCertificate = 'board_certificate';

    case Cv = 'cv';

    case Other = 'other';
}
