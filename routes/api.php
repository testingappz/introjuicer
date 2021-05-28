<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(['namespace' => 'Auth'], function(){
	Route::post('login',     		'LoginController@login');
	Route::post('logout',     		'LoginController@logout');
	Route::post('register', 		'RegisterController@register');
	Route::post('forgot_password', 	'ForgotPasswordController@forgotPassword');
	// API to add user details(that are added in edit profile API) within sign up process.
	Route::post('sign_up_process','RegisterController@sign_up_process');
	//Api to delete user profile
	Route::post('delete_profile','RegisterController@delete_profile');
});
// List all sub categories with category name
// param-> (cat_name)
Route::any('chat_categories','ChatCOntroller2@chat_categories');

//Listing all sub categories with sort by popularity option.
// param-> (cat_name)
Route::any('chat_categories_with_popularity','ChatCOntroller2@chat_categories_with_popularity');

// Create group API
// param-> (Multiple params)
Route::any('create_group', 'ChatCOntroller2@create_group');

//Listing all group with Tag names parameter
// param -> (tag_name)
Route::any('list_chat_group','ChatCOntroller2@list_chat_group');

// API to send friend requests to other user
// param -> (sender_user_id,receiver_user_id)
Route::any('connect_users','ChatCOntroller2@connect_users');

// API to send friend requests to join a group
// param -> (sender_user_id,receiver_group_id)
Route::any('connect_groups','ChatCOntroller2@connect_groups');

// API to list user according to intrests and looking for.
// param -> (interest,looking_for,user_id)
Route::any('list_user_according_cat','ChatCOntroller2@list_user_according_category');

// API to view all the group detials like images and tags
// param -> (group_id)
Route::any('list_groups_details','ChatCOntroller2@list_groups_details');

// Listing Groups according to user create/enter in it
// param -> (user_id)
Route::any('list_user_group','ChatCOntroller2@list_user_groups');

// Edit User profile
// param -> (multiple param)
Route::any('edit_user_profile','ChatCOntroller2@edit_user_profile');

// View User Profile
// param -> (user_id)
Route::post('user_profile','Api\UserController@getUserProfile');

// List user pending requests, rose messages and connected users
// param -> (user_id)
Route::post('one_to_one_chat','ChatCOntroller2@one_to_one_chat');

// Show group listing in which user is joined
// param -> (user_id)
Route::post('group_listing_joined_users','ChatCOntroller2@group_listing_joined_users');

//Show user group listing in which user is joined
// param -> (user_id)
Route::post('group_listing','ChatCOntroller2@group_listing');

// Show chat history between two connected users
// param -> (sender_id,receiver_id)
Route::post('one_to_one_chat_history','ChatCOntroller2@one_to_one_chat_history');

// Show chat history between groups
// param -> (group_id)
Route::post('group_chat_history','ChatCOntroller2@group_chat_history');

// Join interest(category) with user id
// param -> (user_id,interest)
Route::post('join_interest','ConnectController@join_interest');

// View received friend requests from user and groups
// param -> (user_id)
Route::post('friend_requests','ConnectController@friend_requests');

// Acccept or reject Friend requests
// param -> (user_id,receive_user_id,type)
Route::post('manage_requests','ConnectController@manage_requests');

// Block user API
// param -> (block_userid,blocked_userid)
Route::post('block_user','ConnectController@block_user');

// Show connected users listing
// param -> (user_id,group_id)
Route::post('list_connected_user','ConnectController@list_connected_user');

// Send invites to join groups to the connected users.
// param -> (user_id,group_id)
Route::post('send_invite','ConnectController@send_invite');

// View Send friends request to user and user group
// param -> (user_id,receiver_id,group)
Route::post('view_sent_friend_requests','ConnectController@view_sent_friend_requests');

// View received friend request to the group owner only
// param -> (user_id,group_id)
Route::post('group_friend_requests','ConnectController@group_friend_requests');

// Return only created group  id's by the user
Route::post('group_ids','ConnectController@group_ids');

// Accept or reject group requests
Route::post('manage_group_requests','ConnectController@manage_group_requests');

//Get current location of logged in user
Route::post('get_location','ConnectController@get_location');

//Connect filters with multiple parameters
Route::post('connect_filters','ConnectController@connect_filters');

//List user prefrences
Route::post('view_preferences','ConnectController@view_preferences');

//Get child sub categories
Route::post('get_sub_cats','MoneySectionController@get_sub_cats');

//Create job
Route::post('create_job','MoneySectionController@create_job');

//Create sell
Route::post('create_sell','MoneySectionController@create_sell');

//Create sub-category
Route::post('create_sub_cat_child','MoneySectionController@create_sub_cat_child');

//Update read status of rose messages
Route::post('update_read_status','MoneySectionController@update_read_status');

//View jobs listing
Route::post('jobs_listing','MoneySectionController@jobs_listing');

//View job listing with details
Route::post('job_listing_details','MoneySectionController@job_listing_details');

//View sell listing
Route::post('sells_listing','MoneySectionController@sells_listing');

//View sell listing with details
Route::post('sell_listing_details','MoneySectionController@sell_listing_details');

//Create and update professional profile
Route::post('create_professional_profile','MoneySectionController@create_professional_profile');

//View professional profile
Route::post('view_professional_profile','MoneySectionController@view_professional_profile');

// Change password API.
Route::post('change_password','Changepasswordcontroller');

//Listing user feeds
Route::post('user_feeds','UserFeedsController@user_feeds');

//Add user feeds
Route::post('add_user_feeds','UserFeedsController@add_user_feeds');

//Update visibilty of user
Route::post('user_visibility','SettingsController@user_visibility');

//Add or update settings to show user activities
Route::post('user_activities','SettingsController@user_activities');

//View settings to show user activities
Route::post('view_user_activities','SettingsController@view_user_activities');

//Give rating to other users
Route::post('add_rating','UserRatingController@add_rating');

//View user rating API
Route::post('view_rating','UserRatingController@view_rating');

//Filter for money  section
Route::post('add_user_prefrences_money','ConnectController@user_prefrences_money');

//Leave sub category or sub category
Route::post('leave_category','SettingsController@leave_category');

//Show money section chat list of all users , with cuurent user is chatted
Route::post('money_section_chat_list','MoneySectionController@money_section_chat_list');

//Show money section chat history betwen two users
Route::post('money_section_chat_history','MoneySectionController@money_section_chat_history');

// Get starter questions with shuffling
Route::post('starter_questions','QuestionsController@starter_questions');

// Get reconnect questions with shuffling
Route::post('reconnect_questions','QuestionsController@reconnect_questions');

// Add secret crush API
Route::post('add_secret_crush','QuestionsController@add_secret_crush');

// View secret crush results
Route::post('view_secret_crush','QuestionsController@view_secret_crush');

//Api to add likes on the user feeds
Route::post('like_feeds','UserFeedsController@like_feeds');

//Api to add user favorite groups , jobs and sells.
Route::post('add_favorite','UserFeedsController@add_favorite');

//Api to check weather user activate conncet before functionality or not
Route::post('check_connect_before','SettingsController@check_connect_before');

//Api to join monesy section joinable categories
// Route::post('join_money_catgeory','SettingsController@join_money_catgeory');

//Unblock API
Route::post('unblock_user','ConnectController@unblock_user');

//API to check weather given user is blocked , added secret crush or connected or not
Route::post('user_connections','SettingsController@user_connections');

// Route::post('/forgot_password','Api\UserController@forgotPassword');
// Route::post('/reset_password','Api\UserController@resetPassword');

// API to exit any group.
// param-> (user_id,group_id)
Route::post('leave_group','ConnectController@leave_group');

// API to list favourite groups
Route::post('list_favourite_group','SettingsController@list_favourite_group');

// API to show favourite jobs and sells in money section
Route::post('favourite_money_section','SettingsController@favourite_money_section');

// API to report user
Route::post('report_user','SettingsController@report_user');

// Api to view the created chat groups
Route::post('list_created_chat_group','SettingsController@list_created_chat_group');

// Api to add user prefrences for chat section.
Route::post('add_user_prefrences_chat','SettingsController@add_user_prefrences_chat');

// Api to add user prefrences for group section.
Route::post('add_user_prefrences_group','SettingsController@add_user_prefrences_group');

// Api to view prefrences all the group,chat and money sections.
Route::post('view_all_prefrences','SettingsController@view_all_prefrences');

// Api to delete group with tags and images
Route::post('delete_group','UserRatingController@delete_group');

// Api to delete jobs
Route::post('delete_jobs','UserRatingController@delete_jobs');

// Api to delete sells
Route::post('delete_sells','UserRatingController@delete_sells');

// API to Delete images from users/groups etc.
Route::post('delete_image','QuestionsController@delete_image'); 

// API to remove people from groups.
Route::post('remove_user','QuestionsController@remove_user');

// API to add reconnect me and introduce me functionality
Route::post('reconnect_introduce','NotificationController@reconnect_introduce');

// API to join default group
Route::post('join_default_group','UserRatingController@join_default_group');
Route::post('test','ChatCOntroller2@test');


// API to view users professional profile in current category
Route::post('list_profiles','MoneySectionController@list_profiles');

Route::post('cron_for_notify_users','NotificationController@cron_for_notify_users');
