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
        Route::get('package-services', [PackageServiceController::class, 'assign'])
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


        // Appointments
        Route::prefix('appointments')->name('appointments.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\AppointmentController::class, 'index'])->name('index');
            Route::get('/calendar', [App\Http\Controllers\Admin\AppointmentController::class, 'calendar'])->name('calendar');
            Route::get('/calendar/events', [App\Http\Controllers\Admin\AppointmentController::class, 'calendarEvents'])->name('calendar.events');
            Route::get('/create', [App\Http\Controllers\Admin\AppointmentController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Admin\AppointmentController::class, 'store'])->name('store');
            Route::get('/export', [App\Http\Controllers\Admin\AppointmentController::class, 'export'])->name('export');
            Route::get('/available-slots', [App\Http\Controllers\Admin\AppointmentController::class, 'getAvailableSlots'])->name('available-slots');
            Route::get('/{appointment}', [App\Http\Controllers\Admin\AppointmentController::class, 'show'])->name('show');
            Route::get('/{appointment}/edit', [App\Http\Controllers\Admin\AppointmentController::class, 'edit'])->name('edit');
            Route::put('/{appointment}', [App\Http\Controllers\Admin\AppointmentController::class, 'update'])->name('update');
            Route::delete('/{appointment}', [App\Http\Controllers\Admin\AppointmentController::class, 'destroy'])->name('destroy');
            Route::post('/{appointment}/confirm', [App\Http\Controllers\Admin\AppointmentController::class, 'confirm'])->name('confirm');
            Route::post('/{appointment}/cancel', [App\Http\Controllers\Admin\AppointmentController::class, 'cancel'])->name('cancel');
            Route::post('/{appointment}/complete', [App\Http\Controllers\Admin\AppointmentController::class, 'complete'])->name('complete');
            Route::post('/{appointment}/start', [App\Http\Controllers\Admin\AppointmentController::class, 'start'])->name('start');
            Route::post('/{appointment}/no-show', [App\Http\Controllers\Admin\AppointmentController::class, 'noShow'])->name('no-show');
            
            Route::get('/today', [App\Http\Controllers\Admin\AppointmentController::class, 'today'])->name('today');

            // Notes
            Route::get('/{appointment}/notes', [App\Http\Controllers\Admin\AppointmentNoteController::class, 'index'])->name('notes.index');
            Route::post('/{appointment}/notes', [App\Http\Controllers\Admin\AppointmentNoteController::class, 'store'])->name('notes.store');
            Route::get('/{appointment}/notes/{note}/edit', [App\Http\Controllers\Admin\AppointmentNoteController::class, 'edit'])->name('notes.edit');
            Route::put('/{appointment}/notes/{note}', [App\Http\Controllers\Admin\AppointmentNoteController::class, 'update'])->name('notes.update');
            Route::post('/{appointment}/notes/{note}/toggle-pin', [App\Http\Controllers\Admin\AppointmentNoteController::class, 'togglePin'])->name('notes.toggle-pin');
            Route::delete('/{appointment}/notes/{note}', [App\Http\Controllers\Admin\AppointmentNoteController::class, 'destroy'])->name('notes.destroy');
        
        });
        
        // Time Slots
        Route::prefix('time-slots')->name('time-slots.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\TimeSlotController::class, 'index'])->name('index');
            Route::get('/calendar', [App\Http\Controllers\Admin\TimeSlotController::class, 'calendar'])->name('calendar');
            Route::get('/calendar/events', [App\Http\Controllers\Admin\TimeSlotController::class, 'calendarEvents'])->name('calendar.events');
            Route::get('/create', [App\Http\Controllers\Admin\TimeSlotController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Admin\TimeSlotController::class, 'store'])->name('store');
            Route::post('/quick-create', [App\Http\Controllers\Admin\TimeSlotController::class, 'quickCreate'])->name('quick-create');
            Route::post('/bulk-delete', [App\Http\Controllers\Admin\TimeSlotController::class, 'bulkDelete'])->name('bulk-delete');
            Route::get('/{timeSlot}', [App\Http\Controllers\Admin\TimeSlotController::class, 'show'])->name('show');
            Route::get('/{timeSlot}/edit', [App\Http\Controllers\Admin\TimeSlotController::class, 'edit'])->name('edit');
            Route::put('/{timeSlot}', [App\Http\Controllers\Admin\TimeSlotController::class, 'update'])->name('update');
            Route::delete('/{timeSlot}', [App\Http\Controllers\Admin\TimeSlotController::class, 'destroy'])->name('destroy');
            Route::post('/{timeSlot}/block', [App\Http\Controllers\Admin\TimeSlotController::class, 'block'])->name('block');
            Route::post('/{timeSlot}/unblock', [App\Http\Controllers\Admin\TimeSlotController::class, 'unblock'])->name('unblock');
            Route::post('/{timeSlot}/unlock', [App\Http\Controllers\Admin\TimeSlotController::class, 'unlock'])->name('unlock');
        });
        
        // Clients
        Route::prefix('clients')->name('clients.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\ClientController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Admin\ClientController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Admin\ClientController::class, 'store'])->name('store');
            Route::get('/{client}', [App\Http\Controllers\Admin\ClientController::class, 'show'])->name('show');
            Route::get('/{client}/edit', [App\Http\Controllers\Admin\ClientController::class, 'edit'])->name('edit');
            Route::put('/{client}', [App\Http\Controllers\Admin\ClientController::class, 'update'])->name('update');
            Route::delete('/{client}', [App\Http\Controllers\Admin\ClientController::class, 'destroy'])->name('destroy');
            Route::get('/{client}/appointments', [App\Http\Controllers\Admin\ClientController::class, 'appointments'])->name('appointments');
        });
        
        // Payments
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::post('/appointments/{appointment}/process', [App\Http\Controllers\Admin\PaymentController::class, 'processPayment'])->name('process');
            Route::post('/appointments/{appointment}/mark-paid', [App\Http\Controllers\Admin\PaymentController::class, 'markAsPaid'])->name('mark-paid');
            Route::post('/appointments/{appointment}/refund', [App\Http\Controllers\Admin\PaymentController::class, 'refund'])->name('refund');
        });
        
        // Notifications
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('index');
            Route::get('/dropdown', [App\Http\Controllers\Admin\NotificationController::class, 'dropdown'])->name('dropdown');
            Route::get('/unread-count', [App\Http\Controllers\Admin\NotificationController::class, 'unreadCount'])->name('unread-count');
            Route::post('/{notification}/mark-read', [App\Http\Controllers\Admin\NotificationController::class, 'markAsRead'])->name('mark-read');
            Route::post('/mark-all-read', [App\Http\Controllers\Admin\NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
            Route::delete('/{notification}', [App\Http\Controllers\Admin\NotificationController::class, 'destroy'])->name('destroy');
        });

    });

    // Client Routes
    Route::middleware(['role:user'])->prefix('client')->name('client.')->group(function () {
        
        // Dashboard
        Route::get('/dashboard', [App\Http\Controllers\Client\DashboardController::class, 'index'])->name('dashboard');
        
        // Appointments
        Route::prefix('appointments')->name('appointments.')->group(function () {
            Route::get('/', [App\Http\Controllers\Client\AppointmentController::class, 'index'])->name('index');
            Route::get('/calendar', [App\Http\Controllers\Client\AppointmentController::class, 'calendar'])->name('calendar');
            Route::get('/calendar/events', [\App\Http\Controllers\Admin\TimeSlotController::class, 'calendarEvents'])->name('calendar.events');
            Route::get('/book', [App\Http\Controllers\Client\AppointmentController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Client\AppointmentController::class, 'store'])->name('store');
            Route::get('/available-slots', [App\Http\Controllers\Client\AppointmentController::class, 'getAvailableSlots'])->name('available-slots');
            Route::get('/{appointment}', [App\Http\Controllers\Client\AppointmentController::class, 'show'])->name('show');
            Route::get('/{appointment}/payment', [App\Http\Controllers\Client\AppointmentController::class, 'payment'])->name('payment');
            Route::post('/{appointment}/payment', [App\Http\Controllers\Client\AppointmentController::class, 'processPayment'])->name('payment.process');
            Route::get('/{appointment}/payment/callback', [App\Http\Controllers\Client\AppointmentController::class, 'paymentCallback'])->name('payment.callback');
        });
        
        // Notifications
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [App\Http\Controllers\Client\NotificationController::class, 'index'])->name('index');
            Route::get('/dropdown', [App\Http\Controllers\Client\NotificationController::class, 'dropdown'])->name('dropdown');
            Route::post('/{notification}/mark-read', [App\Http\Controllers\Client\NotificationController::class, 'markAsRead'])->name('mark-read');
            Route::post('/mark-all-read', [App\Http\Controllers\Client\NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        });
        
        // Profile
        Route::get('/profile', [App\Http\Controllers\Client\ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [App\Http\Controllers\Client\ProfileController::class, 'update'])->name('profile.update');
    });
    
});