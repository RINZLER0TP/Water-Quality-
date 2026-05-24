<?php

return [
    'java_path' => env('WEKA_JAVA_PATH', 'java'),
    'jar_path' => env('WEKA_JAR_PATH', base_path('java/weka-training-engine/target/weka-training-engine.jar')),
    'models_path' => env('WEKA_MODELS_PATH', 'weka/models'),
    'logs_path' => env('WEKA_LOGS_PATH', 'weka/logs'),
    'timeout_seconds' => (int) env('WEKA_TRAINING_TIMEOUT', 1800),
    'cross_validation_folds' => (int) env('WEKA_CV_FOLDS', 10),
    'random_seed' => (int) env('WEKA_RANDOM_SEED', 42),
];
