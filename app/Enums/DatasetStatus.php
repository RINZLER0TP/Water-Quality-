<?php

namespace App\Enums;

enum DatasetStatus: string
{
    case READY = 'ready';
    case VALIDATED = 'validated';
    case INVALID = 'invalid';
    case PROCESSING = 'processing';
}
