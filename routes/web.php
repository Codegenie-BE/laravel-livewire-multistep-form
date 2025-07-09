<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');
Route::get('/thankyou', function () {
    if (!session()->pull('form_submitted')) {
        return redirect()->route('home');
    }

    return view('thankyou');
})->name('thankyou');