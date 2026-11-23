<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LeaveController;
use App\Http\Middleware\EnsureRole;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! Auth::check()) {
        return redirect()->route('login');
    }

    return in_array(data_get(Auth::user(), 'role'), ['guru', 'teacher'], true)
        ? redirect()->route('teacher.dashboard')
        : redirect()->route('student.dashboard');
});

Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/masuk', [AuthController::class, 'login'])->name('login.store');
    Route::get('/daftar', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/daftar', [AuthController::class, 'register'])->name('register.store');
    Route::get('/lupa-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/lupa-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});
Route::post('/keluar', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::get('/lampiran/{leaveRequest}', [LeaveController::class, 'evidence'])->middleware('auth')->name('leave.evidence');

Route::prefix('siswa')->name('student.')->middleware(['auth', EnsureRole::class . ':siswa'])->group(function () {
    Route::get('/', [LeaveController::class, 'studentDashboard'])->name('dashboard');
    Route::get('/pengajuan', [LeaveController::class, 'indexStudent'])->name('requests.index');
    Route::get('/pengajuan/buat', [LeaveController::class, 'create'])->name('requests.create');
    Route::post('/pengajuan', [LeaveController::class, 'store'])->name('requests.store');
    Route::get('/pengajuan/{leaveRequest}', [LeaveController::class, 'showStudent'])->name('requests.show');
    Route::get('/pengajuan/{leaveRequest}/ubah', [LeaveController::class, 'edit'])->name('requests.edit');
    Route::put('/pengajuan/{leaveRequest}', [LeaveController::class, 'update'])->name('requests.update');
    Route::delete('/pengajuan/{leaveRequest}', [LeaveController::class, 'destroy'])->name('requests.destroy');
});

Route::prefix('guru')->name('teacher.')->middleware(['auth', EnsureRole::class . ':guru'])->group(function () {
    Route::get('/', [LeaveController::class, 'teacherDashboard'])->name('dashboard');
    Route::get('/verifikasi', [LeaveController::class, 'verification'])->name('verification');
    Route::get('/verifikasi/{leaveRequest}/riwayat', [LeaveController::class, 'statusHistory'])->name('history');
    Route::post('/verifikasi/{leaveRequest}', [LeaveController::class, 'decide'])->name('decide');
    Route::get('/keluar-masuk', [LeaveController::class, 'movements'])->name('movements');
    Route::post('/keluar-masuk/{leaveRequest}/keluar', [LeaveController::class, 'recordExit'])->name('exit');
    Route::post('/keluar-masuk/{leaveRequest}/kembali', [LeaveController::class, 'recordReturn'])->name('return');
    Route::get('/notifikasi', [LeaveController::class, 'notifications'])->name('notifications');
    Route::post('/notifikasi/{notificationLog}/kirim-ulang', [LeaveController::class, 'retryNotification'])->name('notifications.retry');
    Route::get('/laporan/pdf', [LeaveController::class, 'reportPdf'])->name('report.pdf');
    Route::get('/laporan', [LeaveController::class, 'report'])->name('report');
});
