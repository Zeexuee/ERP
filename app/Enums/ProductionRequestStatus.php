<?php

namespace App\Enums;

enum ProductionRequestStatus: string
{
    case PENDING = 'pending';
    case IN_PRODUCTION = 'in_production';
    case FINISHED = 'finished';
}
