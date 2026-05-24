<?php

namespace App\Enums;

enum TrainingJobStatus: string
{
    case PENDING = 'pending';
    case RUNNING = 'running';
    case COMPLETED = 'completed';
    case FAILED = 'failed';

    public static function values(): array
    {
        return array_map(static fn (self $status): string => $status->value, self::cases());
    }

    public static function options(): array
    {
        return [
            self::PENDING->value => 'Pendiente',
            self::RUNNING->value => 'En ejecución',
            self::COMPLETED->value => 'Completado',
            self::FAILED->value => 'Fallido',
        ];
    }

    public function label(): string
    {
        return self::options()[$this->value] ?? ucfirst($this->value);
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::COMPLETED, self::FAILED], true);
    }
}
