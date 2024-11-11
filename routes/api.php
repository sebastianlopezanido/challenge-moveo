<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

//role:user routes
Route::middleware(['auth:sanctum','role:user', 'throttle:api'])->group(function () {

    Route::get('/user', function (Request $request) {
        return response()->json([
            'status' => 'success',
            'data' => $request->user(),
            'message' => 'User access granted'
        ]);
    });

    Route::apiResource('posts', PostController::class);
    Route::apiResource('posts.comments', CommentController::class)->shallow();

});


//role:admin routes
Route::middleware(['auth:sanctum', 'role:admin', 'throttle:authenticated'])->get('/admin', function (Request $request) {
    return response()->json([
        'status' => 'success',
        'data' => $request->user(),
        'message' => 'Admin access granted'
    ]);
});

