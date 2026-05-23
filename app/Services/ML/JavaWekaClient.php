<?php

namespace App\Services\ML;

use Symfony\Component\Process\Process;

class JavaWekaClient
{
    private string $javaPath;

    public function __construct(string $javaPath = 'java')
    {
        $this->javaPath = $javaPath;
    }

    /**
     * Ejecuta un jar de Weka con parámetros y devuelve la salida.
     * Implementación mínima; adaptar según la integración Java que uses.
     */
    public function runJar(string $jarPath, array $args = []): string
    {
        $cmd = array_merge([$this->javaPath, '-jar', $jarPath], $args);

        $process = new Process($cmd);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput() ?: $process->getOutput());
        }

        return $process->getOutput();
    }
}
