<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            return response()->json([
                'error' => 'Unauthorized.',
                'message' => 'You are not authorized to access the content.'
            ], 401);
        })
        ->render(function (AccessDeniedHttpException $e, Request $request) {
            return response()->json([
                'error' => 'Error has occured.',
                'message' => 'You are not authorized to make this request.'
            ], 403);
        })
        ->render(function (NotFoundHttpException $e, Request $request) {
            return response()->json([
                'error' => 'Error has occured.',
                'message' => 'Server cannot find the requested resource.'
            ], 404);
        });
    })->create();
