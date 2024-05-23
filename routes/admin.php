<?php

/*
  |--------------------------------------------------------------------------
  | Admin Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register admin routes for your application. These
  | routes are loaded by the RouteServiceProvider within a group which
  | contains the "web" middleware group. Now create something great!
  |
 */

Route::post('/update', 'UpdateController@step0')->name('update');
Route::get('/update/step1', 'UpdateController@step1')->name('update.step1');
Route::get('/update/step2', 'UpdateController@step2')->name('update.step2');

Route::get('/clear-cache', 'AdminController@clearCache')->name('cache.clear');

Route::get('/admin', 'AdminController@admin_dashboard')->name('admin.dashboard')->middleware(['auth', 'admin']);
Route::get('/menu_update', 'AdminController@menu_update')->name('admin.menu_update')->middleware(['auth', 'admin']);
Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'admin']], function() {
    //Update Routes

    Route::resource('categories', 'CategoryController');
    Route::get('/categories/edit/{id}', 'CategoryController@edit')->name('categories.edit');
    Route::get('/categories/destroy/{id}', 'CategoryController@destroy')->name('categories.destroy');
    Route::post('/categories/featured', 'CategoryController@updateFeatured')->name('categories.featured');
    Route::get('/categories.bannerlinkdata', 'CategoryController@banner_linkdata')->name('categories.bannerlinkdata');
    Route::resource('brands', 'BrandController');
    Route::get('/brands/edit/{id}', 'BrandController@edit')->name('brands.edit');
    Route::get('/brands/destroy/{id}', 'BrandController@destroy')->name('brands.destroy');

    Route::get('/products/admin', 'ProductController@admin_products')->name('products.admin');
    Route::get('/products/seller', 'ProductController@seller_products')->name('products.seller');
    Route::get('/products/all', 'ProductController@all_products')->name('products.all');
    Route::get('/products/create', 'ProductController@create')->name('products.create');
    Route::get('/products/admin/{id}/edit', 'ProductController@admin_product_edit')->name('products.admin.edit');
    Route::get('/products/seller/{id}/edit', 'ProductController@seller_product_edit')->name('products.seller.edit');
    Route::post('/products/todays_deal', 'ProductController@updateTodaysDeal')->name('products.todays_deal');
    Route::post('/products/featured', 'ProductController@updateFeatured')->name('products.featured');
    Route::post('/products/product_for_you', 'ProductController@updateProduct_for_you')->name('products.product_for_you');
    Route::post('/products/approved', 'ProductController@updateProductApproval')->name('products.approved');
    Route::post('/products/get_products_by_subcategory', 'ProductController@get_products_by_subcategory')->name('products.get_products_by_subcategory');
    Route::post('/bulk-product-delete', 'ProductController@bulk_product_delete')->name('bulk-product-delete');

    Route::get('/products/discount_percentage', 'ProductController@discount_percentage')->name('discount_percentage.admin');

    Route::resource('sellers', 'SellerController');
    Route::get('sellers_ban/{id}', 'SellerController@ban')->name('sellers.ban');
    Route::get('/sellers/destroy/{id}', 'SellerController@destroy')->name('sellers.destroy');
    Route::post('/bulk-seller-delete', 'SellerController@bulk_seller_delete')->name('bulk-seller-delete');
    Route::get('/sellers/view/{id}/verification', 'SellerController@show_verification_request')->name('sellers.show_verification_request');
    Route::get('/sellers/approve/{id}', 'SellerController@approve_seller')->name('sellers.approve');
    Route::get('/sellers/reject/{id}', 'SellerController@reject_seller')->name('sellers.reject');
    Route::get('/sellers/login/{id}', 'SellerController@login')->name('sellers.login');
    Route::post('/sellers/payment_modal', 'SellerController@payment_modal')->name('sellers.payment_modal');
    Route::get('/seller/payments', 'PaymentController@payment_histories')->name('sellers.payment_histories');
    Route::get('/seller/payments/show/{id}', 'PaymentController@show')->name('sellers.payment_history');

    Route::resource('customers', 'CustomerController');
	Route::post('customers_save/', 'CustomerController@customerSave')->name('customers.save'); //added by alauddin
    Route::get('customers_ban/{customer}', 'CustomerController@ban')->name('customers.ban');
    Route::get('/customers/login/{id}', 'CustomerController@login')->name('customers.login');
    Route::get('/customers/destroy/{id}', 'CustomerController@destroy')->name('customers.destroy');
    Route::post('/bulk-customer-delete', 'CustomerController@bulk_customer_delete')->name('bulk-customer-delete');

    Route::get('/newsletter', 'NewsletterController@index')->name('newsletters.index');
    Route::post('/newsletter/send', 'NewsletterController@send')->name('newsletters.send');
    Route::post('/newsletter/test/smtp', 'NewsletterController@testEmail')->name('test.smtp');

    Route::resource('profile', 'ProfileController');

    Route::post('/business-settings/update', 'BusinessSettingsController@update')->name('business_settings.update');
    Route::post('/business-settings/update/activation', 'BusinessSettingsController@updateActivationSettings')->name('business_settings.update.activation');
    Route::get('/general-setting', 'BusinessSettingsController@general_setting')->name('general_setting.index');
    Route::get('/app_slider', 'BusinessSettingsController@app_slider')->name('app_slider.index');
    Route::get('/app_footer', 'BusinessSettingsController@app_footer')->name('app_footer.index');
    Route::get('/push_notification', 'BusinessSettingsController@create_push_notification')->name('push_notification.create');
    Route::post('/custom_push_store', 'BusinessSettingsController@custom_push_store')->name('custom_push_store');
    
    Route::get('/app_footer_update', 'BusinessSettingsController@app_footer_update')->name('app_footer_update');
    Route::get('/activation', 'BusinessSettingsController@activation')->name('activation.index');
    Route::get('/payment-method', 'BusinessSettingsController@payment_method')->name('payment_method.index');
    Route::get('/file_system', 'BusinessSettingsController@file_system')->name('file_system.index');
    Route::get('/social-login', 'BusinessSettingsController@social_login')->name('social_login.index');
    Route::get('/smtp-settings', 'BusinessSettingsController@smtp_settings')->name('smtp_settings.index');
    Route::get('/google-analytics', 'BusinessSettingsController@google_analytics')->name('google_analytics.index');
    Route::get('/google-recaptcha', 'BusinessSettingsController@google_recaptcha')->name('google_recaptcha.index');
    Route::get('/google-map', 'BusinessSettingsController@google_map')->name('google-map.index');
    Route::get('/google-firebase', 'BusinessSettingsController@google_firebase')->name('google-firebase.index');

    //Facebook Settings
    Route::get('/facebook-chat', 'BusinessSettingsController@facebook_chat')->name('facebook_chat.index');
    Route::post('/facebook_chat', 'BusinessSettingsController@facebook_chat_update')->name('facebook_chat.update');
    Route::get('/facebook-comment', 'BusinessSettingsController@facebook_comment')->name('facebook-comment');
    Route::post('/facebook-comment', 'BusinessSettingsController@facebook_comment_update')->name('facebook-comment.update');
    Route::post('/facebook_pixel', 'BusinessSettingsController@facebook_pixel_update')->name('facebook_pixel.update');

    Route::post('/env_key_update', 'BusinessSettingsController@env_key_update')->name('env_key_update.update');
    Route::post('/payment_method_update', 'BusinessSettingsController@payment_method_update')->name('payment_method.update');
    Route::post('/google_analytics', 'BusinessSettingsController@google_analytics_update')->name('google_analytics.update');
    Route::post('/google_recaptcha', 'BusinessSettingsController@google_recaptcha_update')->name('google_recaptcha.update');
    Route::post('/google-map', 'BusinessSettingsController@google_map_update')->name('google-map.update');
    Route::post('/google-firebase', 'BusinessSettingsController@google_firebase_update')->name('google-firebase.update');
    //Currency
    Route::get('/currency', 'CurrencyController@currency')->name('currency.index');
    Route::post('/currency/update', 'CurrencyController@updateCurrency')->name('currency.update');
    Route::post('/your-currency/update', 'CurrencyController@updateYourCurrency')->name('your_currency.update');
    Route::get('/currency/create', 'CurrencyController@create')->name('currency.create');
    Route::post('/currency/store', 'CurrencyController@store')->name('currency.store');
    Route::post('/currency/currency_edit', 'CurrencyController@edit')->name('currency.edit');
    Route::post('/currency/update_status', 'CurrencyController@update_status')->name('currency.update_status');

    //Tax
    Route::resource('tax', 'TaxController');
    Route::get('/tax/edit/{id}', 'TaxController@edit')->name('tax.edit');
    Route::get('/tax/destroy/{id}', 'TaxController@destroy')->name('tax.destroy');
    Route::post('tax-status', 'TaxController@change_tax_status')->name('taxes.tax-status');


    Route::get('/verification/form', 'BusinessSettingsController@seller_verification_form')->name('seller_verification_form.index');
    Route::post('/verification/form', 'BusinessSettingsController@seller_verification_form_update')->name('seller_verification_form.update');
    Route::get('/vendor_commission', 'BusinessSettingsController@vendor_commission')->name('business_settings.vendor_commission');
    Route::post('/vendor_commission_update', 'BusinessSettingsController@vendor_commission_update')->name('business_settings.vendor_commission.update');

    Route::resource('/languages', 'LanguageController');
    Route::post('/languages/{id}/update', 'LanguageController@update')->name('languages.update');
    Route::get('/languages/destroy/{id}', 'LanguageController@destroy')->name('languages.destroy');
    Route::post('/languages/update_rtl_status', 'LanguageController@update_rtl_status')->name('languages.update_rtl_status');
    Route::post('/languages/key_value_store', 'LanguageController@key_value_store')->name('languages.key_value_store');

    // website setting
    Route::group(['prefix' => 'website'], function() {
        Route::get('/footer', 'WebsiteController@footer')->name('website.footer');
        Route::get('/header', 'WebsiteController@header')->name('website.header');
        Route::get('/appearance', 'WebsiteController@appearance')->name('website.appearance');
        Route::get('/pages', 'WebsiteController@pages')->name('website.pages');
        Route::resource('custom-pages', 'PageController');
        Route::get('/custom-pages/edit/{id}', 'PageController@edit')->name('custom-pages.edit');
        Route::get('/custom-pages/destroy/{id}', 'PageController@destroy')->name('custom-pages.destroy');
    });

    Route::resource('roles', 'RoleController');
    Route::get('/roles/edit/{id}', 'RoleController@edit')->name('roles.edit');
    Route::get('/roles/destroy/{id}', 'RoleController@destroy')->name('roles.destroy');

    Route::resource('staffs', 'StaffController');
    Route::get('/staffs/destroy/{id}', 'StaffController@destroy')->name('staffs.destroy');

    Route::resource('flash_deals', 'FlashDealController');
    Route::get('/flash_deals/edit/{id}', 'FlashDealController@edit')->name('flash_deals.edit');
    Route::get('/flash_deals/destroy/{id}', 'FlashDealController@destroy')->name('flash_deals.destroy');
    Route::get('/flash_deals/reports/{id}', 'FlashDealController@reports')->name('flash_deals.reports');
    Route::get('/flash_deals/view_fd_tails/{sid}/{fdid}', 'FlashDealController@view_fd_tails')->name('view_fd_tails');
    Route::post('/flash_deals/update_status', 'FlashDealController@update_status')->name('flash_deals.update_status');
    Route::post('/flash_deals/update_featured', 'FlashDealController@update_featured')->name('flash_deals.update_featured');
    Route::post('/flash_deals/update_fereeshipping', 'FlashDealController@update_freeshipping')->name('flash_deals.update_freeshipping');
    Route::post('/flash_deals/product_discount', 'FlashDealController@product_discount')->name('flash_deals.product_discount');
    Route::post('/flash_deals/product_discount_edit', 'FlashDealController@product_discount_edit')->name('flash_deals.product_discount_edit');

    
    //Subscribers
    Route::get('/subscribers', 'SubscriberController@index')->name('subscribers.index');
    Route::get('/subscribers/destroy/{id}', 'SubscriberController@destroy')->name('subscriber.destroy');

    // Route::get('/orders', 'OrderController@admin_orders')->name('orders.index.admin');
    // Route::get('/orders/{id}/show', 'OrderController@show')->name('orders.show');
    // Route::get('/sales/{id}/show', 'OrderController@sales_show')->name('sales.show');
    // Route::get('/sales', 'OrderController@sales')->name('sales.index');
    // All Orders
    Route::get('/all_orders', 'OrderController@all_orders')->name('all_orders.index');
    Route::get('/all_orders/{id}/show', 'OrderController@all_orders_show')->name('all_orders.show');
	Route::get('/all_combined_orders/{id}/show', 'OrderController@all_combined_orders_show')->name('all_combined_orders.show'); //added by alauddin
    Route::Post('/reason_cancel_order','OrderController@reason_cancel_order')->name('reason_cancel_order');

    // Inhouse Orders
    Route::get('/inhouse-orders', 'OrderController@admin_orders')->name('inhouse_orders.index');
    Route::get('/inhouse-orders/{id}/show', 'OrderController@show')->name('inhouse_orders.show');

    // Seller Orders
    Route::get('/seller_orders', 'OrderController@seller_orders')->name('seller_orders.index');
    Route::get('/seller_orders/{id}/show', 'OrderController@seller_orders_show')->name('seller_orders.show');
    Route::post('/bulk-order-status', 'OrderController@bulk_order_status')->name('bulk-order-status');
	Route::post('/sendToCurier', 'OrderController@sendToCurier')->name('sendToCurier');

     //slugs
     Route::get('/all_slugs', 'ProductController@all_slugs')->name('slugs.all_slugs');
     Route::post('/add_slug', 'ProductController@add_slug')->name('slugs.add_slug');
     Route::get('/edit/{id}','ProductController@edit');
     Route::post('/update_slug/{id}','ProductController@update_slug');

    // Pickup point orders
    Route::get('orders_by_pickup_point', 'OrderController@pickup_point_order_index')->name('pick_up_point.order_index');
    Route::get('/orders_by_pickup_point/{id}/show', 'OrderController@pickup_point_order_sales_show')->name('pick_up_point.order_show');

    Route::get('/orders/destroy/{id}', 'OrderController@destroy')->name('orders.destroy');
    Route::post('/bulk-order-delete', 'OrderController@bulk_order_delete')->name('bulk-order-delete');
    Route::post('/updateStatus', 'OrderController@updateStatus')->name('updateStatus');
    Route::post('/pay_to_seller', 'CommissionController@pay_to_seller')->name('commissions.pay_to_seller');



	//Purchase and Opening Stock
    Route::get('/purchase_orders', 'PurchaseController@purchase_orders')->name('purchase_orders.index'); // added by alauddin
    Route::get('/add_purchase', 'PurchaseController@add_purchase')->name('purchase_orders.add'); //added by alauddin
    Route::post('/store_purchase', 'PurchaseController@store_purchase')->name('purchase_orders.store'); //added by alauddin
    Route::post('/get_puracher_product', 'PurchaseController@get_puracher_product')->name('purchase_orders.get_puracher_product'); //added by alauddin
	Route::post('/get_supplier_product', 'PurchaseController@get_supplier_product')->name('purchase_orders.get_supplier_product'); //added by alauddin
    Route::get('/puracher_edit/{id}', 'PurchaseController@puracher_edit')->name('puracher_edit'); //added by alauddin
    Route::post('/puracher_edit_store', 'PurchaseController@puracher_edit_store')->name('puracher_edit_store'); //added by alauddin
    Route::get('/purchase_orders_view/{id}', 'PurchaseController@purchase_orders_view')->name('purchase_orders_view'); //added by alauddin
    Route::get('/purchase_approve/{id}', 'PurchaseController@purchase_approve')->name('purchase_approve.index'); //added by alauddin
    Route::post('/purchase_update_payment_status', 'PurchaseController@purchase_update_payment_status')->name('orders.purchase_update_payment_status'); //added by alauddin
    Route::get('/purchase_orders/destroy_po/{id}', 'PurchaseController@destroy_po')->name('orders.destroy_po'); //added by alauddin

    Route::resource('purchase_return', 'PurchaseReturnController'); 
    Route::get('/purchase_return_store', 'PurchaseReturnController@purchase_return_store')->name('purchase_return_store');
    Route::get('/purchase_return.edit/{id}', 'PurchaseReturnController@edit')->name('purchase_return_edit');
    Route::post('/purchase_return_update', 'PurchaseReturnController@purchase_return_update')->name('purchase_return_update'); 
    Route::get('/purchase_return{id}', 'PurchaseReturnController@destroy')->name('purchase_return.destroy'); 
    Route::get('/purchase_return.show/{id}', 'PurchaseReturnController@show')->name('purchase_return_show'); 
    Route::get('/return_approve/{id}', 'PurchaseReturnController@return_approve')->name('return_approve'); 
    Route::post('/find_purchase_order_item', 'PurchaseReturnController@find_purchase_order_item')->name('find_purchase_order_item');
    Route::post('/get_puracher_details', 'PurchaseReturnController@get_puracher_details')->name('get_puracher_details');

    Route::resource('supplier', 'SupplierController'); //added by alauddin
    Route::get('/supplier/destroy/{id}', 'SupplierController@destroy')->name('supplier.destroy'); //added by alauddin



    //Reports
    Route::get('/stock_report', 'ReportController@stock_report')->name('stock_report.index');
	Route::get('/stock_ledger_report', 'ReportController@stock_ledger_report')->name('stock_ledger_report'); //added by alauddin

    Route::get('/in_house_sale_report', 'ReportController@in_house_sale_report')->name('in_house_sale_report.index');
    Route::get('/picked_up_report', 'ReportController@picked_up_report')->name('picked_up_report');
    Route::get('/seller_sale_report', 'ReportController@seller_sale_report')->name('seller_sale_report.index');
    Route::get('/customer_wishlist', 'ReportController@customer_wishlist')->name('customer_wishlist.index');
    Route::get('/wish_report', 'ReportController@wish_report')->name('wish_report.index');
    Route::get('/user_search_report', 'ReportController@user_search_report')->name('user_search_report.index');
    Route::get('/wallet-history', 'ReportController@wallet_transaction_history')->name('wallet-history.index');
    Route::any('/salesReport', 'ReportController@salesReport')->name('salesReport.index');
    Route::any('/customer_ledger_details', 'ReportController@customer_ledger_details')->name('customer_ledger_details.index');
    Route::get('/income_report', 'ReportController@income_report')->name('income_report.index');
    Route::get('/income_details_report/{id}', 'ReportController@income_details_report')->name('income_report.details');
    Route::get('/income_order_details_report/{id}', 'ReportController@income_order_details_report')->name('income_order_report.details'); //added by alauddin
	Route::get('/salesCouponDiscountReport', 'ReportController@salesCouponDiscountReport')->name('salesCouponDiscountReport.index'); //added by alauddin
	Route::get('/top_sale_report', 'ReportController@topSalesReport')->name('topSalesReport.index'); //added by alauddin
	Route::get('/product_wise_sales_details', 'ReportController@productWiseSalesDetails')->name('product_wise_sales_details'); //added by alauddin
    Route::get('/topSalesReport/topsaledetails.show/{shopuserid}/{profit}', 'ReportController@TopSalwDetailsShow')->name('topsaledetails.show');

    Route::get('/coupon_report_details', 'ReportController@coupon_report_details')->name('coupon_report_details'); 

    Route::get('/topCustomersReport', 'ReportController@topCustomersReport')->name('topCustomersReport.index');
    Route::get('/topCustomersReport/topcustomerdetails.show/{customerid}/{start_date}/{end_date}','ReportController@topCustomerDetailsShow')->name('topcustomerdetails.show'); //added by alauddin
    Route::any('/orderReport', 'ReportController@orderReport')->name('orderReport.index');//added by alauddin
    Route::any('/pendingOrderProductInventoryReport', 'ReportController@pendingOrderProductStockReport')->name('pendingOrderProductStockReport.index');//added by alauddin

    Route::get('/stock_closing', 'ReportController@stock_closing')->name('stock_closing'); //added by alauddin
    Route::get('/save_stock_closing', 'ReportController@save_stock_closing')->name('save_stock_closing'); //added by alauddin

Route::get('/top_selling_area', 'ReportController@top_selling_area_report')->name('top_selling_area_report.index');
Route::get('/first_order_customer_report', 'ReportController@firstorder_customer_report')->name('first_order_customer.report'); 

 // Downloads Reports 
 Route::get('/sales_pending_order_ledger_export', 'DownloadReportController@sales_pending_order_ledger_export')->name('sales_pending_order_ledger_export'); //added by alauddin
Route::get('/sales_pending_order_product_stock_ledger_export', 'DownloadReportController@sales_pending_order_product_stock_ledger_export')->name('sales_pending_order_product_stock_ledger_export'); //added by alauddin
 Route::get('/sales_ledger_export', 'DownloadReportController@sales_ledger_export')->name('sales_ledger_export');
 Route::get('/seller_sales_export', 'DownloadReportController@seller_sales_export')->name('seller_sales_export');
 Route::get('/products_stock_export', 'DownloadReportController@products_stock_export')->name('products_stock_export');
 Route::get('/productswise_sale_export', 'DownloadReportController@productswise_sale_export')->name('productswise_sale_export');
 Route::get('/tobepickup_report', 'DownloadReportController@tobepickup_report')->name('tobepickup_report');
 Route::get('/top_sale_download', 'DownloadReportController@top_sale_download')->name('top_sale_download');
 Route::get('/top_customer_download', 'DownloadReportController@top_customer_download')->name('top_customer_download');//added by alauddin
 Route::get('/product_wise_sales_details_download', 'DownloadReportController@productWiseSalesReport')->name('product_wise_sales_details_download'); //added by alauddin
Route::get('/income_report_download', 'DownloadReportController@income_report_download')->name('income_report_download');
Route::get('/sales_coupon_discount_ledger_export', 'DownloadReportController@sales_coupon_discount_ledger_export')->name('sales_coupon_discount_ledger_export'); //added by alauddin

 Route::get('/wishlist_report_download', 'DownloadReportController@wishlist_report_download')->name('wishlist_report_download');
 Route::get('/user_search_download', 'DownloadReportController@user_search_download')->name('user_search_download');
 Route::get('/customer_wishlist_download', 'DownloadReportController@customer_wishlist_download')->name('customer_wishlist_download');
 Route::get('/product_categories', 'ReportController@getProducts')->name('product.categories');
 Route::get('/monthly_stock_ledger_report', 'ReportController@monthly_stock_ledger_report')->name('monthly_stock_ledger_report.index');
    //Blog SectionF
    Route::resource('blog-category', 'BlogCategoryController');
    Route::get('/blog-category/destroy/{id}', 'BlogCategoryController@destroy')->name('blog-category.destroy');
    Route::resource('blog', 'BlogController');
    Route::get('/blog/destroy/{id}', 'BlogController@destroy')->name('blog.destroy');
    Route::post('/blog/change-status', 'BlogController@change_status')->name('blog.change-status');

    //Coupons
    Route::resource('coupon', 'CouponController');
    Route::get('/coupon/destroy/{id}', 'CouponController@destroy')->name('coupon.destroy');

    //Reviews
    Route::get('/reviews', 'ReviewController@index')->name('reviews.index');
    Route::post('/reviews/published', 'ReviewController@updatePublished')->name('reviews.published');

    //Support_Ticket
    Route::get('support_ticket/', 'SupportTicketController@admin_index')->name('support_ticket.admin_index');
    Route::get('support_ticket/{id}/show', 'SupportTicketController@admin_show')->name('support_ticket.admin_show');
    Route::post('support_ticket/reply', 'SupportTicketController@admin_store')->name('support_ticket.admin_store');

    //Pickup_Points
    Route::resource('pick_up_points', 'PickupPointController');
    Route::get('/pick_up_points/edit/{id}', 'PickupPointController@edit')->name('pick_up_points.edit');
    Route::get('/pick_up_points/destroy/{id}', 'PickupPointController@destroy')->name('pick_up_points.destroy');

    //conversation of seller customer
    Route::get('conversations', 'ConversationController@admin_index')->name('conversations.admin_index');
    Route::get('conversations/{id}/show', 'ConversationController@admin_show')->name('conversations.admin_show');

    Route::post('/sellers/profile_modal', 'SellerController@profile_modal')->name('sellers.profile_modal');
    Route::post('/sellers/approved', 'SellerController@updateApproved')->name('sellers.approved');

    Route::resource('attributes', 'AttributeController');
    Route::get('/attributes/edit/{id}', 'AttributeController@edit')->name('attributes.edit');
    Route::get('/attributes/destroy/{id}', 'AttributeController@destroy')->name('attributes.destroy');

    //Attribute Value
    Route::post('/store-attribute-value', 'AttributeController@store_attribute_value')->name('store-attribute-value');
    Route::get('/edit-attribute-value/{id}', 'AttributeController@edit_attribute_value')->name('edit-attribute-value');
    Route::post('/update-attribute-value/{id}', 'AttributeController@update_attribute_value')->name('update-attribute-value');
    Route::get('/destroy-attribute-value/{id}', 'AttributeController@destroy_attribute_value')->name('destroy-attribute-value');

    //Colors
    Route::get('/colors', 'AttributeController@colors')->name('colors');
    Route::post('/colors/store', 'AttributeController@store_color')->name('colors.store');
    Route::get('/colors/edit/{id}', 'AttributeController@edit_color')->name('colors.edit');
    Route::post('/colors/update/{id}', 'AttributeController@update_color')->name('colors.update');
    Route::get('/colors/destroy/{id}', 'AttributeController@destroy_color')->name('colors.destroy');

    Route::resource('addons', 'AddonController');
    Route::post('/addons/activation', 'AddonController@activation')->name('addons.activation');

    Route::get('/customer-bulk-upload/index', 'CustomerBulkUploadController@index')->name('customer_bulk_upload.index');
    Route::post('/bulk-user-upload', 'CustomerBulkUploadController@user_bulk_upload')->name('bulk_user_upload');
    Route::post('/bulk-customer-upload', 'CustomerBulkUploadController@customer_bulk_file')->name('bulk_customer_upload');
    Route::get('/user', 'CustomerBulkUploadController@pdf_download_user')->name('pdf.download_user');
    //Customer Package

    Route::resource('customer_packages', 'CustomerPackageController');
    Route::get('/customer_packages/edit/{id}', 'CustomerPackageController@edit')->name('customer_packages.edit');
    Route::get('/customer_packages/destroy/{id}', 'CustomerPackageController@destroy')->name('customer_packages.destroy');

    //Classified Products
    Route::get('/classified_products', 'CustomerProductController@customer_product_index')->name('classified_products');
    Route::post('/classified_products/published', 'CustomerProductController@updatePublished')->name('classified_products.published');

    //Shipping Configuration
    Route::get('/shipping_configuration', 'BusinessSettingsController@shipping_configuration')->name('shipping_configuration.index');
    Route::post('/shipping_configuration/update', 'BusinessSettingsController@shipping_configuration_update')->name('shipping_configuration.update');

    // Route::resource('pages', 'PageController');
    // Route::get('/pages/destroy/{id}', 'PageController@destroy')->name('pages.destroy');

    Route::resource('countries', 'CountryController');
    Route::post('/countries/status', 'CountryController@updateStatus')->name('countries.status');

    Route::resource('district', 'DistrictController');
    Route::get('/district/edit/{id}', 'DistrictController@edit')->name('district.edit');
    Route::get('/district/destroy/{id}', 'DistrictController@destroy')->name('district.destroy');

    Route::resource('cities', 'CityController');
    Route::get('/cities/edit/{id}', 'CityController@edit')->name('cities.edit');
    Route::get('/cities/destroy/{id}', 'CityController@destroy')->name('cities.destroy');

    Route::resource('areas', 'AreaController');
    Route::get('/areas/edit/{id}', 'AreaController@edit')->name('areas.edit');
    Route::get('/areas/destroy/{id}', 'AreaController@destroy')->name('areas.destroy');

    Route::view('/system/update', 'backend.system.update')->name('system_update');
    Route::view('/system/server-status', 'backend.system.server_status')->name('system_server');

    // uploaded files
    Route::any('/uploaded-files/file-info', 'AizUploadController@file_info')->name('uploaded-files.info');
    Route::resource('/uploaded-files', 'AizUploadController');
    Route::get('/uploaded-files/destroy/{id}', 'AizUploadController@destroy')->name('uploaded-files.destroy');

    Route::get('/all-notification', 'NotificationController@index')->name('admin.all-notification');
        Route::get('/bkash/refund/{id}', 'BkashController@getRefund')->name('get.refund');
        Route::post('/bkash/refund', 'BkashController@refund')->name('post.refund');
});
