<?php

declare(strict_types=1);

use CraftCms\GatsbyHelper\Http\Controllers\PreviewPayloadController;
use Illuminate\Support\Facades\Route;

// Reachable at site action URLs too, so it requires a control panel user.
Route::middleware(['auth', 'can:accessCp'])->group(function () {
    Route::get('preview-payload', PreviewPayloadController::class);
});
