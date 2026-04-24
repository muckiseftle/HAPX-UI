<?php

use App\Http\Controllers\ProxyHostController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    
    Route::get('login/2fa', [AuthenticatedSessionController::class, 'showTwoFactor'])->name('login.2fa');
    Route::post('login/2fa', [AuthenticatedSessionController::class, 'verifyTwoFactor']);
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return redirect()->route('proxies.index');
    });

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Profil & Passwort
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/2fa/enable', [ProfileController::class, 'enable2fa'])->name('profile.2fa.enable');
    Route::post('/profile/2fa/disable', [ProfileController::class, 'disable2fa'])->name('profile.2fa.disable');

    Route::resource('proxies', ProxyHostController::class);

    // Zertifikatsverwaltung
    Route::prefix('certificates')->name('certificates.')->group(function () {
        Route::get('/', [CertificateController::class, 'index'])->name('index');
        Route::post('/', [CertificateController::class, 'store'])->name('store');
        Route::post('/letsencrypt', [CertificateController::class, 'storeLetsEncrypt'])->name('letsencrypt.store');
        Route::post('/{certificate}/renew', [CertificateController::class, 'renew'])->name('renew');
        Route::get('/sync', [CertificateController::class, 'sync'])->name('sync');
        Route::delete('/', [CertificateController::class, 'destroy'])->name('destroy');
    });

    // Monitoring
    Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
    Route::get('/api/monitoring/live', [MonitoringController::class, 'liveData'])->name('monitoring.live');
});
