<?php

use App\Http\Controllers\Student\Auth\EmailVerificationController;
use App\Http\Controllers\Student\Auth\GradeSelectionController;
use App\Http\Controllers\Student\Auth\LoginController;
use App\Http\Controllers\Student\Auth\PasswordResetController;
use App\Http\Controllers\Student\Auth\RegisterController;
use App\Http\Controllers\Student\CurriculumController;
use App\Http\Controllers\Student\LegacyContentController;
use App\Http\Controllers\Student\PracticeController;
use App\Http\Controllers\Student\ProgressController;
use App\Http\Middleware\UseStudentLocale;
use Illuminate\Support\Facades\Route;

Route::middleware(UseStudentLocale::class)->group(function (): void {
    Route::view('/', 'student.home')->name('home');

    Route::controller(CurriculumController::class)->prefix('mufredat')->name('curriculum.')->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::get('{program}/{grade}', 'grade')->whereNumber('grade')->name('grade');
        Route::get('{program}/{grade}/{unit}/{topic}', 'topic')->whereNumber('grade')->name('topic');
    });

    // Registration and login (design § 08, Sprint 2.3).
    Route::middleware('guest')->group(function (): void {
        Route::get('kayit', [RegisterController::class, 'create'])->name('register');
        Route::post('kayit', [RegisterController::class, 'store'])->middleware('throttle:10,1');

        Route::get('giris', [LoginController::class, 'create'])->name('login');
        Route::post('giris', [LoginController::class, 'store']);

        Route::controller(PasswordResetController::class)->name('password.')->group(function (): void {
            Route::get('sifremi-unuttum', 'request')->name('request');
            Route::post('sifremi-unuttum', 'email')->middleware('throttle:6,1')->name('email');
            Route::get('sifre-sifirla/{token}', 'edit')->name('reset');
            Route::post('sifre-sifirla', 'update')->name('update');
        });
    });

    Route::middleware('auth')->group(function (): void {
        Route::get('kayit/sinif', [GradeSelectionController::class, 'edit'])->name('register.grade');
        Route::post('kayit/sinif', [GradeSelectionController::class, 'update']);

        Route::get('e-posta-dogrula/{id}/{hash}', [EmailVerificationController::class, 'verify'])
            ->middleware(['signed', 'throttle:6,1'])
            ->name('verification.verify');
        Route::post('e-posta-dogrula/yeniden', [EmailVerificationController::class, 'send'])
            ->middleware('throttle:6,1')
            ->name('verification.send');

        Route::post('cikis', [LoginController::class, 'destroy'])->name('logout');

        // Progress panel (design § 06, Sprint 3.3).
        Route::get('ilerleme', [ProgressController::class, 'index'])->name('progress.index');

        // Topic practice (design § 05, § 09, Sprint 3.2).
        Route::controller(PracticeController::class)->prefix('calis')->name('practice.')->group(function (): void {
            Route::get('baslat/{topic}', 'intent')->whereNumber('topic')->name('intent');
            Route::post('/', 'store')->middleware('throttle:30,1')->name('store');
            Route::get('{session}/ozet', 'summary')->whereNumber('session')->name('summary');
            Route::post('{session}/bitir', 'finish')->whereNumber('session')->name('finish');
            Route::get('{session}/{number?}', 'show')->whereNumber(['session', 'number'])->name('show');
            Route::post('{session}/{number}/cevap', 'answer')->whereNumber(['session', 'number'])->name('answer');
            Route::post('{session}/{number}/isaretle', 'flag')->whereNumber(['session', 'number'])->name('flag');
        });
    });

    Route::view('kullanim-kosullari', 'student.legal.terms')->name('legal.terms');
    Route::view('kvkk-aydinlatma-metni', 'student.legal.kvkk')->name('legal.kvkk');

    // kimyaogren.com WordPress URLs: /{slug}/, /category/…, /tag/… (ADR-004).
    Route::fallback(LegacyContentController::class);
});
