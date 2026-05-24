<?php

namespace App\Enums;

enum TrainingAlgorithm: string
{
    case ZERO_R = 'zeror';
    case ONE_R = 'oner';
    case NAIVE_BAYES = 'naive_bayes';
    case LOGISTIC = 'logistic';

    public function label(): string
    {
        return match ($this) {
            self::ZERO_R => 'ZeroR',
            self::ONE_R => 'OneR',
            self::NAIVE_BAYES => 'NaiveBayes',
            self::LOGISTIC => 'Logistic',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::ZERO_R => 'Baseline sin parámetros. Predice la clase mayoritaria.',
            self::ONE_R => 'Genera reglas simples de un solo atributo.',
            self::NAIVE_BAYES => 'Clasificador probabilístico con soporte nominal y numérico.',
            self::LOGISTIC => 'Modelo lineal de clasificación logística compatible con Weka.',
        };
    }

    public function defaultParameters(): array
    {
        return match ($this) {
            self::ZERO_R => [],
            self::ONE_R => [
                'bucket_size' => 6,
            ],
            self::NAIVE_BAYES => [
                'use_kernel_estimator' => false,
                'use_supervised_discretization' => false,
            ],
            self::LOGISTIC => [
                'ridge' => 0.00000001,
                'max_iterations' => 500,
            ],
        };
    }

    public function parameterSchema(): array
    {
        return match ($this) {
            self::ZERO_R => [],
            self::ONE_R => [
                [
                    'name' => 'bucket_size',
                    'label' => 'Tamaño mínimo de bucket',
                    'type' => 'number',
                    'step' => 1,
                    'min' => 1,
                    'default' => 6,
                    'help' => 'Controla cuántos valores agrupa el algoritmo al construir reglas.',
                ],
            ],
            self::NAIVE_BAYES => [
                [
                    'name' => 'use_kernel_estimator',
                    'label' => 'Usar kernel estimator',
                    'type' => 'boolean',
                    'default' => false,
                    'help' => 'Activa una estimación más flexible para atributos numéricos.',
                ],
                [
                    'name' => 'use_supervised_discretization',
                    'label' => 'Discretización supervisada',
                    'type' => 'boolean',
                    'default' => false,
                    'help' => 'Convierte atributos continuos en intervalos supervisados por la clase.',
                ],
            ],
            self::LOGISTIC => [
                [
                    'name' => 'ridge',
                    'label' => 'Ridge',
                    'type' => 'number',
                    'step' => 'any',
                    'min' => 0,
                    'default' => 0.00000001,
                    'help' => 'Regularización para estabilizar la regresión logística.',
                ],
                [
                    'name' => 'max_iterations',
                    'label' => 'Iteraciones máximas',
                    'type' => 'number',
                    'step' => 1,
                    'min' => 1,
                    'default' => 500,
                    'help' => 'Límite de optimización interna antes de detener el entrenamiento.',
                ],
            ],
        };
    }

    public static function options(): array
    {
        return array_map(
            static fn (self $algorithm): array => [
                'value' => $algorithm->value,
                'label' => $algorithm->label(),
                'description' => $algorithm->description(),
            ],
            self::cases()
        );
    }

    public static function values(): array
    {
        return array_map(static fn (self $algorithm): string => $algorithm->value, self::cases());
    }
}