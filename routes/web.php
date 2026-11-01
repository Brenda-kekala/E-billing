<?php

use App\Http\Controllers\IncomeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\MpesaController;
use App\Http\Controllers\SelfAssessmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
 Web Routes

 Here is where you can register web routes for your application. These
 routes are loaded by the RouteServiceProvider and all of them will
 be assigned to the "web" middleware group.

*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'active'])->name('dashboard');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/tax', [TaxController::class, 'index'])->name('tax.index');
    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
    // Inputs in record a payment are sent to /payments go to store() in paymentController
    Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy'); 
    Route::get('/self-assessment', [SelfAssessmentController::class, 'index'])->name('sa.index');
    Route::post('/expenses', [SelfAssessmentController::class, 'storeExpense'])->name('expenses.store');
    Route::delete('/expenses/{expense}', [SelfAssessmentController::class, 'destroyExpense'])->name('expenses.destroy');
    Route::post('/mpesa/pay', [MpesaController::class, 'pay'])->name('mpesa.pay');
    Route::get('/mpesa/status/{payment}', [MpesaController::class, 'status'])->name('mpesa.status');
    Route::post('/regime', [DashboardController::class, 'updateRegime'])->name('regime.update');
    // Says slip sent to /regime go to updateRegime method in Dashboard Controller 
});

Route::middleware(['auth', 'admin', 'active'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('user.edit');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('user.update');
    Route::get('/export', [AdminController::class, 'exportCsv'])->name('export');
    Route::get('/logs', [AdminController::class, 'logs'])->name('logs');
    Route::post('/users/{user}/toggle-active', [AdminController::class, 'toggleActive'])->name('user.toggle-active');
    Route::get('/export-pdf', [AdminController::class, 'exportPdf'])->name('export.pdf');
    Route::get('/users/{user}', [AdminController::class, 'show'])->name('user');
    Route::post('/incomes/{income}/flag', [AdminController::class, 'toggleFlag'])->name('incomes.flag');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/incomes', [IncomeController::class, 'index'])->name('incomes.index');
    Route::post('/incomes', [IncomeController::class, 'store'])->name('incomes.store');
    Route::delete('/incomes/{income}', [IncomeController::class, 'destroy'])->name('incomes.destroy');
});

require __DIR__.'/auth.php';
