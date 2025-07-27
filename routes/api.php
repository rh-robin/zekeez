<?php


use App\Http\Controllers\API\BankAccountApiController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\OtpController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\EntityApiController;
use App\Http\Controllers\API\FilterApiController;
use App\Http\Controllers\API\ReviewApiController;
use App\Http\Controllers\API\ContactApiController;
use App\Http\Controllers\API\ProductApiController;
use App\Http\Controllers\API\SocialLoginController;
use App\Http\Controllers\API\SubscriptionApiController;
use App\Http\Controllers\API\Property\LeaseApiController;


Route::post('/social-login', [SocialLoginController::class, 'SocialLogin']);
Route::post('/register', [AuthController::class, 'userStore']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/resend-otp', [OtpController::class, 'resendOtp']);
Route::post('/verify-otp', [OtpController::class, 'verifyOtp']);

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'getProfile']);
    Route::post('/refresh', [SocialLoginController::class, 'refresh']);

    Route::post('/profile/update', [SocialLoginController::class, 'updateProfile']);
    Route::post('/profile/image/update', [SocialLoginController::class, 'updateProfileImage']);

    /*============ Entity routes ==========*/
    Route::prefix('entity')
        ->controller(EntityApiController::class)
        ->group(function () {
            Route::get('/get-all', 'getAllEntities');
            Route::get('/get-all-accessible', 'getAccessibleEntities');
            Route::post('/store', 'storeEntity');
            Route::get('/edit/{id}', 'edit');
            Route::post('/update/{id}', 'update');
            Route::post('/status/{id}', 'status');
            Route::delete('/destroy/{id}', 'destroy');
        });

    /*============ Contact routes ==========*/
    Route::prefix('contact')
        ->controller(ContactApiController::class)
        ->group(function () {
            Route::post('/store', 'store');
            Route::get('/get-all', 'getAllContacts');
            Route::get('/individuals', 'getIndividualContacts');
            Route::get('/legal-entities', 'getLegalEntityContacts');
        });

    /*======= REVIEW =======*/
    Route::post('/product/review', [ReviewApiController::class, 'postProductReview']);
    Route::post('/shipping/review', [ReviewApiController::class, 'postShippingReview']);

    /*======= RATING =======*/
    Route::post('/product/rating', [ReviewApiController::class, 'postProductRating']);
    Route::post('/shipping/rating', [ReviewApiController::class, 'postShippingRating']);
});

Route::post('/send-otp', [OtpController::class, 'sendOtp']);
Route::post('/verify-otp', [OtpController::class, 'verifyOtp']);

/*=========== GET LISTED contact form ==============*/
Route::post('/contact/store', [ContactApiController::class, 'store']);
Route::post('/subscribe/store', [SubscriptionApiController::class, 'store']);

/*============ PRODUCT API ============*/
Route::get('/products', [ProductApiController::class, 'index']);
Route::get('/products/{id}', [ProductApiController::class, 'productDetails']);

/*============ PRODUCT FILTER API ============*/
Route::get('/calibers/{category_id?}', [FilterApiController::class, 'getCalibers']);
Route::get('/get-categories', [FilterApiController::class, 'getCategories']);
Route::get('/get-brands', [FilterApiController::class, 'getBrands']);
Route::get('/get-casings', [FilterApiController::class, 'getCasings']);
Route::get('/get-grains', [FilterApiController::class, 'getGrains']);
Route::get('/get-filtered-products', [FilterApiController::class, 'getFilteredProducts']);


Route::post('/link-account', [BankAccountApiController::class, 'linkAccount']);
Route::get('/transactions/{accountId}', [BankAccountApiController::class, 'getTransactions']);

require __DIR__.'/mamon.php';



