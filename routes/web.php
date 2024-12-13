<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminLoginController;
use App\Http\Controllers\CMSController;
Route::get('/admin', function () {
    return redirect('/admin/login');
});
Route::get('/admin/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminLoginController::class, 'login']);
Route::get('/admin/dashboard', [AdminController::class, 'index'])->middleware('auth:admin');
Route::get('/admin/users', [AdminController::class, 'users'])->middleware('auth:admin');
Route::get('/admin/countries', [AdminController::class, 'countries'])->middleware('auth:admin');
// Route::get('/admin/cms', [AdminController::class, 'cms'])->middleware('auth:admin');
// Route::get('/admin/edit_cms', [AdminController::class, 'edit_cms'])->middleware('auth:admin');
Route::get('/admin/hobbies', [AdminController::class, 'hobbies'])->middleware('auth:admin');
Route::get('/admin/influencer_categories', [AdminController::class, 'influencer_categories'])->middleware('auth:admin');
Route::get('/admin/locations', [AdminController::class, 'locations'])->middleware('auth:admin');
Route::get('/admin/posts', [AdminController::class, 'posts'])->middleware('auth:admin');
Route::get('/admin/events', [AdminController::class, 'events'])->middleware('auth:admin');
Route::resource('/admin/cms', CMSController::class)->middleware('auth:admin');
Route::get('/admin/export-all-users', [AdminController::class, 'exportAllUsers'])->middleware('auth:admin');

// Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
//     Route::resource('cms', CMSController::class);
// });

Route::get('/admin/logout', function () {
    Auth::guard('admin')->logout();
    return redirect('/admin');
});


require __DIR__.'/auth.php';

// Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
