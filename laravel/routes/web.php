<?php

use App\Http\Controllers\NoticeController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/notices');

// GET /notices, GET /notices/create, POST /notices, GET /notices/{notice},
// GET /notices/{notice}/edit, PUT/PATCH /notices/{notice}, DELETE /notices/{notice}
// Run `php artisan route:list` to see them all.
Route::resource('notices', NoticeController::class);
