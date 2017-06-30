<?php


// Verification
Route::get('register/verify', 'App\Http\Controllers\Auth\RegisterController@verify')->name('verifyEmailLink');



