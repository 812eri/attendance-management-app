<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampCorrectionRequestController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\StaffController as AdminStaffController;
use App\Http\Controllers\Admin\StampCorrectionRequestController as AdminStampCorrectionRequestController;

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

// --- 一般ユーザー用ルーティング ---

// ※ログイン(PG02)・会員登録(PG01)はFortifyが自動でルートを提供しますが、
// 必要に応じてカスタマイズするため、Fortify設定後に確認します。

// ▼ 勤怠関連
Route::group(['prefix' => 'attendance'], function () {
    // PG03 勤怠登録画面（打刻画面）
    Route::get('/', [AttendanceController::class, 'index'])->name('attendance.index');

    // PG04 勤怠一覧画面
    Route::get('/list', [AttendanceController::class, 'list'])->name('attendance.list');

    // PG05 勤怠詳細画面
    Route::get('/detail/{id}', [AttendanceController::class, 'show'])->name('attendance.show');
});


// ▼ 修正申請関連（一般・管理者 共通URL）
// ※画面定義の要件により、PG06(一般)とPG12(管理者)は同じURLを使用します。
// コントローラー内部で権限判定を行い、表示内容を分岐させます。
Route::group(['prefix' => 'stamp_correction_request'], function () {

    // PG06 & PG12 申請一覧画面
    Route::get('/list', [StampCorrectionRequestController::class, 'index'])->name('stamp_correction_request.index');

    // 申請送信処理（一般ユーザー用アクション）
    Route::post('/create', [StampCorrectionRequestController::class, 'store'])->name('stamp_correction_request.store');
});


// --- 管理者用ルーティング ---

Route::prefix('admin')->name('admin.')->group(function () {

    // PG07 ログイン画面（管理者）
    Route::get('/login', [AdminAuthController::class, 'login'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'loginStore'])->name('login.store');

    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    // ▼ 勤怠管理
    Route::group(['prefix' => 'attendance'], function () {
        // PG08 勤怠一覧画面（管理者）
        Route::get('/list', [AdminAttendanceController::class, 'list'])->name('attendance.list');

        // PG09 勤怠詳細画面（管理者）
        Route::get('/{id}', [AdminAttendanceController::class, 'show'])->name('attendance.show');
        Route::post('/{id}', [AdminAttendanceController::class, 'update'])->name('attendance.update');

        // PG11 スタッフ別勤怠一覧画面
        Route::get('/staff/{id}', [AdminAttendanceController::class, 'staffList'])->name('attendance.staff');
        Route::get('/staff/{id}/csv', [AdminAttendanceController::class, 'exportCsv'])->name('attendance.csv');
    });

    // ▼ スタッフ管理
    // PG10 スタッフ一覧画面
    Route::get('/staff/list', [AdminStaffController::class, 'index'])->name('staff.list');

    // ▼ 修正申請管理（管理者専用アクション）
    Route::group(['prefix' => 'stamp_correction_request'], function () {

        Route::get('/list', [AdminStampCorrectionRequestController::class, 'index'])
            ->name('stamp_correction_request.index');

        // PG13 修正申請承認画面
        Route::get('/approve/{attendance_correct_request_id}', [AdminStampCorrectionRequestController::class, 'approveView'])
            ->name('stamp_correction_request.approve');

        // 承認処理（POST）
        Route::post('/approve/{attendance_correct_request_id}', [AdminStampCorrectionRequestController::class, 'approve'])
            ->name('stamp_correction_request.approve.action');
    });

});
