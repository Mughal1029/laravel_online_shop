<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\AdminLoginController;
use App\Http\Controllers\admin\HomeController;
use App\Http\Controllers\admin\CategoryController;
use App\Http\Controllers\admin\SubCategoryController;
use App\Http\Controllers\admin\BrandController;
use App\Http\Controllers\admin\ProductController;
use App\Http\Controllers\admin\ProductSubCategoryController;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\admin\TempImagesController;
use Illuminate\Http\Request;


// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [FrontController::class, 'index'])->name('front.home');


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

     // Category Routes
     Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
     Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
     Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
     Route::post('/upload-temp-image', [TempImagesController::class, 'create'])->name('temp-images.create');
     Route::get('/categories/{id}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
     Route::put('/categories/{id}', [CategoryController::class, 'update'])->name('categories.update');
     Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.delete');

     Route::get('/getSlug', function(Request $request){
      $slug = '';
      if(!empty($request->title)){
        $slug = Str::slug($request->title);
      }
      return response()->json([
        'status' => true,
        'slug' => $slug,
      ]);
     })->name('getSlug');
   });

   // Sub Category Routes
   Route::get('/sub_categories', [SubCategoryController::class, 'index'])->name('sub-categories.index');
   Route::get('/sub_categories/create', [SubCategoryController::class, 'create'])->name('sub-categories.create');
   Route::post('/sub_category/store', [SubCategoryController::class, 'store'])->name('sub-category.store');
   Route::get('/sub_category/{id}/edit', [SubCategoryController::class, 'edit'])->name('sub-category.edit');
   Route::put('/sub_category/{id}', [SubCategoryController::class, 'update'])->name('sub-category.update');
   Route::delete('/sub_category/{id}/delete', [SubCategoryController::class, 'destroy'])->name('sub-category.delete');

   // Brands Routes
   Route::get('/brand', [BrandController::class, 'index'])->name('brand.index');
   Route::get('/brand/create', [BrandController::class, 'create'])->name('brand.create');
   Route::post('/brand/store', [BrandController::class, 'store'])->name('brand.store');
   Route::get('/brand/edit/{id}', [BrandController::class, 'edit'])->name('brand.edit');
   Route::put('/brand/update/{id}', [BrandController::class, 'update'])->name('brand.update');
   Route::delete('/brand/delete', [BrandController::class, 'destroy'])->name('brand.delete');

   // Product Routes
   Route::get('/product', [ProductController::class, 'index'])->name('product.index');
   Route::get('/product-subcategories', [ProductSubCategoryController::class, 'index'])->name('product-subcategories.index');
   Route::get('/product/create', [ProductController::class, 'create'])->name('product.create');
   Route::post('/product/store', [ProductController::class, 'store'])->name('product.store');

});