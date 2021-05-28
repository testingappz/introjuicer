<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/users', 'HomeController@users');
Route::get('/update_user_status', 'HomeController@updateUserStatus');
Route::get('/get_user_info/{id}', 'HomeController@getUserInfo');
Route::get('/logoutweb', 'HomeController@logoutWeb');

Route::get('/reconnect_qs', 'HomeController@reconnect_qs');
Route::any('/add_reconnect_qs', 'HomeController@add_reconnect_qs');
Route::any('/adding_qs', 'HomeController@adding_qs');

Route::get('/starter_qs', 'HomeController@starter_qs');
Route::any('/add_starter_qs', 'HomeController@add_starter_qs');
// Show all categories
Route::any('/categories', 'HomeController@categories');
// Add category
Route::any('/add_category', 'HomeController@add_category');
// Add sub category
Route::any('/add_subcategory/{id}', 'HomeController@add_subcategory');
//Show sub category according to category
Route::any('/sub_categories/{id}', 'HomeController@sub_categories');
// Update categories
Route::get('/update_cat/{id}', 'HomeController@update_cat');
// Update sub categories
Route::get('/update_sub_cat/{id}', 'HomeController@update_subcat');
//Delete sub categories
Route::get('/delete_sub_cat/{id}', 'HomeController@delete_sub_cat');
Route::get('/reset_password/{token}', 'Auth\ForgotPasswordController@reset_password');
Route::post('/update_password', 'Auth\ForgotPasswordController@update_password')->name('update_password');


// Route to show groups and delete group
Route::get('/groups/{group_id?}', 'HomeController@groups');

// Route to show chat groups
Route::get('/chat-groups', 'HomeController@chat_groups');

// Route to delete user here
Route::get('/delete_user/{user_id?}', 'HomeController@delete_user');

// Privacy policy
Route::get('/privacy-policy', 'HomeController@privacy_policy');

// Terms of use
Route::get('/terms-of-use', 'HomeController@tou');



// Frontend URL for privacy policy and terms of use
Route::get('/about/privacy', 'FrontEndController@privacy');
Route::get('/about/tou', 'FrontEndController@tou');
