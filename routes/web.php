<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BlogCategoryController;
use App\Http\Controllers\Admin\BlogTagController;
use App\Http\Controllers\Admin\BlogPostController;

Route::get('/', function () {
    return redirect('/login');
});

Auth::routes();

Route::middleware(['auth'])->group(function () {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    
    // Profile routes
    //Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'index'])->name('profile.index');
    //Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');

    
    // Admin routes
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        
        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        
        // Users, Roles, Permissions
        Route::resource('users', UserController::class);
        Route::resource('roles', RoleController::class);
        Route::resource('permissions', PermissionController::class);
        
        // Blog Module
        Route::prefix('blog')->name('blog.')->group(function () {
            // Blog Categories
            Route::resource('categories', BlogCategoryController::class);
            
            // Blog Tags
            Route::resource('tags', BlogTagController::class);
            
            // Blog Posts - Image Upload (must be before resource route)
            Route::post('posts/upload-image', [BlogPostController::class, 'uploadImage'])->name('posts.upload-image');
            
            // Blog Posts
            Route::resource('posts', BlogPostController::class);
        });
    });
    
});