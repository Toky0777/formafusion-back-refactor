<?php

use App\Http\Controllers\SpaController;
use App\Http\Controllers\SupportCoursController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

Route::get('/{any}', SpaController::class)->where('any', '.*');
