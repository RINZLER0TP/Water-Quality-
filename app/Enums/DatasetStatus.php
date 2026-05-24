<?php

namespace App\Enums;

enum DatasetStatus: string
{
    case READY = 'ready';
    case VALIDATED = 'validated';
    case INVALID = 'invalid';
    case PROCESSING = 'processing';

    public function label(): string
    {
        return match ($this) {
            self::READY => 'Listo',
            self::VALIDATED => 'Validado',
            self::INVALID => 'Inválido',
            self::PROCESSING => 'Procesando',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::READY => 'bg-slate-100 text-slate-700 ring-slate-200',
            self::VALIDATED => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            self::INVALID => 'bg-rose-50 text-rose-700 ring-rose-200',
            self::PROCESSING => 'bg-amber-50 text-amber-700 ring-amber-200',
        };
    }
}
