<?php

namespace App\Services\ML;

use Symfony\Component\Process\Process;

class JavaWekaClient
{
    private string $javaPath;
    /** @var array<int, string> */
    private array $javaOptions;

    public function __construct(string $javaPath = 'java', array $javaOptions = [])
    {
        $this->javaPath = $javaPath;
        $this->javaOptions = array_values(array_filter($javaOptions, static fn ($option) => is_string($option) && trim($option) !== ''));
    }

    /**
     * Ejecuta un jar de Weka con parámetros y devuelve la salida.
     * Implementación mínima; adaptar según la integración Java que uses.
     */
    public function runJar(
        string $jarPath,
        array $args = [],
        ?int $timeoutSeconds = null,
        ?string $input = null,
        array $extraJavaOptions = []
    ): string
    {
        $runtimeOptions = array_values(array_filter(array_merge($this->javaOptions, $extraJavaOptions), static fn ($option) => is_string($option) && trim($option) !== ''));
        $cmd = array_merge([$this->javaPath, ...$runtimeOptions, '-jar', $jarPath], $args);

        $process = new Process($cmd);
        if ($timeoutSeconds !== null) {
            $process->setTimeout($timeoutSeconds);
        }

        if ($input !== null) {
            $process->setInput($input);
        }

        $process->run();

        if (! $process->isSuccessful()) {
            $error = $process->getErrorOutput() ?: $process->getOutput();
            if (empty(trim($error))) {
                $error = "Process failed with exit code {$process->getExitCode()}. Command: " . $process->getCommandLine();
            }
            throw new \RuntimeException($error);
        }

        return $process->getOutput();
    }
}
