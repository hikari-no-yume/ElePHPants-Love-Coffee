<?php
declare(strict_types=1);

namespace ajf\ElePHPants_Love_Coffee;

class PHPDbg
{
    private $binaryPath;

    public function __construct(string $binaryPath) {
        $this->binaryPath = $binaryPath;
    }

    public function showFile(string $filePath): array {
        exec(
            escapeshellarg($this->binaryPath)
            . ' -p '
            . escapeshellarg($filePath),
            $lines,
            $exit_status
        );
        if ($exit_status) {
            throw new \Exception("Error when running phpdbg and fetching file: " . $filePath);
        }
        return $lines;
    }

    public function showFileFunction(string $filePath, string $functionName): array {
        exec(
            escapeshellarg($this->binaryPath)
            . ' -p=' . escapeshellarg($functionName)
            . ' ' . escapeshellarg($filePath),
            $lines,
            $exit_status
        );
        if ($exit_status) {
            throw new \Exception("Error when running phpdbg and fetching function: " . $functionName);
        }
        return $lines;
    }
}
