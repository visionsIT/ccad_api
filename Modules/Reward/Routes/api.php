<?php

Route::resource('catalogs', 'ProductCatalogController');
Route::resource('product_categories', 'ProductCategoryController');

Route::get('products/search', 'ProductController@search');
Route::get('products/advancesearch', 'ProductController@searchAdvance');
Route::get('products/getbrands', 'ProductController@getBrandsByCategory');
Route::resource('products', 'ProductController');


Route::get('orders/pending', 'ProductOrderController@getPendingOrders');
Route::get('orders/confirmed', 'ProductOrderController@getConfirmedOrders');
Route::get('orders/cancelled', 'ProductOrderController@getCancelledOrders');
Route::get('orders/shipped', 'ProductOrderController@getShippedOrders');
Route::post('orders/filter', 'ProductOrderController@filterByDates');
Route::get('orders/search', 'ProductOrderController@search');
Route::resource('orders', 'ProductOrderController');

Route::resource('sub-products', 'SubProductController');
Route::resource('user-catalogs', 'UserProductCatalogController');
Route::resource('user-category', 'UserProductCategoryController');
Route::resource('user-products', 'UserProductController');
Route::get('myorders/{account_id}', 'UserProductOrderController@myorders')->name('myorders');
Route::get('cat_prod/{catalog_id}', 'ProductCategoryController@sub_prod')->name('sub_prod');
//Route::get('cat_prod/{category_id}', 'ProductCategoryController@sub_prod')->name('sub_prod');
Route::resource('my-orders', 'UserProductOrderController');

Route::post('products/import/{program_id}', 'ImportsController@import');

Route::post('orders/{id}/confirm', 'ProductOrderController@confirmOrder');
Route::post('orders/{id}/ship', 'ProductOrderController@shipOrder');
Route::post('orders/{id}/cancel', 'ProductOrderController@cancelOrder');

Route::post('products/import_products', 'ImportsController@importProductApi');
Route::post('users/import_users', 'ImportsController@importUserApi');
Route::post('orders/import_orders', 'ImportsController@importOrderApi');

Route::get('max-points', 'ProductController@fetchMaxPoint');
Route::post('add_product', 'ProductController@addProduct');
Route::post('add_product/{product_id}', 'ProductController@addProduct');
Route::post('change_product_status', 'ProductController@updateProductStatus');

Route::post('change_category_status', 'ProductCatalogController@updateCatalogStatus');
Route::resource('create_catalog', 'ProductCatalogController');
Route::post('update_catalog/{catalog_id}', 'ProductCatalogController@updateCatalog');
Route::post('change_subcategory_status', 'ProductCategoryController@updateCategoryStatus');
Route::resource('create_subcategory', 'ProductCategoryController');
Route::post('update_subcategory/{subcategory_id}', 'ProductCategoryController@updateSubCategory');
Route::resource('reward_setting', 'RewardController');
Route::get('countries_list', 'RewardController@getCountries');
Route::post('change_country_delivery', 'RewardController@changeDeliveryStatus');
// Route::post('change_ecards_permission', 'RewardController@ecardPermission');
Route::post('delete_testing_products','ProductOrderController@deleteTestOrders');
Route::post('users/assign_user_vp', 'ImportsController@assignUserVpApi');
Route::post('users/email_update_vp', 'ImportsController@sendEmailUpdateVp');
Route::post('users/welcome_email', 'ImportsController@sendWelcomeEmail');
Route::post('users/remove_pre_groups','ImportsController@removeExistingGroups');

// Order Export 
Route::get('orders_export', 'ProductOrderController@OrdersExport');

// Order Detail Export
Route::get('orders_detail_export', 'ProductOrderController@OrdersDetailExport');
Route::get('get_no_imgs_product', 'ImportsController@getProductImage');

Route::get('denominationCountries', 'ProductOrderController@denominationCountries');
Route::get('denominationMultipleCountries', 'ProductOrderController@denominationMultipleCountries');

Route::get('mappProductOrderData', 'ProductOrderController@mappProductOrderData');
Route::get('mappProductPrice', 'ProductController@mappProductPrice');

// Qty Slots
Route::get('qty_slots_list', 'RewardController@QtySlotsList');
Route::post('add_qty_slots', 'RewardController@AddQtySlot');
Route::get('get_qty_slots', 'RewardController@GetQtySlotByID');
Route::post('update_qty_slots', 'RewardController@UpdateQtySlotByID');
Route::post('delete_qty_slots', 'RewardController@DeleteQtySlotByID');

// Delivery Charges
Route::get('delivery_charges_list', 'RewardController@DeliveryChargesList');
Route::post('add_delivery_charges', 'RewardController@AddDeliveryCharges');
//Route::get('get_delivery_charges', 'RewardController@GetDeliveryChargesByID');
Route::post('update_delivery_charges', 'RewardController@UpdateDeliveryChargesByID');
//Route::post('delete_delivery_charges', 'RewardController@DeleteDeliveryChargesByID');

Route::post('remove_campaign_budget', 'ImportsController@removeBudgets');