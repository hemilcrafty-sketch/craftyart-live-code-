<?php

use App\Http\Controllers\AI\AiGenerator;
use App\Http\Controllers\AI\AIJobController;
use App\Http\Controllers\AI\BGRemover;
use App\Http\Controllers\AI\CreditHistoryController;
use App\Http\Controllers\Auth\BroadcastAuthController;
use App\Http\Controllers\Auth\NewAuthController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Caricature\AICreditController;
use App\Http\Controllers\Caricature\CaricatureController;
use App\Http\Controllers\Caricature\CaricatureGenerator;
use App\Http\Controllers\ContactUsController;
use App\Http\Controllers\DashboardApiController;
use App\Http\Controllers\DesignerDraftController;
use App\Http\Controllers\FavouriteApiController;
use App\Http\Controllers\KPageApiController;
use App\Http\Controllers\LottieApiController;
use App\Http\Controllers\OfferPopUpController;
use App\Http\Controllers\OfferRegistrationController;
use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\Payment\PhonePeController;
use App\Http\Controllers\Payment\PhonePeWebhookController;
use App\Http\Controllers\Payment\RazorpayWebhookController;
use App\Http\Controllers\Payment\StripeWebhookController;
use App\Http\Controllers\Payment\TestRazorpayWebhookController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\Lottie\LottieSitemapController;
use App\Http\Controllers\StockImagesController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\RawDataController;
use App\Http\Controllers\FontController;
use App\Http\Controllers\DraftController;
use App\Http\Controllers\BgController;
use App\Http\Controllers\StickerController;
use App\Http\Controllers\FrameApiController;
use App\Http\Controllers\SearchApiController;
use App\Http\Controllers\CategoryTemplatesApiController;
use App\Http\Controllers\TemplateApiController;
use App\Http\Controllers\PageApiController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\UpdaterController;
use App\Http\Controllers\FilterController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\MobileVerificationController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\PReviewController;
use App\Http\Controllers\VirtualDataController;
use App\Http\Controllers\XMLController;
use App\Http\Controllers\NoIndexController;
use App\Http\Controllers\PageSlugHistoryController;
use App\Http\Controllers\Revenue\RevenueController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('revenue/login', [RevenueController::class, 'login']);
Route::post('revenue/', [RevenueController::class, 'index']);
Route::post('revenue/logs', [RevenueController::class, 'logs']);
Route::post('revenue/analytics', [RevenueController::class, 'analytics']);
Route::post('revenue/e_mandates', [RevenueController::class, 'e_mandates']);
Route::post('revenue/new_subs', [RevenueController::class, 'new_subs']);
Route::post('revenue/top_users', [RevenueController::class, 'top_users']);

Route::any('stock/images', [StockImagesController::class, 'getImages']);

Route::any('slugs', [PageSlugHistoryController::class, 'getData']);
Route::any('ip', [SubscriptionController::class, 'getIp']);
Route::any('filters', [FilterController::class, 'getFilters']);
Route::any('sizes', [FilterController::class, 'getSizes']);

Route::any('tmp', [DashboardApiController::class, 'getTemplates']);
Route::any('tmp/fetch', [TemplateApiController::class, 'getAllFab']);
Route::any('tmp/get', [TemplateApiController::class, 'getPosterDetail']);
Route::any('tmp/page', [TemplateApiController::class, 'getPosterPage']);
Route::any('tmp/search', [SearchApiController::class, 'searchReferTemplates']);
Route::any('tmp/fvrt/create', [FavouriteApiController::class, 'favourite']);
Route::any('tmp/fvrt/delete', [FavouriteApiController::class, 'favourite']);
Route::any('tmp/fvrt', [FavouriteApiController::class, 'getAllFavourite']);
Route::any('page', [PageApiController::class, 'getPage']);
Route::any('tmp/s', [PageApiController::class, 'getSpecialTemplates']);
Route::any('tmp/k', [KPageApiController::class, 'getKeyTemplates']);

Route::any('catlist', [CategoryTemplatesApiController::class, 'getDashboardDatas']);
Route::any('cat', [CategoryTemplatesApiController::class, 'getCategories']);

Route::any('video/categories', [LottieApiController::class, 'getCategories']);
Route::any('video/category', [LottieApiController::class, 'getCategory']);
Route::any('video/template', [LottieApiController::class, 'getTemplate']);

Route::any('caricatures', [CaricatureController::class, 'getCategories']);
Route::any('caricature', [CaricatureController::class, 'getCategory']);
Route::any('caricature/attire', [CaricatureController::class, 'getAttire']);
Route::any('caricature/generate', [CaricatureGenerator::class, 'generate']);
Route::any('user/caricatures', [CaricatureGenerator::class, 'getCaricatures']);

Route::any('fonts/get', [FontController::class, 'getFonts']);
Route::any('fonts/check', [FontController::class, 'checkUnicode']);

Route::any('element/bg/get', [BgController::class, 'getBgs']);
Route::any('element/bg/cat', [BgController::class, 'getCategoryBgs']);

Route::any('element/stk/get', [StickerController::class, 'getStickers']);
Route::any('element/stk/cat', [StickerController::class, 'getCategoryStickers']);

Route::any('element/fr/get', [FrameApiController::class, 'getFrameData']);
Route::any('element/fr/cat', [FrameApiController::class, 'getCatFrameData']);

Route::any('element/search', [SearchApiController::class, 'searchElements']);

Route::any('export/add', [ExportController::class, 'add']);
Route::any('export/update', [ExportController::class, 'update']);
Route::any('export/get', [ExportController::class, 'get']);

Route::any('updater/get', [UpdaterController::class, 'get']);
Route::any('updater/set', [UpdaterController::class, 'update']);

//start of payments
Route::any('cancel-subscription', [PaymentController::class, 'cancelSubscription']);
Route::any('payment/tr', [PaymentController::class, 'getTempRates']);
Route::any('payment/pc', [PaymentController::class, 'checkPromoCode']);
Route::any('payment/order', [PaymentController::class, 'getOrder']);
Route::any('payment/phonepe', [PhonePeController::class, 'createOrder']);
Route::any('payment/razorpay', [PaymentController::class, 'createRazorPayIntent']);
Route::any('payment/list', [PaymentController::class, 'listMethods']);
Route::any('payment/update', [PaymentController::class, 'updatePm']);
Route::any('payment/detach', [PaymentController::class, 'detachPm']);
Route::any('payment/stripe', [PaymentController::class, 'createStripeIntent']);
Route::any('payment/webhook', [PaymentController::class, 'webhook']);
Route::any('payment/verifyPayId', [PaymentController::class, 'verifyStripeId']);
Route::any('payment/refreshTransaction', [PaymentController::class, 'refreshTransaction']);
Route::any('payment/refreshTransaction/{id}', [PaymentController::class, 'refreshTransaction']);
Route::any('payment/razorpay/webhook', [RazorpayWebhookController::class, 'handleWebhook']);
Route::any('test/payment/razorpay/webhook', [TestRazorpayWebhookController::class, 'handleWebhook']);
Route::any('payment/stripe/webhook', [StripeWebhookController::class, 'handleWebhook']);
Route::any('payment/phonepe/webhook', [PhonePeWebhookController::class, 'handleWebhook']);
//end of payments

//start of ai credit
Route::any('ai_credits', [AICreditController::class, 'getAiCreditsPlan']);
Route::any('cari_rate', [AICreditController::class, 'getRates']);
Route::any('credit_history', [CreditHistoryController::class, 'history']);
Route::any('job_status', [AIJobController::class, 'jobStatus']);
Route::any('ai_created_history', [CreditHistoryController::class, 'aiCreatedHistory']);
//end of ai credit

//start of ai_generator
Route::any('ai_generate', [AiGenerator::class, 'generate']);
Route::any('removebg', [BGRemover::class, 'remove']);
//end of ai_generator

//start of users
Route::any('user', [UserController::class, 'createUser']);
Route::any('user/update', [UserController::class, 'updateUser']);
Route::any('user/delete', [UserController::class, 'deleteUser']);
Route::any('purchases', [SubscriptionController::class, 'getPurchases']);
Route::any('plans', [SubscriptionController::class, 'getSubs']);
Route::any('plans/v2', [PlanController::class, 'getPlanData']);
Route::any('offer_package', [PlanController::class, 'getOfferPackage']);
Route::any('check_offer', [PlanController::class, 'checkOffer']);
Route::any('plans/additional_user', [PlanController::class, 'getAdditionalUserPlan']);
Route::any('plans/check_limit', [PlanController::class, 'checkLimit']);
Route::any('change_email_subscribe', [UserController::class, 'changeEmailSubscribe']);
Route::any('email_subscribe_status', [UserController::class, 'getSubscribeStatus']);
Route::any('portfolio', [UserController::class, 'getPortfolio']);

Route::any('login', [AuthController::class, 'login']);
Route::any('signup', [AuthController::class, 'signup']);
Route::any('user/get', [AuthController::class, 'getUser']);
Route::any('reset-pass', [AuthController::class, 'resetPassword']);

Route::post('auth/login', [NewAuthController::class, 'login']);
Route::post('auth/signup', [NewAuthController::class, 'signup']);
Route::post('auth/reset-password', [NewAuthController::class, 'resetPassword']);
Route::post('auth/logout', [NewAuthController::class, 'logout']);
Route::post('auth/user', [NewAuthController::class, 'getUser']);
Route::post('auth/google', [NewAuthController::class, 'handleGoogleSignIn']);
//end of users

//start of otp
Route::any('otp', [VerificationController::class, 'sendVerificationOTP']);
Route::any('otp/verify', [VerificationController::class, 'verifyOTP']);

Route::any('mobile/otp', [MobileVerificationController::class, 'sendVerificationOTP']);
Route::any('mobile/otp/verify', [MobileVerificationController::class, 'verifyOTP']);
//end of otp

//start of uploads
Route::any('upload', [UploadController::class, 'uploadDatas']);
Route::any('uploads', [UploadController::class, 'getUploads']);
Route::any('upload/trash', [UploadController::class, 'modifiedUpload']);
Route::any('upload/rename', [UploadController::class, 'renameUpload']);
//end of uploads

//start of uploads
Route::any('d/upload', [RawDataController::class, 'uploadDatas']);
Route::any('d/uploads', [RawDataController::class, 'getUploads']);
Route::any('d/upload/trash', [RawDataController::class, 'modifiedUpload']);
Route::any('d/upload/rename', [RawDataController::class, 'renameUpload']);
Route::any('d/fvrt', [RawDataController::class, 'favourite']);
//end of uploads

//start of drafts
Route::any('design/data', [DraftController::class, 'getPosterDetail']);
Route::any('design/sdata', [DraftController::class, 'saveData']);
Route::any('drafts', [DraftController::class, 'getDrafts']);
Route::any('draft/trash', [DraftController::class, 'modifiedDraft']);
Route::any('draft/rename', [DraftController::class, 'renameDraft']);
Route::any('draft/copy', [DraftController::class, 'copyDraft']);
//end of drafts

//start of designer
Route::any('design/create', [DesignerDraftController::class, 'create']);
Route::any('design/all', [DesignerDraftController::class, 'getAll']);
Route::any('design/get', [DesignerDraftController::class, 'get']);
Route::any('design/save', [DesignerDraftController::class, 'save']);
Route::any('design/export', [DesignerDraftController::class, 'export']);
Route::any('design/reject', [DesignerDraftController::class, 'reject']);
Route::any('design/approve', [DesignerDraftController::class, 'approve']);
Route::any('creators', [DesignerDraftController::class, 'getCreators']);
Route::any('creator', [DesignerDraftController::class, 'findCreator']);
//end of designer

//start of review
Route::any('review/anl', [ReviewController::class, 'allAnalyticReviews']);
Route::any('reviews', [ReviewController::class, 'getReviews']);
Route::any('review/post', [ReviewController::class, 'postReview']);
Route::any('review/user', [ReviewController::class, 'getUserReview']);
Route::any('review/delete', [ReviewController::class, 'deleteReview']);
Route::any('review/edit', [ReviewController::class, 'editReview']);
//end of review

//start of p_review
Route::any('p_reviews', [PReviewController::class, 'getReviews']);
Route::any('p_review/post', [PReviewController::class, 'postReview']);
Route::any('p_review/delete', [PReviewController::class, 'deleteReview']);
Route::any('p_review/edit', [PReviewController::class, 'editReview']);
//end of p_review

//start of OfferPopUp
Route::any('offer_popup', [OfferPopUpController::class, 'getOfferPopUp']);
//end of OfferPopUp

Route::any('catalog-of-google', [XMLController::class, 'catalog']);
Route::any('catalog-of-caricature-google', [XMLController::class, 'caricatures']);

Route::any('offer/add', [OfferRegistrationController::class, 'add']);
Route::any('offer/show', [OfferRegistrationController::class, 'show']);

Route::any('contact', [ContactUsController::class, 'contactUs']);
Route::any('feedback', [ContactUsController::class, 'sendFeedback']);

Route::any('check_n_i', [NoIndexController::class, 'checkNoindex']);

Route::post('virtual/get-options', [VirtualDataController::class, 'getOptions']);
Route::post('virtual/get-dependent-value', [VirtualDataController::class, 'getDependentValue']);
Route::post('virtual/get-unique-options', [VirtualDataController::class, 'getUniqueOptions']);
Route::post('virtual/get-data', [VirtualDataController::class, 'getVirtualData']);

Route::any('keywords', [SitemapController::class, 'keywords']);
Route::any('sitemap', [SitemapController::class, 'sitemap']);

Route::any('video_keywords', [LottieSitemapController::class, 'keywords']);
Route::any('video_sitemap', [LottieSitemapController::class, 'sitemap']);

Route::any('new-sitemap.xml', [SitemapController::class, 'sitemapIndex']);
Route::any('new-sitemap/others.xml', [SitemapController::class, 'otherSitemap']);
Route::any('new-sitemap/categoriesV1.xml', [SitemapController::class, 'categoriesSitemap']);
Route::any('new-sitemap/categoriesV2.xml', [SitemapController::class, 'newCategoriesSitemap']);
Route::any('new-sitemap/categoriesV2/{parent}.xml', [SitemapController::class, 'parentSitemap']);
Route::any('new-sitemap/categoriesV2/{parent}/{child}.xml', [SitemapController::class, 'childSitemap']);

//Route::any('/broadcasting/auth', function (Request $request) {
////    if (!$request->isMethod('post')) abort(404);
//    return Broadcast::auth(request());
//});
//

Broadcast::routes(['middleware' => ['auth:sanctum']]);

Route::post('/broadcasting/auth', function (Request $request) {
    try {
        return app(BroadcastAuthController::class)->authenticate($request);
    } catch (Exception) {
        return response()->json(['error' => 'Authentication failed'], 403);
    }
});
//
//Route::post('/broadcasting/debug', function () {
//    return response()->json([
//        'token_header' => request()->header('Authorization'),
//        'channel_name' => request()->channel_name,
//    ]);
//});
