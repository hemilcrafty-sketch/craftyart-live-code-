<?php

namespace App\Http\Controllers\Utils;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class StorageUtils
{

    // Storage::disk('gcs');

    public static function makeDirectory($dir): void
    {
        if (!Storage::exists($dir)) {
            Storage::makeDirectory($dir);
        }
    }

    public static function storeAs($file, $path, $name): void
    {
        $file->storeAs($path, $name, 'public');
    }

    public static function put($path, $data): void
    {
        Storage::put($path, $data);
    }

    public static function putFileAs($dir, $file, $name): void
    {
        Storage::putFileAs($dir, $file, $name);
    }

    public static function get($path): string
    {
        return Storage::get($path);
    }

    public static function exists($path): bool
    {
        return Storage::exists($path);
    }

    public static function delete($file): void
    {
        try {
            if (Storage::exists($file)) {
                Storage::delete($file);
            }
        } catch (\Exception $e) {
        }
    }

    public static function getNewName(): string
    {
        $bytes = random_bytes(20);
        return bin2hex($bytes) . Carbon::now()->timestamp;
    }

}