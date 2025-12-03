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

use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\PackageServiceController;

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

        // Services
        Route::resource('services', ServiceController::class);
        Route::post('services/{service}/toggle-status', [ServiceController::class, 'toggleStatus'])
            ->name('services.toggle-status');
        
        // Packages
        Route::resource('packages', PackageController::class);
        Route::post('packages/{package}/toggle-status', [PackageController::class, 'toggleStatus'])
            ->name('packages.toggle-status');
        Route::post('packages/{package}/toggle-featured', [PackageController::class, 'toggleFeatured'])
            ->name('packages.toggle-featured');
        
        // Package Services (Assignment)
        Route::get('package-services', [PackageServiceController::class, 'index'])
            ->name('package-services.index');
        Route::get('package-services/assign', [PackageServiceController::class, 'assign'])
            ->name('package-services.assign');
        Route::post('package-services/assign', [PackageServiceController::class, 'storeAssignment'])
            ->name('package-services.store-assignment');
        Route::put('package-services/{packageService}', [PackageServiceController::class, 'updateSingle'])
            ->name('package-services.update');
        Route::delete('package-services/{packageService}', [PackageServiceController::class, 'destroy'])
            ->name('package-services.destroy');
        Route::post('package-services/bulk-assign', [PackageServiceController::class, 'bulkAssign'])
            ->name('package-services.bulk-assign');


    });
    
});