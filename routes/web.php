<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/clear-cache', function () {
    try {
        Artisan::call('optimize:clear');
    } catch (\Throwable $e) {
        return response()->json([
            'message' => 'Cache clear failed.',
            'error' => $e->getMessage(),
        ], 500);
    }

    return response()->json([
        'message' => 'Cache cleared successfully.',
    ]);
});

Route::get('/fix-storage-symlink', function () {
    try {
        $output = [];
        $symlinkPath = public_path('storage');

        // 1. Remove the old/broken symlink if it exists
        if (File::exists($symlinkPath) || is_link($symlinkPath)) {
            // Using PHP's native unlink to cleanly delete symlinks or broken references
            unlink($symlinkPath);
            $output['step_1'] = 'Old or broken symlink successfully removed.';
        } else {
            $output['step_1'] = 'No existing symlink found to remove.';
        }

        // 2. Create a fresh symbolic link
        Artisan::call('storage:link');
        $output['step_2'] = Artisan::output();

        return response()->json([
            'status' => 'success',
            'details' => $output
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
});

Route::get('/publish-filament-assets', function () {
    try {
        Artisan::call('vendor:publish', [
            '--tag' => 'filament-assets',
            '--force' => true,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Filament assets published successfully.',
            'output' => Artisan::output(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
});

Route::get('/optimize-filament', function () {
    try {
        Artisan::call('filament:optimize');

        return response()->json([
            'status' => 'success',
            'message' => 'Filament optimized successfully.',
            'output' => Artisan::output(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
});
