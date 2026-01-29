<?php

use App\Http\Middleware\Admin;
use App\Http\Middleware\AdminOrDoctor;
use App\Http\Middleware\Doctor;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->appendToGroup('admin',[
            Admin::class
        ]);
        $middleware->appendToGroup('doctor',[
            Doctor::class
        ]);
        $middleware->appendToGroup('admin_doctor',[
            AdminOrDoctor::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
