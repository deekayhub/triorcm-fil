<?php

use Illuminate\Support\Facades\Artisan;
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
