<?php

namespace App\Enums;

enum WaterQualityStatus: string
{
    case GOOD = 'good';
    case MODERATE = 'moderate';
    case POOR = 'poor';
    case UNKNOWN = 'unknown';
}
