<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
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

Route::middleware('guest')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
});

// ▼ 勤怠関連
Route::group(['prefix' => 'attendance', 'middleware' => ['verified']], function () {
    Route::get('/', [AttendanceController::class, 'index'])->name('attendance.index');

    Route::post('/clockin', [AttendanceController::class, 'clockIn'])->name('attendance.clockIn');
    Route::post('/clockout', [AttendanceController::class, 'clockOut'])->name('attendance.clockOut');
    Route::post('/break/start', [AttendanceController::class, 'breakStart'])->name('attendance.breakStart');
    Route::post('/break/end', [AttendanceController::class, 'breakEnd'])->name('attendance.breakEnd');

    // 勤怠一覧画面
    Route::get('/list', [AttendanceController::class, 'list'])->name('attendance.list');

    // 勤怠詳細画面
    Route::get('/detail/{id}', [AttendanceController::class, 'show'])->name('attendance.show');
});

Route::post('/logout', function (\Illuminate\Http\Request $request) {
    Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/login');
})->name('logout');


// ▼ 修正申請関連（一般・管理者 共通URL）
Route::middleware(['auth'])->get('/stamp_correction_request/list', function () {

    $user = \Illuminate\Support\Facades\Auth::user();

    if ($user->role_id === 2) {
        return app()->call([app(AdminStampCorrectionRequestController::class), 'index']);
    }

    return app()->call([app(StampCorrectionRequestController::class), 'index']);

})->name('stamp_correction_request.index');

// ▼ 一般ユーザー用の申請送信
Route::middleware(['auth:web'])->group(function () {
    Route::post('/stamp_correction_request/store', [StampCorrectionRequestController::class, 'store'])
        ->name('stamp_correction_request.store');
});


// --- 管理者用ルーティング ---

Route::prefix('admin')->name('admin.')->group(function () {

    Route::get('/login', [AdminAuthController::class, 'login'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'loginStore'])->name('login.store');

    Route::middleware(['auth', 'admin'])->group(function () {

        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

        // ▼ 勤怠管理
        Route::group(['prefix' => 'attendance'], function () {
            Route::get('/list', [AdminAttendanceController::class, 'list'])->name('attendance.list');
            Route::get('/{id}', [AdminAttendanceController::class, 'show'])->name('attendance.show');
            Route::post('/{id}', [AdminAttendanceController::class, 'update'])->name('attendance.update');
            Route::get('/staff/{id}', [AdminAttendanceController::class, 'staffList'])->name('attendance.staff');
            Route::get('/staff/{id}/csv', [AdminAttendanceController::class, 'exportCsv'])->name('attendance.csv');
        });

        // ▼ スタッフ管理
        Route::get('/staff/list', [AdminStaffController::class, 'index'])->name('staff.list');
    });
});

// ▼ 管理者用の「承認画面」関連
Route::middleware(['auth', 'admin'])->name('admin.')->group(function () {

    Route::group(['prefix' => 'stamp_correction_request'], function () {

        // 承認画面 (GET)
        Route::get('/approve/{attendance_correct_request_id}', [AdminStampCorrectionRequestController::class, 'approveView'])
            ->name('stamp_correction_request.approve');

        // 承認処理 (POST)
        Route::post('/approve/{attendance_correct_request_id}', [AdminStampCorrectionRequestController::class, 'approve'])
            ->name('stamp_correction_request.approve.action');

    });
});