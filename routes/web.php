<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome')->with('version', app()
        ->version());
})->name('welcome');
