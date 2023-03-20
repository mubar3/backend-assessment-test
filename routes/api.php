<?php

use App\Http\Controllers\DebitCardController;
use App\Http\Controllers\DebitCardTransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LoanController;
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
Route::get('reg_user/{id}', [UserController::class, 'reg_user']);
Route::post('login', [UserController::class, 'login']);

Route::middleware('auth:api')
    ->group(function () {
        // Debit card endpoints
        Route::get('debit-cards', [DebitCardController::class, 'index']);
        Route::post('debit-cards', [DebitCardController::class, 'store']);
        Route::get('debit-cards/{debitCard}', [DebitCardController::class, 'show']);
        Route::put('debit-cards/{debitCard}', [DebitCardController::class, 'update']);
        Route::delete('debit-cards/{debitCard}', [DebitCardController::class, 'destroy']);

        // Debit card transactions endpoints
        Route::put('debit-card-transactions', [DebitCardTransactionController::class, 'index']);
        Route::post('debit-card-transactions', [DebitCardTransactionController::class, 'store']);
        Route::get('debit-card-transactions/{debitCardTransaction}', [DebitCardTransactionController::class, 'show']);
        
        Route::get('debit-loan-transactions', [LoanController::class, 'index']);
        Route::post('debit-loan-transactions', [LoanController::class, 'store']);
        Route::post('pay-loan-transactions', [LoanController::class, 'pay']);

        Route::get('/logout', [UserController::class, 'logout']);
    });
