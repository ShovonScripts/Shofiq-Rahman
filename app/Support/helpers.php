<?php

use Illuminate\Support\Facades\Storage;

if (! function_exists('storage_url')) {
    function storage_url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        $path = ltrim($path, '/');
        $path = preg_replace('#^(?:storage/|public/)+#', '', $path);

        return Storage::url($path);
    }
}
