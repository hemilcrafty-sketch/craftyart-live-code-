<?php

use App\Http\Controllers\CategoryFeatureController;
use App\Http\Controllers\ColorController;
use App\Http\Controllers\ContectUsWebControlller;
use App\Http\Controllers\FeatureController;
use App\Http\Controllers\FormateController;
use App\Http\Controllers\InterestController;
use App\Http\Controllers\LangController;
use App\Http\Controllers\NewSearchTagController;
use App\Http\Controllers\PendingTaskController;
use App\Http\Controllers\Pricing\BonusPackageController;
use App\Http\Controllers\Pricing\OfferPackageController;
use App\Http\Controllers\Pricing\PlanCategoryFeatureController;
use App\Http\Controllers\Pricing\PlanDurationController;
use App\Http\Controllers\Pricing\PlanFeatureController;
use App\Http\Controllers\Pricing\PlanUserDiscountController;
use App\Http\Controllers\Pricing\PricePlanController;
use App\Http\Controllers\PlanMetaDetailsController;
use App\Http\Controllers\RelegionController;
use App\Http\Controllers\ReviewsController;
use App\Http\Controllers\PReviewController;
use App\Http\Controllers\FrameCategoryController;
use App\Http\Controllers\FrameItemController;
use App\Http\Controllers\SearchTagController;
use App\Http\Controllers\SeoErrorListController;
use App\Http\Controllers\SizeController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\ThemeController;
use App\Http\Controllers\UserManageSubscriptionController;
use App\Http\Controllers\UserManageTemplateProductController;
use App\Http\Controllers\UserManageVideoProductController;
use App\Http\Controllers\SpecialPagesController;
use App\Http\Controllers\CustomDataExporter;
use App\Http\Controllers\PromoCodeController;
use App\Http\Controllers\TemplateRateController;
use App\Http\Controllers\RawDatasController;
use App\Http\Controllers\StickerCatController;
use App\Http\Controllers\StickerItemController;
use App\Http\Controllers\Utils\ContentManager;
use App\Http\Controllers\VectorCategoryController;
use App\Http\Controllers\VectorItemController;
use App\Http\Controllers\AudioCategoryController;
use App\Http\Controllers\AudioItemController;
use App\Http\Controllers\BgCatController;
use App\Http\Controllers\BgItemController;
use App\Http\Controllers\GifCategoryControllers;
use App\Http\Controllers\GifItemControllers;
use App\Http\Controllers\DensityCheckerController;
use App\Http\Controllers\OfferPopUpController;
use App\Http\Controllers\OrderUserController;
use App\Http\Controllers\RecentExpireController;
use App\Http\Controllers\PanelHistroyController;
use App\Http\Controllers\AiCreditController;
use App\Http\Controllers\Caricature\AttireController;
use App\Http\Controllers\Caricature\CaricatureCategoryController;
use App\Http\Controllers\Caricature\CaricatureHistoryController;
use App\Http\Controllers\AI\AiCreditTransactionController;
use App\Http\Controllers\Pricing\PaymentConfigController;
use App\Http\Controllers\Excel\UsersExport;
use App\Http\Middleware\IsAdminOrManager;
use App\Http\Middleware\isAdminOrDesignerManager;
use App\Http\Middleware\isAdminOrSeoManger;
use App\Http\Middleware\IsSalesAccess;
use App\Http\Middleware\IsSalesManagerAccess;
use App\Http\Controllers\Revenue\BusinessSupportController;
use App\Models\Template;
use App\Http\Middleware\IsAdmin;
use App\Models\PlanUserDiscount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Automation\AutomationConfigController;
use App\Http\Controllers\Automation\AutomationReportController;
use App\Http\Controllers\Automation\CampaignController;
use App\Http\Controllers\Automation\EmailTemplateController;
use App\Http\Controllers\Automation\WhatsAppTemplateController;
use App\Http\Controllers\Automation\WpFeedbackController;

use App\Http\Controllers\Video\VideoCatController;
use App\Http\Controllers\Video\VideoTemplateController;
use App\Http\Controllers\Video\VideoVirtualCategoryController;
use App\Http\Controllers\Video\VideoStyleController;
use App\Http\Controllers\Video\VideoThemeController;
use App\Http\Controllers\Video\VideoSearchTagController;
use App\Http\Controllers\Video\VideoInterestController;
use App\Http\Controllers\Video\VideoLangController;
use App\Http\Controllers\Video\VideoReligionController;
use App\Http\Controllers\Video\VideoReviewController;
use App\Http\Controllers\Video\VideoPageReviewController;
use App\Http\Controllers\Video\VideoSizeController;
use App\Http\Controllers\Revenue\CustomLeadsController;
use App\Http\Controllers\NoIndexController;
use App\Http\Controllers\UnifiedLeadsController;

use App\Http\Controllers\Creator\DesignerSystemController as CreatorDesignerSystemController;
use App\Http\Controllers\Creator\DesignerSystemSettingsController as CreatorDesignerSystemSettingsController;
use App\Http\Controllers\Vendor\ReferralDashboardController;
use App\Http\Controllers\Vendor\VendorController;
use App\Http\Controllers\Vendor\VendorWalletSettingController;

use App\Http\Controllers\Pricing\OfferPageController;

use App\Http\Controllers\CareerPageController;
use App\Http\Controllers\JobOpeningController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Middleware\IsAdminOrHr;



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

Auth::routes(['reset' => true, 'register' => false]);

Route::get('ip', function (Request $request) {
	    return [
        'ips' => $request->getClientIps(),
        'ip' => $request->ip(),
        'server' => [
            'X-Forwarded-For' => $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null,
            'X-Real-IP' => $_SERVER['HTTP_X_REAL_IP'] ?? null,
            'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]
    ];
	});
	
Route::group(['middleware' => ['restrict.ip']], function () {

		// Referral Dashboard
	Route::get('referral/dashboard', [ReferralDashboardController::class, 'index'])->name('referral.dashboard')->middleware(IsAdmin::class);
	Route::get('referral/{uid}', [ReferralDashboardController::class, 'show'])->name('referral.show')->middleware(IsAdmin::class);

	// Vendor Management (bank details & withdrawals — admin panel)
	Route::get('vendor/bank-details', [VendorController::class, 'adminBankDetails'])->name('vendor.bank_details')->middleware(IsAdmin::class);
	Route::get('vendor/withdrawals', [VendorController::class, 'adminWithdrawals'])->name('vendor.withdrawals')->middleware(IsAdmin::class);
	Route::get('vendor/withdrawal/{id}', [VendorController::class, 'adminShowWithdrawal'])->name('vendor.withdrawal.show')->middleware(IsAdmin::class);
	Route::post('vendor/withdrawal/{id}/accept', [VendorController::class, 'adminAcceptWithdrawal'])->name('vendor.withdrawal.accept')->middleware(IsAdmin::class);
	Route::post('vendor/withdrawal/{id}/reject', [VendorController::class, 'adminRejectWithdrawal'])->name('vendor.withdrawal.reject')->middleware(IsAdmin::class);
	Route::post('vendor/withdrawal/{id}/complete', [VendorController::class, 'adminCompleteWithdrawal'])->name('vendor.withdrawal.complete')->middleware(IsAdmin::class);

	// Vendor Wallet Settings
	Route::get('vendor/wallet-settings', [VendorWalletSettingController::class, 'index'])->name('vendor.wallet_settings')->middleware(IsAdmin::class);
	Route::get('vendor/wallet-settings/create', [VendorWalletSettingController::class, 'create'])->name('vendor.wallet_settings.create')->middleware(IsAdmin::class);
	Route::post('vendor/wallet-settings', [VendorWalletSettingController::class, 'store'])->name('vendor.wallet_settings.store')->middleware(IsAdmin::class);
	Route::get('vendor/wallet-settings/{id}/edit', [VendorWalletSettingController::class, 'edit'])->name('vendor.wallet_settings.edit')->middleware(IsAdmin::class);
	Route::put('vendor/wallet-settings/{id}', [VendorWalletSettingController::class, 'update'])->name('vendor.wallet_settings.update')->middleware(IsAdmin::class);
	Route::delete('vendor/wallet-settings/{id}', [VendorWalletSettingController::class, 'destroy'])->name('vendor.wallet_settings.destroy')->middleware(IsAdmin::class);
	Route::post('vendor/wallet-settings/{id}/toggle', [VendorWalletSettingController::class, 'toggleActive'])->name('vendor.wallet_settings.toggle')->middleware(IsAdmin::class);


	Route::middleware(['auth'])->prefix('designer-system')->name('designer_system.')->group(function () {
	// Designer Applications (only user_data email can apply; approve = creator=1 + DesignerProfile + DesignerWallet)
		Route::get('/applications', [CreatorDesignerSystemController::class, 'applications'])->name('applications');
		Route::post('/application/{id}/approve', [CreatorDesignerSystemController::class, 'approveApplication'])->name('application.approve');
		Route::post('/application/{id}/reject', [CreatorDesignerSystemController::class, 'rejectApplication'])->name('application.reject');

		// Designers List
		Route::get('/designers', [CreatorDesignerSystemController::class, 'designers'])->name('designers');

		// Design Submissions (Designer Head) — freelancer design + SEO flow
		Route::get('/design-submissions', [CreatorDesignerSystemController::class, 'designSubmissions'])->name('design_submissions');
		Route::get('/design/{id}/edit', [CreatorDesignerSystemController::class, 'editDesign'])->name('design.edit');
		Route::put('/design/{id}', [CreatorDesignerSystemController::class, 'updateDesign'])->name('design.update');
		Route::get('/design/{id}/seo', [CreatorDesignerSystemController::class, 'showDesignSeo'])->name('design.seo');
		Route::put('/design/{id}/seo', [CreatorDesignerSystemController::class, 'updateSeoDetails'])->name('design.seo.update');
		Route::post('/design/{id}/approve', [CreatorDesignerSystemController::class, 'approveDesign'])->name('design.approve');
		Route::post('/design/{id}/reject', [CreatorDesignerSystemController::class, 'rejectDesign'])->name('design.reject');

		// SEO Submissions (SEO Head) — approve/reject then publish to crafty_db.designs (edit_seo_item)
		Route::get('/seo-submissions', [CreatorDesignerSystemController::class, 'seoSubmissions'])->name('seo_submissions');
		// GET /publish: browsers/bookmarks hit this — redirect to SEO page (actual publish is POST only)
		Route::get('/design/{id}/publish', [CreatorDesignerSystemController::class, 'publishDesignRedirect'])->name('design.publish.get');
		Route::post('/design/{id}/publish', [CreatorDesignerSystemController::class, 'publishDesign'])->name('design.publish');
		Route::post('/design/{id}/reject-seo', [CreatorDesignerSystemController::class, 'rejectDesignBySeo'])->name('design.reject_seo');
		Route::post('/design/{id}/reapply-seo', [CreatorDesignerSystemController::class, 'reapplySeo'])->name('design.reapply_seo');

		// REMOVED: Old designer withdrawal routes
		// Freelancer withdrawals are now handled via vendor withdrawal system
		// See: /vendor/withdrawals (VendorManagementController)

		// Designer Types (crafty_creator)
		Route::get('/types', [CreatorDesignerSystemSettingsController::class, 'typesIndex'])->name('types');
		Route::post('/types', [CreatorDesignerSystemSettingsController::class, 'storeType'])->name('types.store');
		Route::put('/types/{id}', [CreatorDesignerSystemSettingsController::class, 'updateType'])->name('types.update');
		Route::delete('/types/{id}', [CreatorDesignerSystemSettingsController::class, 'deleteType'])->name('types.delete');
		Route::post('/types/{id}/toggle', [CreatorDesignerSystemSettingsController::class, 'toggleTypeActive'])->name('types.toggle');

		// Designer Goals (crafty_creator)
		Route::get('/goals', [CreatorDesignerSystemSettingsController::class, 'goalsIndex'])->name('goals');
		Route::post('/goals', [CreatorDesignerSystemSettingsController::class, 'storeGoal'])->name('goals.store');
		Route::put('/goals/{id}', [CreatorDesignerSystemSettingsController::class, 'updateGoal'])->name('goals.update');
		Route::delete('/goals/{id}', [CreatorDesignerSystemSettingsController::class, 'deleteGoal'])->name('goals.delete');
		Route::post('/goals/{id}/toggle', [CreatorDesignerSystemSettingsController::class, 'toggleGoalActive'])->name('goals.toggle');
	});


		// Unified Leads Management
	Route::get('/unified-leads', [UnifiedLeadsController::class, 'index'])->name('unified_leads.index')->middleware(IsSalesAccess::class);
	Route::post('/unified-leads/followup-update', [UnifiedLeadsController::class, 'followupUpdate'])->name('unified_leads.followup_update')->middleware(IsSalesAccess::class);

	Route::get('/wp-feedback', [WpFeedbackController::class, 'index'])->name('wp_feedback.index')->middleware(IsSalesAccess::class);
	Route::post('/wp-feedback/followup-update', [WpFeedbackController::class, 'followupUpdate'])->name('wp_feedback.followupUpdate')->middleware(IsSalesAccess::class);

	Route::resource("custom_leads", CustomLeadsController::class)->middleware(IsSalesAccess::class);
	Route::post('/custom-leads/followup-update', [CustomLeadsController::class, 'followupUpdate'])->name('custom_leads.followupUpdate')->middleware(IsSalesAccess::class);

	// Revenue Module Routes
	Route::get('business_support', [BusinessSupportController::class, 'showBusinessSupport'])->name('business_support')->middleware(IsSalesManagerAccess::class);
	Route::get('business_support_purchases', [BusinessSupportController::class, 'showBusinessSupport'])->name('business_support_purchases')->middleware(IsSalesManagerAccess::class);
	Route::post('business_support_purchases/followup', [BusinessSupportController::class, 'updateFollowup'])->name('business_support.followup')->middleware(IsSalesManagerAccess::class);

	Route::resource('caricature_history', CaricatureHistoryController::class)->middleware(IsAdmin::class);
	Route::get('caricature_history/payment/{payment_id}', [CaricatureHistoryController::class, 'showByPaymentId'])->name('caricature_history.showByPaymentId')->middleware(IsAdmin::class);

	Route::resource('order_user', OrderUserController::class)->middleware(IsSalesAccess::class);
	Route::get('/order-user/get-user-usage', [OrderUserController::class, 'getUserUsage'])->name('order_user.get_user_usage');
	Route::post('/order-user/validate-email', [OrderUserController::class, 'validateEmail'])->name('order_user.validate_email');
	Route::get('/order-user/purchase-history/{userId}', [OrderUserController::class, 'getPurchaseHistory'])->name('order_user.purchase_history');
	Route::resource('recent_expire', RecentExpireController::class)->middleware(IsSalesAccess::class);
	Route::post('/order-user/followup-update', [OrderUserController::class, 'followupUpdate'])->name('order_user.followupUpdate')->middleware(IsSalesAccess::class);
	Route::post('/recent-expire/followup-update', [RecentExpireController::class, 'followupUpdate'])->name('recent_expire.followupUpdate')->middleware(IsSalesAccess::class);
	Route::post('/order-user/create-payment-link', [OrderUserController::class, 'createPaymentLink'])->name('order_user.create_payment_link');
	Route::get('/order-user/get-plans', [OrderUserController::class, 'getPlans'])->name('order_user.get_plans');

	// Payment status check APIs (for testing/admin)
	Route::get('/order-user/check-phonepe-status/{merchantOrderId}', [OrderUserController::class, 'checkPhonePeStatusApi'])->name('order_user.check_phonepe_status');
	Route::get('/order-user/check-razorpay-status/{paymentLinkId}', [OrderUserController::class, 'checkRazorpayStatusApi'])->name('order_user.check_razorpay_status');

	// PhonePe custom payment page routes
	Route::get('/phonepe-payment/{referenceId}', [OrderUserController::class, 'showPhonePePaymentPage'])->name('phonepe.payment_page');
	Route::post('/phonepe-payment/initiate', [OrderUserController::class, 'initiatePhonePePayment'])->name('phonepe.initiate_payment');


	Route::get('/', [App\Http\Controllers\HomeController::class, 'index']);
	Route::get('/dashboard/{manager?}', [App\Http\Controllers\HomeController::class, 'index'])->name('dashboard');

	Route::get('show_attire_item', [AttireController::class,'show'])->name('show_attire_item');
	Route::post('create_attire', [AttireController::class,'store'])->name('create_attire');
	Route::get('edit_seo_attire/{id}', [AttireController::class, 'edit_seo'])->name('edit_seo_attire');
	Route::post('update_seo_attire/{id}', [AttireController::class, 'update_seo'])->name('update_seo_attire');
	Route::post('update_caricature_category', [AttireController::class, 'updateCategory'])->name('update_caricature_category');
	Route::post('attire.assign.newcategory', [AttireController::class, 'assignNewCategory'])->name('attire.assign.newcategory');
	Route::post('attire.assign-seo', [AttireController::class, 'assignSeo'])->name('attire.assign-seo');
	Route::post('attire.premium.update', [AttireController::class, 'updateAttirePremium'])->name('attire.premium.update');
	Route::post('attire_pinned/{id}', [AttireController::class, 'pinned_update'])->name('attire.pinned');
	Route::post('attire.editor.choice', [AttireController::class, 'editorChoiceUpdate'])->name('attire.editor.choice');
	Route::post('attire_status/{id}', [AttireController::class, 'status_update'])->name('attire.status');
	Route::post('delete_attire/{id}', [AttireController::class, 'destroy'])->name('attire.delete');
	Route::get('add_attire', [AttireController::class,'add'])->name('add_attire');
	Route::get('/edit_attire/{id}', [AttireController::class, 'edit'])->name('attire.edit');
	Route::post('/update_attire/{id}', [AttireController::class, 'update'])->name('attire.update');

	Route::get('show_cari_cat', [CaricatureCategoryController::class, 'show'])->name('show_cari_cat');
	Route::get('delete_cari_cat/{id}', [CaricatureCategoryController::class, 'destroy']);
	Route::get('create_cari_cat', [CaricatureCategoryController::class, 'create'])->name('create_cari_cat');
	Route::post('submit_cari_cat', [CaricatureCategoryController::class, 'store']);
	Route::get('edit_cari_cat/{id}', [CaricatureCategoryController::class, 'edit'])->name('edit_cari_cat');
	Route::post('update_cari_cat/{id}', [CaricatureCategoryController::class, 'update'])->name('update_cari_cat');

	Route::get('show_fonts', [App\Http\Controllers\FontController::class, 'show'])->name('show_fonts');
	Route::get('delete_font/{id}', [App\Http\Controllers\FontController::class, 'destroy']);
	Route::get('create_font', [App\Http\Controllers\FontController::class, 'create'])->name('create_font');
	Route::post('submit_font', [App\Http\Controllers\FontController::class, 'store']);
	Route::get('edit_font/{id}', [App\Http\Controllers\FontController::class, 'edit'])->name('edit_font');
	Route::post('update_font/{id}', [App\Http\Controllers\FontController::class, 'update'])->name('font.update');

	Route::get('font_families', [App\Http\Controllers\FontFamilyController::class, 'show'])->name('font_families');
	Route::post('get_font_family', [App\Http\Controllers\FontFamilyController::class, 'get'])->name('get_font_family');
	Route::post('submit_font_family', [App\Http\Controllers\FontFamilyController::class, 'add'])->name('font_family.create');
	Route::post('update_font_family', [App\Http\Controllers\FontFamilyController::class, 'update'])->name('font_family.update');
	Route::post('delete_font_family', [App\Http\Controllers\FontFamilyController::class, 'delete'])->name('font_family.delete');

	Route::get('font_list', [App\Http\Controllers\FontListController::class, 'show'])->name('font_list');
	Route::post('get_fontlist', [App\Http\Controllers\FontListController::class, 'get'])->name('get_fontlist');
	Route::post('submit_list', [App\Http\Controllers\FontListController::class, 'add'])->name('font_list.create');
	Route::post('update_list', [App\Http\Controllers\FontListController::class, 'update'])->name('font_list.update');
	Route::post('delete_list', [App\Http\Controllers\FontListController::class, 'delete'])->name('font_list.delete');

	Route::get('show_cat', 'App\Http\Controllers\CategoryController@show')->name('show_cat');
	Route::get('delete_cat/{id}', 'App\Http\Controllers\CategoryController@destroy');
	Route::get('create_cat', 'App\Http\Controllers\CategoryController@create')->name('create_cat');
	Route::post('submit_cat', 'App\Http\Controllers\CategoryController@store');
	Route::get('edit_cat/{id}', 'App\Http\Controllers\CategoryController@edit')->name('edit_cat');
	Route::post('update_cat/{id}', 'App\Http\Controllers\CategoryController@update')->name('cat.update');

	/* New Categories Creating */
	Route::get('show_new_cat', 'App\Http\Controllers\NewCategoryController@show')->name('show_new_cat');
	Route::get('delete_new_cat/{id}', 'App\Http\Controllers\NewCategoryController@destroy');
	Route::get('create_new_cat', 'App\Http\Controllers\NewCategoryController@create')->name('create_new_cat');
	Route::post('submit_new_cat', 'App\Http\Controllers\NewCategoryController@store');
	Route::get('edit_new_cat/{id}', 'App\Http\Controllers\NewCategoryController@edit')->name('edit_new_cat');
	Route::post('update_new_cat/{id}', 'App\Http\Controllers\NewCategoryController@update')->name('new_cat.update');
	Route::post('cat_imp/{id}', 'App\Http\Controllers\NewCategoryController@imp_update')->name('cat.imp');

	// Route::get('preview/new_cat/{id}', 'App\Http\Controllers\NewCategoryController@preview')->name('preview_new_cat');
	Route::get('{mode}/new_cat/{id}', 'App\Http\Controllers\NewCategoryController@preview')->name('preview_new_cat');
	/* End Categories Creating */

	Route::get('show_virtual_cat', 'App\Http\Controllers\VirtualCategoryController@index')->name('show_virtual_cat');
	Route::get('create_virtual_cat', 'App\Http\Controllers\VirtualCategoryController@create')->name('create_virtual_cat');
	Route::post('submit_virtual_cat', 'App\Http\Controllers\VirtualCategoryController@store')->name('submit_virtual_cat');
	Route::get('edit_virtual_cat/{id}', 'App\Http\Controllers\VirtualCategoryController@edit')->name('edit_virtual_cat');
	Route::post('update_virtual_cat/{id}', 'App\Http\Controllers\VirtualCategoryController@update')->name('new_virtual_cat.update');
	Route::post('virtual', 'App\Http\Controllers\VirtualCategoryController@getVirtual')->name('virtual');

	Route::get('show_sub_cat', 'App\Http\Controllers\SubCategoryController@show_sub_cat')->name('show_sub_cat');
	Route::post('submit_sub_cat', 'App\Http\Controllers\SubCategoryController@addSubCat');
	Route::post('update_sub_cat/{id}', 'App\Http\Controllers\SubCategoryController@updateSubCat')->name('subCat.update');
	Route::post('delete_sub_cat/{id}', 'App\Http\Controllers\SubCategoryController@deleteSubCat')->name('subCat.delete');

	Route::post('submit_style', [App\Http\Controllers\StyleController::class, 'submitStyle']);
	Route::get('show_style', 'App\Http\Controllers\StyleController@show_style')->name('show_style');
	Route::post('delete_style/{id}', 'App\Http\Controllers\StyleController@deleteStyle')->name('style.delete');

	Route::get('show_keyword', 'App\Http\Controllers\KeywordController@show')->name('show_keyword');
	Route::get('create_keyword', 'App\Http\Controllers\KeywordController@create')->name('create_keyword');
	Route::get('edit_keyword/{id?}', 'App\Http\Controllers\KeywordController@edit')->name('edit_keyword');

	Route::get('page_slug_history', 'App\Http\Controllers\PageSlugHistoryController@show')->name('page_slug_history')->middleware(isAdminOrSeoManger::class); // v2update
	Route::post('create_page_slug', 'App\Http\Controllers\PageSlugHistoryController@add')->name('create_page_slug')->middleware(isAdminOrSeoManger::class); // v2update
	Route::post('edit_page_slug/{id}', 'App\Http\Controllers\PageSlugHistoryController@update')->name('edit_page_slug')->middleware(isAdminOrSeoManger::class); // v2update

	Route::post('get_keyword', 'App\Http\Controllers\KeywordController@get');
	Route::post('submit_keyword', 'App\Http\Controllers\KeywordController@add');
	Route::post('update_keyword/{id}', 'App\Http\Controllers\KeywordController@update')->name('keyword.update');
	Route::post('delete_keyword/{id}', 'App\Http\Controllers\KeywordController@delete')->name('keyword.delete');

	// theme
	Route::get('show_theme', 'App\Http\Controllers\ThemeController@show_theme')->name('show_theme');
	Route::post('submit_theme', [ThemeController::class, 'submitTheme']);
	Route::post('delete_theme/{id}', 'App\Http\Controllers\ThemeController@deleteTheme')->name('theme.delete');
	// releted tag
	Route::get('show_search_tag', [SearchTagController::class, 'show_search_tag'])->name('show_search_tag');
	Route::post('submit_search_tag', [SearchTagController::class, 'submitSearchTag']);
	Route::post('delete_search_tag/{id}', [SearchTagController::class, 'deleteSearchTag'])->name('searchTag.delete');

	// interest
	Route::get('show_interest', [InterestController::class, 'showInterest'])->name('show_interest');
	Route::post('interest_store_or_update', [InterestController::class, 'storeOrUpdate'])->name('interest_store_or_update');
	Route::post('delete_interest/{id}', [InterestController::class, 'deleteInterest'])->name('interest.delete');

	// language
	Route::get('show_lang', [LangController::class, 'showLang'])->name('show_lang');
	Route::post('store_or_update_lang', [LangController::class, 'storeOrUpdateLang'])->name('store_or_update_lang');
	Route::post('delete_lang/{id}', [LangController::class, 'deleteLang'])->name('lang.delete');

	Route::get('create_editable_mode', 'App\Http\Controllers\EditableModesController@create')->name('create_editable_mode');
	Route::post('submit_editable_mode', 'App\Http\Controllers\EditableModesController@store');
	Route::post('update_editable_mode/{id}', 'App\Http\Controllers\EditableModesController@update')->name('editable_mode.update');
	Route::post('delete_editable_mode/{id}', 'App\Http\Controllers\EditableModesController@delete')->name('editable_mode.delete');

	Route::get('show_item/{isInActive?}', 'App\Http\Controllers\TemplateController@show')->name('show_item');
	// Route::get('in_active_template','App\Http\Controllers\TemplateController@getInActiveTemplate')->name('getInActiveTemplate');

	Route::post('delete_item/{id}', 'App\Http\Controllers\TemplateController@destroy')->name('item.delete');
	Route::post('create_item', 'App\Http\Controllers\TemplateController@create')->name('create_item');
	Route::post('submit_item', 'App\Http\Controllers\TemplateController@store');
	Route::post('update_item/{id}', 'App\Http\Controllers\TemplateController@update')->name('item.update');
	Route::post('update_seo_item/{id}', 'App\Http\Controllers\TemplateController@update_seo')->name('item.update_seo');
	Route::post('update_temp_category', 'App\Http\Controllers\TemplateController@updateTempCategory')->name('update.temp_category');

	Route::get('edit_item/{id}', 'App\Http\Controllers\TemplateController@edit')->name('edit_item');
	Route::get('edit_seo_item/{id}', 'App\Http\Controllers\TemplateController@edit_seo')->name('edit_seo_item');
	Route::post('get_custom_item_data/getCustomData', 'App\Http\Controllers\TemplateController@getCustomData')->name('item.custom_data');
	Route::post('reset_date/{id}', 'App\Http\Controllers\TemplateController@reset_date')->name('reset.date');
	Route::post('reset_creation/{id}', 'App\Http\Controllers\TemplateController@reset_creation')->name('reset.creation');
	Route::post('temp_status/{id}', 'App\Http\Controllers\TemplateController@status_update')->name('temp.status');
	Route::post('temp_pinned/{id}', 'App\Http\Controllers\TemplateController@pinned_update')->name('temp.pinned');
	Route::post('temp.assign-seo', [TemplateController::class, 'assignSeo'])->name('temp.assign-seo');
	Route::post('/design/assign-newcategory', [TemplateController::class, 'assignNewCategory'])->name('design.assign.newcategory');

	Route::post('temp.editor.choice', [TemplateController::class, 'editorChoiceUpdate'])->name('temp.editor.choice');
	Route::post('temp.premium.update', [TemplateController::class, 'updateTemplatePremium'])->name('temp.premium.update');

	Route::post('remove-additional-thumb/{id}', [TemplateController::class, 'removeAdditionalThumb'])->name('item.remove_additional_thumb');


	// ============================================
	// VIDEO ROUTES
	// ============================================

	// Video Categories
	Route::get('show_v_cat', [VideoCatController::class, 'show'])->name('show_v_cat');
	Route::get('create_v_cat', [VideoCatController::class, 'create'])->name('create_v_cat');
	Route::post('submit_v_cat', [VideoCatController::class, 'store'])->name('v_cat.store');
	Route::get('edit_v_cat/{id}', [VideoCatController::class, 'edit'])->name('edit_v_cat');
	Route::post('update_v_cat/{id}', [VideoCatController::class, 'update'])->name('v_cat.update');
	Route::get('delete_v_cat/{id}', [VideoCatController::class, 'destroy'])->name('v_cat.delete');
	Route::post('v_cat_imp/{id}', [VideoCatController::class, 'imp_update'])->name('v_cat.imp');

	// Video Templates
	Route::get('show_v_item', [VideoTemplateController::class, 'show'])->name('show_v_item');
	Route::get('create_v_item', [VideoTemplateController::class, 'create'])->name('create_v_item');
	Route::post('submit_v_item', [VideoTemplateController::class, 'store'])->name('v_item.store');
	Route::get('edit_v_item/{id}', [VideoTemplateController::class, 'edit'])->name('edit_v_item');
	Route::post('update_v_item/{id}', [VideoTemplateController::class, 'update'])->name('v_item.update');
	Route::post('delete_v_item/{id}', [VideoTemplateController::class, 'destroy'])->name('v_item.delete');
	Route::get('edit_seo_v_item/{id}', [VideoTemplateController::class, 'editSeo'])->name('edit_seo_v_item');
	Route::post('update_seo_v_item/{id}', [VideoTemplateController::class, 'updateSeo'])->name('v_item_seo.update');
	Route::post('v_item_noindex/{id}', [VideoTemplateController::class, 'noindex_update'])->name('v_item.noindex');
	Route::post('v_item_assign_seo', [VideoTemplateController::class, 'assignSeo'])->name('v_item.assign-seo');
	Route::post('v_item_assign_category', [VideoTemplateController::class, 'assignCategory'])->name('v_item.assign-category');
	Route::post('loadVideoSizeAndTheme', [VideoTemplateController::class, 'loadVideoSizeAndTheme'])->name('loadVideoSizeAndTheme');

	// Video Virtual Categories
	Route::get('show_video_virtual_cat', [VideoVirtualCategoryController::class, 'index'])->name('show_video_virtual_cat');
	Route::get('create_video_virtual_cat', [VideoVirtualCategoryController::class, 'create'])->name('create_video_virtual_cat');
	Route::post('submit_video_virtual_cat', [VideoVirtualCategoryController::class, 'store'])->name('submit_video_virtual_cat');
	Route::get('edit_video_virtual_cat/{id}', [VideoVirtualCategoryController::class, 'edit'])->name('edit_video_virtual_cat');
	Route::post('update_video_virtual_cat/{id}', [VideoVirtualCategoryController::class, 'store'])->name('video_virtual_cat.update');
	Route::get('delete_video_virtual_cat/{id}', [VideoVirtualCategoryController::class, 'destroy'])->name('delete_video_virtual_cat');

	// Video Styles
	// Route::get('show_video_style', [VideoStyleController::class, 'show_video_style'])->name('show_video_style');
	// Route::post('submit_video_style', [VideoStyleController::class, 'submitStyle'])->name('video_style.submit');
	// Route::post('delete_video_style/{id}', [VideoStyleController::class, 'deleteStyle'])->name('video_style.delete');

	// Video Themes
	Route::get('show_video_theme', [VideoThemeController::class, 'show_video_theme'])->name('show_video_theme');
	Route::post('submit_video_theme', [VideoThemeController::class, 'submitTheme'])->name('video_theme.submit');
	Route::post('delete_video_theme/{id}', [VideoThemeController::class, 'deleteTheme'])->name('video_theme.delete');

	// Video Search Tags
	Route::get('show_video_search_tag', [VideoSearchTagController::class, 'show_video_search_tag'])->name('show_video_search_tag');
	Route::post('submit_video_search_tag', [VideoSearchTagController::class, 'submitVideoSearchTag'])->name('video_search_tag.submit');
	Route::post('delete_video_search_tag/{id}', [VideoSearchTagController::class, 'deleteVideoSearchTag'])->name('video_search_tag.delete');

	// Video Interests
	// Route::get('show_video_interest', [VideoInterestController::class, 'showInterest'])->name('show_video_interest');
	// Route::post('store_or_update_video_interest', [VideoInterestController::class, 'storeOrUpdateInterest'])->name('store_or_update_video_interest');
	// Route::post('delete_video_interest/{id}', [VideoInterestController::class, 'deleteInterest'])->name('video_interest.delete');

	// Video Languages
	Route::get('show_video_lang', [VideoLangController::class, 'showLanguage'])->name('show_video_lang');
	Route::post('store_or_update_video_lang', [VideoLangController::class, 'storeOrUpdateLanguage'])->name('store_or_update_video_lang');
	Route::post('delete_video_lang/{id}', [VideoLangController::class, 'deleteLanguage'])->name('video_lang.delete');

	// Video Religions
	Route::get('video_religions', [VideoReligionController::class, 'index'])->name('video_religions.index');
	Route::post('video_religions/submit', [VideoReligionController::class, 'submit'])->name('video_religions.submit');
	Route::delete('video_religions/{id}', [VideoReligionController::class, 'destroy'])->name('video_religions.destroy');

	// Video Reviews Routes (Simple Reviews for Videos)
	Route::resource('video_reviews', VideoReviewController::class);
	Route::post('/video-reviews/status', [VideoReviewController::class, 'reviewStatus'])->name('video_reviews.reviewStatus');

	// Video Page Reviews Routes (Page Reviews for Videos)
	Route::resource('video_page_reviews', VideoPageReviewController::class);
	Route::post('/video-page-reviews/status', [VideoPageReviewController::class, 'reviewStatus'])->name('video_page_reviews.reviewStatus');
	Route::get('/video-page-reviews/video-page-data', [VideoPageReviewController::class, 'getSelectedVideoPageData'])->name('get_selected_video_page_data');
	Route::get('/video-page-review/video-page-title', [VideoPageReviewController::class, 'getSelectedVideoPageTitle'])->name('get_selected_video_page_title');

	Route::resource('video_sizes', VideoSizeController::class);

	// ============================================
	// END VIDEO ROUTES
	// ============================================

	Route::resource('raw_datas', RawDatasController::class);
	Route::get('edit_rawdata/{id}', [RawDatasController::class, 'edit'])->name('edit_rawdata');

	Route::resource('show_sticker_cat', StickerCatController::class);
	Route::resource('sticker_item', StickerItemController::class);

	Route::post('stk_status/{id}', [StickerItemController::class, 'status_update'])->name('stk.status');
	Route::post('stk_premium/{id}', [StickerItemController::class, 'premium_update'])->name('stk.premium');

	Route::resource('vector_categories', VectorCategoryController::class);
	Route::resource('vector_items', VectorItemController::class);
	Route::post('updateVectorItem', [VectorItemController::class, 'updateVectorItemPremium'])->name('vectorItem.premium');

	Route::resource('audio_cat', AudioCategoryController::class);
	Route::resource('audio_items', AudioItemController::class);
	Route::post('updateAudioItem', [AudioItemController::class, 'updateAudioItemPremium'])->name('audioItem.premium');

	Route::resource('show_bg_cat', BgCatController::class);
	Route::resource('show_bg_item', BgItemController::class);
	Route::post('updatebackgroundItem', [BgItemController::class, 'updatebackgroundItemPremium'])->name('backgroundItem.premium');

	Route::resource('gif_categories', GifCategoryControllers::class);
	Route::resource('gif_items', GifItemControllers::class);
	Route::post('updategifItem', [GifItemControllers::class, 'updategifItemPremium'])->name('gifItem.premium');

	Route::resource('special_page', SpecialPagesController::class);
	Route::get('create_pages', [SpecialPagesController::class, 'create'])->name('create_pages');
	Route::get('edit_pages/{id}', [SpecialPagesController::class, 'create'])->name('edit_pages');
	Route::post('submit_pages', [SpecialPagesController::class, 'addUpdatePage']);
	Route::post('/page/add-update-pages', [SpecialPagesController::class, 'addUpdatePage'])->name('add.update.form');

	Route::get('import_json', 'App\Http\Controllers\JsonController@create')->name('import_json');
	Route::post('submit_json', 'App\Http\Controllers\JsonController@store');

	Route::post('import_page', 'App\Http\Controllers\JsonPageController@import_page')->name('import_page');

	Route::post('sendPosterNotification/{id}', 'App\Http\Controllers\Api\NotificationController@sendPosterNotification')->name('poster.notification');
	Route::post('sendCategoryNotification/{id}', 'App\Http\Controllers\Api\NotificationController@sendCategoryNotification')->name('cat.notification');

	Route::post('/check-density-by-slug', [DensityCheckerController::class, 'checkFromSlug'])->name('density.check.slug');
	Route::post('/density-checker/primary-check', [DensityCheckerController::class, 'checkPrimaryKeyword'])->name('density-checker.primary-check');

	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	Route::post('sendCustomNotification', 'App\Http\Controllers\Api\NotificationController@sendCustomNotification')->name('custom.notification');

	Route::post('check_n_i', [NoIndexController::class, 'checkNoindex'])->name('check_n_i')->middleware(isAdminOrSeoManger::class);
	Route::post('check_status', [NoIndexController::class, 'checkStatus'])->name('check_status')->middleware(isAdminOrSeoManger::class);
	Route::post('check_premium', [NoIndexController::class, 'checkPremium'])->name('check_premium')->middleware(isAdminOrSeoManger::class);

	Route::get('show_orders', 'App\Http\Controllers\CustomOrder\CustomOrderController@show')->name('show_orders')->middleware(IsAdmin::class);

	Route::get('show_messages', 'App\Http\Controllers\InAppMessageController@show')->name('show_messages')->middleware(IsSalesManagerAccess::class);
	Route::post('submit_message', 'App\Http\Controllers\InAppMessageController@add')->middleware(IsSalesManagerAccess::class);
	Route::post('update_message/{id}', 'App\Http\Controllers\InAppMessageController@update')->name('message.update')->middleware(IsSalesManagerAccess::class);
	Route::post('delete_message/{id}', 'App\Http\Controllers\InAppMessageController@delete')->name('message.delete')->middleware(IsSalesManagerAccess::class);

	Route::get('show_app', 'App\Http\Controllers\AppCategoryController@show')->name('show_app')->middleware(IsAdmin::class);
	Route::get('delete_app/{id}', 'App\Http\Controllers\AppCategoryController@destroy')->middleware(IsAdmin::class);
	Route::get('create_app', 'App\Http\Controllers\AppCategoryController@create')->middleware(IsAdmin::class);
	Route::post('submit_app', 'App\Http\Controllers\AppCategoryController@store')->middleware(IsAdmin::class);
	Route::get('edit_app/{id}', 'App\Http\Controllers\AppCategoryController@edit')->middleware(IsAdmin::class);
	Route::post('update_app/{id}', 'App\Http\Controllers\AppCategoryController@update')->name('app.update')->middleware(IsAdmin::class);

	Route::get('show_employee', 'App\Http\Controllers\EmployeeController@show')->name('show_employee')->middleware(IsAdminOrManager::class);
	Route::post('create_employee', 'App\Http\Controllers\EmployeeController@create')->middleware(IsAdminOrManager::class);
	Route::post('update_employee/{id}', 'App\Http\Controllers\EmployeeController@update')->name('employee.update')->middleware(IsAdminOrManager::class);
	Route::post('reset_employee/{id}', 'App\Http\Controllers\EmployeeController@resetPassword')->name('employee.reset')->middleware(IsAdminOrManager::class);
	Route::post('delete_employee/{id}', 'App\Http\Controllers\EmployeeController@destroy')->name('employee.delete')->middleware(IsAdminOrManager::class);

	Route::get('show_users', 'App\Http\Controllers\UserController@show')->name('show_users')->middleware(IsAdmin::class);

	Route::get('user_detail/{id}', 'App\Http\Controllers\UserController@user_detail')->middleware(IsSalesManagerAccess::class);

	Route::resource('ai_credits', AiCreditController::class)->middleware(IsAdmin::class);
	Route::post('ai_credits/submit', [AiCreditController::class, 'submit'])->name('ai_credits.submit')->middleware(IsAdmin::class);

	Route::get('show_packages', 'App\Http\Controllers\SubscriptionController@show_package')->name('show_packages')->middleware(IsAdmin::class);
	Route::post('submit_package', 'App\Http\Controllers\SubscriptionController@addPackage')->middleware(IsAdmin::class);
	Route::post('update_package/{id}', 'App\Http\Controllers\SubscriptionController@updatePackage')->name('package.update')->middleware(IsAdmin::class);
	Route::post('delete_package/{id}', 'App\Http\Controllers\SubscriptionController@deletePackage')->name('delete.update')->middleware(IsAdmin::class);

	Route::get('payment_setting', 'App\Http\Controllers\SubscriptionController@showPaymentSetting')->name('payment_setting')->middleware(IsAdmin::class);
	Route::post('update_payment/{id}', 'App\Http\Controllers\SubscriptionController@updatePaymentSetting')->name('payment.update')->middleware(IsAdmin::class);
	Route::get('transcation_logs', 'App\Http\Controllers\SubscriptionController@showTranscation')->name('transcation_logs')->middleware(IsAdmin::class);
	Route::get('upcoming_mandates', 'App\Http\Controllers\SubscriptionController@upcomingMandates')->name('upcoming_mandates')->middleware(IsAdmin::class);
	Route::get('free_exports', 'App\Http\Controllers\SubscriptionController@freeExports')->name('free_exports')->middleware(IsAdmin::class);
	Route::get('purchases', 'App\Http\Controllers\SubscriptionController@showPurchases')->name('purchases')->middleware(IsAdmin::class);
	Route::get('cari_purchases', 'App\Http\Controllers\SubscriptionController@showCariPurchases')->name('cari_purchases')->middleware(IsAdmin::class);
	Route::get('ai_credit_purchases', 'App\Http\Controllers\SubscriptionController@showAiCreditPurchases')->name('ai_credit_purchases')->middleware(IsAdmin::class);
	Route::any('add_credit_bonus', 'App\Http\Controllers\SubscriptionController@addAiCreditBouns')->name('add_credit_bonus')->middleware(IsAdmin::class);
	Route::any('transactions/refund', 'App\Http\Controllers\SubscriptionController@processRefund')->name('transactions.refund')->middleware(IsSalesManagerAccess::class);
	Route::get('credit_transaction_logs', [AiCreditTransactionController::class, 'index'])->name('credit_transaction_logs')->middleware(IsAdmin::class);


	Route::get('notification_setting', 'App\Http\Controllers\NotificationController@showNotificationSetting')->name('notification_setting')->middleware(IsAdmin::class);
	Route::post('update_notification/{id}', 'App\Http\Controllers\NotificationController@updateNotificationSetting')->name('notification.update')->middleware(IsAdmin::class);
	Route::post('update_ip', 'App\Http\Controllers\NotificationController@updateIpSetting')->name('ip.update')->middleware(IsAdmin::class);

	Route::post('update_cache_ver', 'App\Http\Controllers\HomeController@update_cache_ver')->middleware(IsAdmin::class);

	Route::get('show_feedbacks', 'App\Http\Controllers\FeedbackController@showFeedbacks')->name('show_feedbacks')->middleware(IsSalesManagerAccess::class);
	Route::get('show_contacts', 'App\Http\Controllers\FeedbackController@showContacts')->name('show_contacts')->middleware(IsSalesManagerAccess::class);
	Route::post('user/getChatData', 'App\Http\Controllers\FeedbackController@getChatData')->name('user.getChatData')->middleware(IsSalesManagerAccess::class);
	Route::post('send_reply', 'App\Http\Controllers\FeedbackController@send_reply')->middleware(IsSalesManagerAccess::class);
	Route::get('getFeedback/{id}', 'App\Http\Controllers\FeedbackController@getFeedback')->middleware(IsSalesManagerAccess::class);
	Route::get('getContact/{id}', 'App\Http\Controllers\FeedbackController@getContact')->middleware(IsSalesManagerAccess::class);
	Route::get('/contact_us_web', [ContectUsWebControlller::class, 'index'])->name('contact_us_web')->middleware(IsSalesManagerAccess::class);
	Route::post('/contact_us_web/followup-update', [ContectUsWebControlller::class, 'followupUpdate'])->name('contact_us_web.followupUpdate')->middleware(IsSalesAccess::class);

	// Route::get('/show_contect_us_web', [ContectUsWebControlller::class, 'index'])->name('show_contect_us_web');


	Route::resource('promocode', PromoCodeController::class)->middleware(IsAdmin::class);
	Route::get('/get_users_by_email', [PromoCodeController::class, 'getUsersByEmail'])->name('get_users_by_email')->middleware(IsAdmin::class);
	Route::get('/get_users_by_ids', [PromoCodeController::class, 'getUsersByIds'])->name('get_users_by_ids')->middleware(IsAdmin::class);

	Route::post('customTranscation', 'App\Http\Controllers\Api\SubscriptionController@customTranscation')->name('custom.transcation')->middleware(IsAdmin::class);
	Route::get('show_pending_task', 'App\Http\Controllers\PendingTaskController@show')->name('show_pending_task');
	Route::post('/pending-task/approve', [PendingTaskController::class, 'approve'])->name('pending-task.approve');
	Route::post('/pending-task/reject', [PendingTaskController::class, 'reject'])->name('pending-task.reject');
	Route::get('/pending-task/preview/{id}', [PendingTaskController::class, 'preview'])->name('pending-task.preview');
	Route::get('rejecte_task', [PendingTaskController::class, 'rejecteTask'])->name('rejecte_task');


	//Route::get('refreshTanscation', [App\Http\Controllers\Api\PaymentController::class, 'refreshTanscation'])->name('refreshTanscation')->middleware(IsAdmin::class);

	Route::get('/clear-cache', function () {
	    Artisan::call('optimize');
	    Artisan::call('route:cache');
	    Artisan::call('route:clear');
	    Artisan::call('view:clear');
	    Artisan::call('config:cache');
	    Artisan::call('config:clear');
	    Artisan::call('cache:clear');
	    return '<h1>Cache facade value cleared</h1>';
	})->middleware(IsAdmin::class);

	Route::get('/clear-trending', function () {
	    Template::query()->update(array('trending_views' => 0));
	    return '<h1>Trending cleared</h1>';
	})->middleware(IsAdmin::class);

	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	// Route::get('/uploadedFiles/v/{file}', [App\Http\Controllers\Api\DownloadController::class, 'v']);

	// Route::get('/uploadedFiles/video_file/{file}', [App\Http\Controllers\Api\DownloadController::class, 'video_file']);
	// Route::get('/uploadedFiles/vCatThumb/{file}', [App\Http\Controllers\Api\DownloadController::class, 'vCatThumb']);
	// Route::get('/uploadedFiles/vThumb_file/{file}', [App\Http\Controllers\Api\DownloadController::class, 'vThumb_file']);
	// Route::get('/uploadedFiles/vZip_file/{file}', [App\Http\Controllers\Api\DownloadController::class, 'vZip_file']);

	// Route::get('/uploadedFiles/bg_file/{file}', [App\Http\Controllers\Api\DownloadController::class, 'bg_file']);
	// Route::get('/uploadedFiles/sticker_file/{file}', [App\Http\Controllers\Api\DownloadController::class, 'sticker_file']);
	// Route::get('/uploadedFiles/catThumb/{file}', [App\Http\Controllers\Api\DownloadController::class, 'catThumb']);
	// Route::get('/uploadedFiles/parse_file/{file}', [App\Http\Controllers\Api\DownloadController::class, 'parse_file']);
	// Route::get('/uploadedFiles/sticker_file/{file}', [App\Http\Controllers\Api\DownloadController::class, 'sticker_file']);
	Route::get('/uploadedFiles/thumb_file/{file}', [App\Http\Controllers\Api\DownloadController::class, 'thumb_file']);
	// Route::get('/uploadedFiles/font_file/{file}', [App\Http\Controllers\Api\DownloadController::class, 'font_file']);
	// Route::get('/uploadedFiles/font_thumb/{file}', [App\Http\Controllers\Api\DownloadController::class, 'font_thumb']);
	// Route::get('/uploadedFiles/user_dp/{file}', [App\Http\Controllers\Api\DownloadController::class, 'user_dp']);
	// Route::get('/uploadedFiles/message_file/{file}', [App\Http\Controllers\Api\DownloadController::class, 'message_file']);
	// Route::get('/uploadedFiles/contact_ss/{file}', [App\Http\Controllers\Api\DownloadController::class, 'contact_ss']);
	// Route::get('/uploadedFiles/notifi_file/{file}', [App\Http\Controllers\Api\DownloadController::class, 'notifi_file']);
	// Route::get('/uploadedFiles/customOrder/{folder}/{file}', [App\Http\Controllers\Api\DownloadController::class, 'customOrder']);
	// Route::get('/uploadedFiles/brandKit/{file}', [App\Http\Controllers\Api\DownloadController::class, 'brandKit']);
	// Route::get('/uploadedFiles/zip_file/{file}', [App\Http\Controllers\Api\DownloadController::class, 'zip_file']);
	// Route::get('/uploadedFiles/d_zip_file/{file}', [App\Http\Controllers\Api\DownloadController::class, 'd_zip_file']);
	// Route::get('/uploadedFiles/crafty_assets/{file}', [App\Http\Controllers\Api\DownloadController::class, 'crafty_assets']);

	// Route::get('/uploadedFiles/frame_thumb/{file}', [App\Http\Controllers\Api\DownloadController::class, 'frame_thumb']);
	// Route::get('/uploadedFiles/frame_file/{file}', [App\Http\Controllers\Api\DownloadController::class, 'frame_file']);

	// Route::get('/uploadedFiles/designs/{file}', [App\Http\Controllers\Api\DownloadController::class, 'designs']);
	Route::get('/uploadedFiles/fab_jsons/{file}', [App\Http\Controllers\Api\DownloadController::class, 'designs']);
	// Route::get('/uploadedFiles/fab_designs/{file}', [App\Http\Controllers\Api\DownloadController::class, 'fab_designs']);

	// Route::get('/uploadedFiles/draftTb/{file}', [App\Http\Controllers\Api\DownloadController::class, 'draftTb']);
	// Route::get('/uploadedFiles/drafts/{file}', [App\Http\Controllers\Api\DownloadController::class, 'drafts']);

	// Route::get('/uploadedFiles/{file}', [App\Http\Controllers\Api\DownloadController::class, 'uploadedFiles']);
	// Route::get('/uploadedFiles', [App\Http\Controllers\Api\DownloadController::class, 'uploadedFiles']);

	// Route::get('/uploadedFiles/cta_images/{file}', [App\Http\Controllers\Api\DownloadController::class, 'cta_images']);

	Route::resource('templateRate', TemplateRateController::class)->middleware(IsAdmin::class);
	Route::resource('caricatureRate', TemplateRateController::class)->middleware(IsAdmin::class);
	Route::post('downloadImages', [App\Http\Controllers\Api\DownloadController::class, 'downloadImages'])->middleware(IsAdmin::class);

	/* 17/04/2024 */

	Route::resource('sizes', SizeController::class);
	// Route::resource('colors', ColorController::class)->middleware(IsAdmin::class);
	Route::resource('religions', RelegionController::class);
	Route::post('religions/submit', [RelegionController::class, 'submit'])->name('religions.submit');

	// plan 
	Route::resource('planduration', PlanDurationController::class)->middleware(IsAdmin::class);
	Route::resource('plans', PricePlanController::class)->middleware(IsAdmin::class);
	// Route::delete('/subplans/{subPlan}', [PricePlanController::class, 'subPlanDestroy'])->name('subplans.destroy');
	Route::resource('categoryFeatures', PlanCategoryFeatureController::class)->middleware(IsAdmin::class);
	Route::resource('features', PlanFeatureController::class)->middleware(IsAdmin::class);

	Route::prefix('plan/plan-discount')->group(function () {
	    Route::get('/', [PlanUserDiscountController::class, 'index'])->name('plan_discount.index');
	    Route::post('/store', [PlanUserDiscountController::class, 'store'])->name('plan_discount.store');
	    Route::get('/edit/{id}', [PlanUserDiscountController::class, 'edit'])->name('plan_discount.edit');
	    Route::delete('/delete/{id}', [PlanUserDiscountController::class, 'destroy'])->name('plan_discount.delete');
	});

	// new bounce 
	Route::resource('bonus-package', BonusPackageController::class)->middleware(IsAdmin::class);
	Route::resource('offer-package', OfferPackageController::class)->middleware(IsAdmin::class);
	Route::resource('offer-page', OfferPageController::class)->middleware(IsAdmin::class);
	Route::get('/get-durations/{plan_id}', [App\Http\Controllers\OfferPackageController::class, 'getDurations'])->name('offer-package.getDurations');

	Route::resource('planMetaFeatures', PlanMetaDetailsController::class)->middleware(IsAdmin::class);
	Route::resource('frame_categories', FrameCategoryController::class);
	Route::resource('frame_items', FrameItemController::class);
	Route::post('updateFrameItem', [FrameItemController::class, 'updateFrameItemPremium'])->name('frameItem.premium');

	// new_search_tags
	Route::resource('new_search_tags', NewSearchTagController::class);
	Route::post('new_search_tags/submit', [NewSearchTagController::class, 'storeOrUpdate'])->name('new_search_tags.submit');

	/* User Subscription Mannual Setting  */
	Route::get('show_manage_subscription/{userId?}', [UserManageSubscriptionController::class, 'showManageSubscription'])->name('manage_subscription.show')->middleware(IsAdmin::class);
	Route::post('show_manage_subscription_submit', [UserManageSubscriptionController::class, 'saveManageSubscription'])->name('manage_subscription.submit');
	Route::post('show_manage_subscription_update', [UserManageSubscriptionController::class, 'updateManageSubscription'])->name('manage_subscription.update')->middleware(IsAdmin::class);
	Route::post('manage_subscription_delete/{sub_package_id?}', [UserManageSubscriptionController::class, 'deleteManageSubscription'])->name('manage_subscription.delete')->middleware(IsAdmin::class);

	Route::get('manage_template_product/{userId?}', [UserManageTemplateProductController::class, 'manageTemplateProductShow'])->name('manage_template_product.show')->middleware(IsAdmin::class);
	Route::post('manage_template_product_submit', [UserManageTemplateProductController::class, 'saveTemplateProduct'])->name('manage_template_product.submit')->middleware(IsAdmin::class);
	Route::post('manage_template_product_update', [UserManageTemplateProductController::class, 'updateTemplateProduct'])->name('manage_template_product.update')->middleware(IsAdmin::class);
	Route::post('manage_template_product_delete/{template_product_id?}', [UserManageTemplateProductController::class, 'deleteTemplateProduct'])->name('manage_template_product.delete')->middleware(IsAdmin::class);

	Route::get('manage_video_product/{userId?}', [UserManageVideoProductController::class, 'manageVideoProductShow'])->name('manage_video_product.show')->middleware(IsAdmin::class);
	Route::post('manage_video_product_submit', [UserManageVideoProductController::class, 'saveVideoProduct'])->name('manage_video_product.submit')->middleware(IsAdmin::class);
	Route::post('manage_video_product_update', [UserManageVideoProductController::class, 'updateVideoProduct'])->name('manage_video_product.update')->middleware(IsAdmin::class);
	Route::post('manage_video_product_delete/{template_product_id?}', [UserManageVideoProductController::class, 'deleteVideoProduct'])->name('manage_video_product.delete')->middleware(IsAdmin::class);
	Route::get('/users_export', 'App\Http\Controllers\UserController@export')->name('users.export')->middleware(IsAdmin::class);
	Route::get('/active_subscription_export', 'App\Http\Controllers\UserController@exportActiveSubscribers')->name('active_subscription.export')->middleware(IsAdmin::class);
	Route::get('/expired_subscription_export', 'App\Http\Controllers\UserController@exportExpiredSubscribers')->name('expired_subscription.export')->middleware(IsAdmin::class);

	Route::get('template_transcation_logs', 'App\Http\Controllers\SubscriptionController@showTemplateTranscation')->name('template_transcation_logs')->middleware(IsAdmin::class);
	Route::get('video_transcation_logs', 'App\Http\Controllers\SubscriptionController@showVideoTranscation')->name('video_transcation_logs')->middleware(IsAdmin::class);

	Route::resource('reviews', ReviewsController::class);
	Route::post('review_status', [ReviewsController::class, 'reviewStatus'])->name('review.status');
	Route::resource('p_reviews', PReviewController::class);
	Route::post('/p-reviews/status', [PReviewController::class, 'reviewStatus'])->name('p_reviews.reviewStatus');
	Route::get('/p-reviews/page-data', [PReviewController::class, 'getSelectedPageData'])->name('get_selected_page_data');
	Route::get('/p-review/page-title', [PReviewController::class, 'getSelectedPageTitle'])->name('get_selected_page_title');

	// Route::get('/export-users', [UsersExport::class, 'sheets'])->middleware(IsAdmin::class);
	Route::get('refreshTanscation', [App\Http\Controllers\HomeController::class, 'refreshTanscation'])->name('refreshTanscation')->middleware(IsAdmin::class);
	Route::any('export-users', [CustomDataExporter::class, 'getUsers'])->name('export-users')->middleware(IsAdmin::class);
	Route::any('export-datas', [CustomDataExporter::class, 'getDatas'])->name('export-datas')->middleware(IsAdmin::class);
	Route::any('export-sub-datas', [CustomDataExporter::class, 'getSubDatas'])->name('export-sub-datas')->middleware(IsAdmin::class);
	Route::any('export-meta-sub-datas', [CustomDataExporter::class, 'getMetaSubDatas'])->name('export-meta-sub-datas')->middleware(IsAdmin::class);

	Route::get('/export-users', function () {
	    return Excel::download(new UsersExport, 'users.xlsx');
	})->middleware(IsAdmin::class);

	Route::post('getNewSearchTag', [TemplateController::class, "getNewSearchTag"])->name('getNewSearchTag');
	Route::get('editIntrest/{id}', [InterestController::class, 'editIntrest'])->name('interest.edit');
	Route::get('themeEdit/{id}', [ThemeController::class, 'themeEdit'])->name('theme.edit');
	Route::post('getSizeList', [SizeController::class, 'getSizeList'])->name('getSizeList');
	Route::post('getThemeList', [ThemeController::class, 'getThemeList'])->name('getThemeList');
	Route::post('getInterestList', [InterestController::class, 'getInterestList'])->name('getInterestList');

	Route::resource('offer-popup', OfferPopUpController::class)->middleware(IsAdmin::class);
	Route::post('/offer-popup/{id}/set-enable', [OfferPopUpController::class, 'setEnableOffer'])->name('offer-popup.set-enable');
	Route::post('offer-popup/{id}/set-enable-promo', [OfferPopupController::class, 'setEnablePromo'])->name('offer-popup.set-enable-promo');

	Route::get('panel_histroy', [PanelHistroyController::class, 'index'])->name('panel_histroy');

	Route::get('/get-storage-link', function (Request $request) {
	    $src = $request->query('src');
	    return response()->json(['url' => ContentManager::getStorageLink($src)]);
	})->middleware('auth');
	
	// Route::get('get-options/{table}/{idColumn}/{nameColumn}', function ($table, $idColumn, $nameColumn) { return DB::table($table)->select($idColumn, $nameColumn)->get(); })->middleware('auth');
	Route::get('get-options/{table}/{idColumn}/{nameColumn}/{database?}', function ($table, $idColumn, $nameColumn, $database = null) {
		$connection = $database ? DB::connection($database) : DB::connection();
		return $connection->table($table)->select($idColumn, $nameColumn)->get();
	})->middleware('auth');

	Route::get('/get-dependent-value/{table}/{dependentColumn}/{dependentColumnId}/{id}/{database?}', function ($table, $dependentColumn, $dependentColumnId, $id, $database = null) {
		$connection = $database ? DB::connection($database) : DB::connection();
		$value = $connection->table($table)
			->where($dependentColumnId, $id)
			->value($dependentColumn);
		// return response()->json(['data' => $value ?? ""]);
		return response()->json($value ?? "");
	})->middleware('auth');

	Route::get('get-unique-options/{table}/{column}', function ($table, $column) {
	    $results = DB::table($table)->select($column)->distinct()->get();

	    $options = [];
	    foreach ($results as $result) {
	        // Remove brackets and double quotes, then split by comma
	        $cleaned = str_replace(['[', ']', '"'], '', $result->$column);
	        $tags = explode(',', $cleaned);
	        foreach ($tags as $tag) {
	            $trimmedTag = trim($tag);
	            if ($trimmedTag !== '') { // Ensure empty values are skipped, but not '0'
	                $options[] = ['value' => $trimmedTag, 'text' => $trimmedTag];
	            }
	        }
	    }

	    return response()->json($options);
	})->middleware('auth');
	
	Route::get('seo_error_list', [SeoErrorListController::class, 'index'])->name('seo_error_list');

	Route::middleware(IsAdmin::class)->group(function (){

	    Route::get('get_email_tmp', [EmailTemplateController::class, 'getEmailTmp'])->name('get_email_tmp')->middleware(IsAdmin::class);
	    Route::resource('email_template', EmailTemplateController::class)->middleware(IsAdmin::class);
	    Route::post('/email-template/store/{id?}', [EmailTemplateController::class, 'storeTemplate'])->name('email_template.storeTemplate');
	    Route::get('/email-template/{id}/edit', [EmailTemplateController::class, 'editTemplate'])->name('email_template.editTemplate');
	    Route::delete('/email-template/{id}/delete', [EmailTemplateController::class, 'deleteTemplate'])->name('email_template.deleteTemplate');
	    Route::get('email-template/preview/{id}', [EmailTemplateController::class, 'preview'])
	        ->name('email_template.preview');
	    Route::get('create_email_template', [EmailTemplateController::class, 'createEmailTemplate'])->name('create_email_template');


	    Route::get('whatsapp_template', [WhatsAppTemplateController::class, 'index'])->name('whatsapp_template.index');
	    Route::post('whatsapp_template', [WhatsAppTemplateController::class, 'storeTemplate'])->name('whatsapp_template.store');
	    Route::get('whatsapp_template/{id}/edit', [WhatsAppTemplateController::class, 'edit'])->name('whatsapp_template.edit');
	    Route::post('whatsapp_template/{id}', [WhatsAppTemplateController::class, 'update'])->name('whatsapp_template.update');
	    Route::delete('whatsapp_template/{id}', [WhatsAppTemplateController::class, 'destroy'])->name('whatsapp_template.destroy');


	    Route::get('campaign', [CampaignController::class, 'index'])->name('campaign.index');
	    Route::get('campaign/report', [CampaignController::class, 'report'])->name('campaign.report');
	    Route::get('campaign/failed-logs/{log_id}', [CampaignController::class, 'failedLogs'])->name('campaign.failed_logs');
	    Route::post('combined-campaign-start', [CampaignController::class, 'startCampaign'])->name('start_combined_campaign');
	    Route::post('/campaign/{id}/stop', [CampaignController::class, 'stop'])->name('campaign.stop');
	    Route::post('/campaign/{id}/pause', [CampaignController::class, 'pause'])->name('campaign.pause');
	    Route::post('/campaign/{id}/resume', [CampaignController::class, 'resume'])->name('campaign.resume');
	    Route::post('/campaign/{log_id}/resend-failed-all', [CampaignController::class, 'resendFailedAll'])->name('campaign.resend_failed_all');
	    Route::post('/campaign/resend-single', [CampaignController::class, 'resendSingleFailed'])->name('campaign.resend_single');
	    Route::post('/campaign/{id}/toggle-auto-resume', [CampaignController::class, 'toggleAutoResume'])
	        ->name('campaign.toggle_auto_resume');
	    // Route::post('campaign/resend-failed-all/{log_id}', [CampaignController::class, 'triggerResendCampaignJob'])
	    //     ->name('campaign.resend_failed_all');
	    Route::post('campaign/resend-failed-email/{log_id}', [CampaignController::class, 'triggerResendEmailJob'])
	        ->name('campaign.resend_failed_email');
	    Route::post('campaign/resend-failed-whatsapp/{log_id}', [CampaignController::class, 'triggerResendWhatsAppJob'])
	        ->name('campaign.resend_failed_whatsapp');

	    Route::resource('automation_report', AutomationReportController::class);
		Route::get('/automation_report/failed-logs/{log_id}', [AutomationReportController::class, 'failedLogs'])->name('automation_report.failed_logs');
		Route::resource('automation_config', AutomationConfigController::class);
	});

});

Route::middleware(IsAdmin::class)->prefix('payment_configuration')->group(function () {
    Route::get('/', [PaymentConfigController::class, 'index'])->name('payment_configuration.index');
    Route::post('/store', [PaymentConfigController::class, 'store'])->name('payment.config.store');
    Route::post('/add-gateway', [PaymentConfigController::class, 'addNewGateway'])->name('payment.config.add-gateway');
    Route::get('/{id}/get', [PaymentConfigController::class, 'getGateway'])->name('payment.config.get');
    Route::post('/{id}/update', [PaymentConfigController::class, 'updateGateway'])->name('payment.config.update');
    Route::post('/{id}/activate', [PaymentConfigController::class, 'activate'])->name('payment.config.activate');
    Route::delete('/{id}', [PaymentConfigController::class, 'destroy'])->name('payment.config.destroy');
});

Route::middleware(['auth', IsAdminOrHr::class])->group(function () {
    // Career page
    Route::get('career-page/edit', [CareerPageController::class, 'edit'])->name('career_page.edit');
    Route::post('career-page/update', [CareerPageController::class, 'update'])->name('career_page.update');

    // Job openings
    Route::resource('job_openings', JobOpeningController::class);

    // Job applications
    Route::get('job_applications', [JobApplicationController::class, 'index'])->name('job_applications.index');
    Route::get('job_applications/download/{job_application}', [JobApplicationController::class, 'downloadResume'])->name('job_applications.download');
    Route::get('job_applications/{job_application}', [JobApplicationController::class, 'show'])->name('job_applications.show');
    Route::delete('job_applications/{job_application}', [JobApplicationController::class, 'destroy'])->name('job_applications.destroy');
});