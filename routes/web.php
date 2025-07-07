<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::get('/thankyou', function () {
    if (!session()->has('success')) {
        return redirect()->route('home');
    }

    return view('thankyou');
})->name('thankyou');