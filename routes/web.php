<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\AdminLoginController;
use App\Http\Controllers\admin\HomeController;
use App\Http\Controllers\admin\CategoryController;


Route::get('/', function () {
    return view('welcome');
});



Route::group(['prefix' => 'admin'], function(){

//    Route::group(['middleware' => 'admin.guest'],function(){
//     Route::get('/login', [AdminLoginController::class, 'index']);
//    });
   
   Route::middleware('admin.guest')->group(function(){
     Route::get('/login', [AdminLoginController::class, 'index'])->name('admin.login');
     Route::post('/authenticate', [AdminLoginController::class, 'authenticate'])->name('admin.authenticate');
   });

   Route::group(['middleware' => 'admin.auth'], function(){
     Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('admin.dashboard');
     Route::get('/logout', [HomeController::class, 'logout'])->name('admin.logout');

     Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');

   });


});