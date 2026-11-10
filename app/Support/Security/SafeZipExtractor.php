<?php

namespace App\Support\Security;

use Illuminate\Support\Facades\File;
use RuntimeException;
use ZipArchive;

class SafeZipExtractor
{
    /**
     * Extract a zip into $destination, rejecting zip-slip / absolute paths.
     *
     * @return list<string> Extracted relative entry names
     */
    public function extract(string $archive, string $destination): array
    {
        File::ensureDirectoryExists($destination);
        $destinationReal = realpath($destination);
        if ($destinationReal === false) {
            throw new RuntimeException('Unable to resolve extract destination.');
        }

        $zip = new ZipArchive;
        if ($zip->open($archive) !== true) {
            throw new RuntimeException('Unable to open zip archive.');
        }

        $extracted = [];

        try {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if (! is_string($name) || $name === '') {
                    continue;
                }

                $this->assertSafeEntryName($name);

                $target = $destinationReal.DIRECTORY_SEPARATOR.str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $name);
                $targetDir = str_ends_with($name, '/') ? $target : dirname($target);

                if (! is_dir($targetDir)) {
                    File::ensureDirectoryExists($targetDir);
                }

                $resolvedDir = realpath($targetDir);
                if ($resolvedDir === false || ! str_starts_with($resolvedDir, $destinationReal)) {
                    throw new RuntimeException("Unsafe zip path rejected: {$name}");
                }

                if (str_ends_with($name, '/')) {
                    $extracted[] = $name;

                    continue;
                }

                $stream = $zip->getStream($name);
                if ($stream === false) {
                    throw new RuntimeException("Unable to read zip entry: {$name}");
                }

                $out = fopen($target, 'wb');
                if ($out === false) {
                    fclose($stream);
                    throw new RuntimeException("Unable to write zip entry: {$name}");
                }

                stream_copy_to_stream($stream, $out);
                fclose($stream);
                fclose($out);

                $resolvedFile = realpath($target);
                if ($resolvedFile === false || ! str_starts_with($resolvedFile, $destinationReal)) {
                    @unlink($target);
                    throw new RuntimeException("Unsafe zip extract rejected: {$name}");
                }

                $extracted[] = $name;
            }
        } finally {
            $zip->close();
        }

        return $extracted;
    }

    public function assertSafeEntryName(string $name): void
    {
        $normalized = str_replace('\\', '/', $name);

        if ($normalized === '' || str_starts_with($normalized, '/') || preg_match('#^[A-Za-z]:/#', $normalized)) {
            throw new RuntimeException("Absolute zip path rejected: {$name}");
        }

        foreach (explode('/', $normalized) as $segment) {
            if ($segment === '..') {
                throw new RuntimeException("Zip-slip path rejected: {$name}");
            }
        }
    }
}
