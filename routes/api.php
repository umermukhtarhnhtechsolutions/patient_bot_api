<?php

use App\Http\Controllers\Api\{AppointmentController, AuthController, DashboardController, DoctorController};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'loginProcess']);
Route::post('/reset', [AuthController::class, 'resetPasswordProcess']);
Route::post('/forgot', [AuthController::class, 'forgotProcess']);

Route::middleware('auth:api')->group(function () {
    Route::get('/profile', [AuthController::class, 'currentUser']);
    Route::post('/profile/update', [AuthController::class, 'updateProfile']);
    Route::post('/changepassword',  [AuthController::class, 'changePassword']);

    Route::group(['middleware' => 'admin'], function () {
        //Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index']);
        //Doctor
        Route::apiResources([
            '/doctor'  => DoctorController::class,
        ]);

        Route::get('/doctor-status-change/{id}', [DoctorController::class, 'statusChange']);
    });

    Route::group(['middleware' => 'admin_doctor'], function () {
        //Appointment
        Route::get('/appointment', [AppointmentController::class, 'index']);
        Route::get('/appointment/{appointment}', [AppointmentController::class, 'show']);
        Route::post('/appointment-status-change', [AppointmentController::class, 'statusChange']);
    });
});