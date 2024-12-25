<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CartController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WishlistController;
use App\Http\Middleware\AuthAdmin;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/




Auth::routes();

Route::get('/',[HomeController::class,'index'])->name('home.index');
Route::get('shop',[ShopController::class,'index'])->name('shop.index');
Route::get('shop/details/{slug}',[ShopController::class,'details_shop'])->name('shop.details');
Route::get('cart',[CartController::class,'index'])->name('cart.index');
Route::post('cart/add',[CartController::class,'cart_add'])->name('cart.add');
Route::put('cart/increase-quantity/{rowId}', [CartController::class, 'cart_increase'])->name('cart.qty.increase');
Route::put('cart/decrease-quantity/{rowId}', [CartController::class, 'cart_decrease'])->name('cart.qty.decrease');
Route::delete('cart/remove/{rowId}',[CartController::class,'cart_remove'])->name('cart.remove');
Route::delete('/cart/clear-cart',[CartController::class,'cart_delete'])->name('cart.destory');
Route::post(uri: "cart/wishlist",action: [WishlistController::class,"add_to_wishlist"])->name(name: "wishlist.add");
Route::get('wishlist',[WishlistController::class,'index'])->name('wishlist.index');
Route::delete('wishlist/remove/{rowId}',[WishlistController::class,'remove_from_wishlist'])->name('wishlist.remove');
Route::delete('wishlist/clear',[WishlistController::class,'clear_wishlist'])->name('wishlist.clear');
Route::post('wishlist/move-to-cart/{rowId}',[WishlistController::class,'move_to_cart'])->name('move.to.cart');
Route::post('coupon/apply',[CartController::class,'coupon_apply'])->name('coupon.apply');
Route::delete('coupon/remove',[CartController::class,'coupon_remove'])->name('coupon.remove');
Route::get('checkout',[CartController::class,'checkout'])->name('checkout.index');
Route::post('/place-an-order',[CartController::class,'place_an_order'])->name('cart.place.an.order');
Route::get('/order-confirmation',[CartController::class,'order_confirmation'])->name('order.confirmation');
Route::get('/contact',[HomeController::class,'contact'])->name('contact');
Route::post('/contact/store',[HomeController::class,'contact_store'])->name('contact.store');
Route::get('/search',[ShopController::class,'search'])->name('search');
Route::middleware(['auth'])->group(function () {
    Route::get('/account-dashboard',[UserController::class,'index'])->name('user.index');
    Route::get('/account-order',[UserController::class,'order'])->name('user.order');
    Route::get('/account-order-detail/{id}',[UserController::class,'order_detail'])->name('user.order.detail');
    Route::put('/account-order-canceld',[UserController::class,'cancel_order'])->name('usr.order.cancel');
});

Route::middleware(['auth',AuthAdmin::class])->group(function () {
Route::get('/admin',[AdminController::class,'index'])->name('admin.index');
Route::get('/admin/brand',[AdminController::class,'brands'])->name('admin.brand');
Route::get('/admin/brand/add',[AdminController::class,'brand_add'])->name('brand_add');
Route::post('/admin/brand/store',[AdminController::class,'brandstore'])->name('brandstore');
Route::get('/admin/brand/edit/{id}',[AdminController::class,'brand_edit'])->name('brand.edit');
Route::post('/admin/brand/update/{id}',[AdminController::class,'brandupdate'])->name('brandupdate');
Route::delete('/admin/brand/delete/{id}',[AdminController::class,'brand_delete'])->name('brand.delete');
Route::get('admin/categor',[AdminController::class,'category'])->name('admin.category');
Route::get('admin/category/add',[AdminController::class,'category_add'])->name('category_add');
Route::post('admin/category/store',[AdminController::class,'category_store'])->name('category_store');
Route::delete('/admin/category/delete/{id}',[AdminController::class,'category_delete'])->name('category.delete');
Route::get('admin/category/edit/{id}',[AdminController::class,'category_edit'])->name('category.edit');
Route::post('admin/category/update/{id}',[AdminController::class,'category_update'])->name('category.update');
Route::get('admin/product',[AdminController::class,'product'])->name('admin.products');
Route::get('admin/product/add',[AdminController::class,'product_add'])->name('product_add');
Route::post('admin/product/store',[AdminController::class,'product_store'])->name('product_store');
Route::get('admin/product/edit/{id}',[AdminController::class,'product_edit'])->name('product.edit');
Route::post('admin/product/update/{id}',[AdminController::class,'product_update'])->name('admin.product.update');
Route::delete('admin/product/delete/{id}',[AdminController::class,'product_delete'])->name('product.delete');
Route::get('admin/coupon',[AdminController::class,'coupon'])->name('coupon.index');
Route::get('admin/coupon-add',[AdminController::class,'coupon_add'])->name('coupon.add');
Route::post('admin/coupon/add',[AdminController::class,'coupon_store'])->name('coupon.store');
Route::get('coupon/edit/{id}',[AdminController::class,'coupon_edit'])->name('coupon.edit');
Route::post('coupon/update/{id}',[AdminController::class,'coupon_update'])->name('coupon.update');
Route::delete('coupon/delete/{id}',[AdminController::class,'coupon_delete'])->name('coupon.delete');
Route::get('admin/orders',[AdminController::class,'order'])->name('admin.orders');
Route::get('admin/order/details/{id}',[AdminController::class,'order_details'])->name('order.details');
Route::put('/admin/order/status',[AdminController::class,'order_status'])->name('order.status');

Route::get('admin/slider',[AdminController::class,'slide'])->name('admin.slider');
Route::get("admin/slider/add",[AdminController::class,"slide_add"])->name("slide.add");
Route::post("admin/slider/store",[AdminController::class,"slide_store"])->name("slide.store");
Route::get('admin/slider/edit/{id}',[AdminController::class,'slide_edit'])->name('slide.edit');
Route::put('admin/slider/update/{id}',[AdminController::class,'slide_update'])->name('slide.update');
Route::delete('admin/slider/delete/{id}',[AdminController::class,'destroy'])->name('slide.delete');
Route::get('admin/contact',[AdminController::class,'contact'])->name('admin.contact');
Route::delete('admin/contact/delete/{id}',[AdminController::class,'contact_delete'])->name('contact.delete');

});
