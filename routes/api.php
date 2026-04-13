<?php

use App\Http\Controllers\AccessController;
use Illuminate\Support\Facades\Route;

Route::post('/request-access', AccessController::class);
