<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait HandlesMediaCleanup
{
    /**
     * Recursively find file paths in old data that are no longer in new data and delete them.
     */
    protected function cleanupFiles(array $old, array $new, string $disk = 'public'): void
    {
        $oldFiles = $this->extractFiles($old);
        $newFiles = $this->extractFiles($new);

        $toDelete = array_diff($oldFiles, $newFiles);

        foreach ($toDelete as $file) {
            if ($file && Storage::disk($disk)->exists($file)) {
                Storage::disk($disk)->delete($file);
            }
        }
    }

    protected function extractFiles(array $data): array
    {
        $files = [];
        $extensions = ['.jpg', '.jpeg', '.png', '.gif', '.svg', '.webp', '.mp4', '.pdf', '.doc', '.docx'];

        foreach ($data as $val) {
            if (is_array($val)) {
                $files = array_merge($files, $this->extractFiles($val));
            } elseif (is_string($val) && trim($val) !== '') {
                $lower = strtolower($val);
                $isMatch = false;
                foreach ($extensions as $ext) {
                    if (str_contains($lower, $ext)) {
                        $isMatch = true;
                        break;
                    }
                }

                if ($isMatch) {
                    $path = $val;
                    // If it's a URL, extract only the path
                    if (str_contains($path, '://')) {
                        $path = parse_url($path, PHP_URL_PATH);
                    }
                    
                    // Standardize path (remove leading slashes and storage/ prefix)
                    $path = ltrim($path, '/');
                    if (str_starts_with($path, 'storage/')) {
                        $path = substr($path, 8);
                    }

                    $files[] = $path;
                }
            }
        }
        return array_unique($files);
    }
}
