<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\FacilityController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\Admin\BookingApprovalController;
use App\Http\Controllers\Admin\ResidentController;
use App\Http\Controllers\InfoController;
use App\Http\Controllers\Admin\TransactionTypeController;
use App\Http\Controllers\EventCalendarController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\DocumentRequestController;
use App\Http\Controllers\Admin\DocumentRequestController as AdminDocumentRequestController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\EquipmentRentalController;
use App\Http\Controllers\Admin\EquipmentController;
use App\Http\Controllers\Admin\EquipmentRentalController as AdminEquipmentRentalController;
use App\Http\Controllers\Admin\TransactionHistoryController;
use App\Http\Controllers\Admin\RefundRequestController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;

// Public pages — barangay news and events are readable without an account.
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/announcements', [HomeController::class, 'announcements'])->name('announcements.index');
Route::get('/announcements/{announcement}', [HomeController::class, 'show'])->name('announcements.show');

// Guest-only auth routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

// Authenticated routes
    Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // My Profile — reached by clicking your own name in the top bar.
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo.update');
    Route::delete('/profile/photo', [ProfileController::class, 'destroyPhoto'])->name('profile.photo.destroy');
    // Profile photos live outside the web root, so they are streamed rather
    // than linked. See ProfileController::photo().
    Route::get('/profile/photo/{user}', [ProfileController::class, 'photo'])->name('profile.photo');

    // In-app notifications (bell menu in the top bar)
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.readAll');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{booking}/receipt', [BookingController::class, 'receipt'])->name('bookings.receipt');
    Route::get('/bookings/{booking}/pay/callback', [BookingController::class, 'paymentCallback'])->name('bookings.pay.callback');
    Route::get('/bookings/{booking}/pay/cancel', [BookingController::class, 'paymentCancelled'])->name('bookings.pay.cancel');
    Route::post('/bookings/{booking}/pay/retry', [BookingController::class, 'retryPayment'])->name('bookings.pay.retry');
    Route::get('/info', [InfoController::class, 'index'])->name('info.index');
    Route::get('/info/{transactionType}', [InfoController::class, 'show'])->name('info.show');
    Route::get('/events', [EventCalendarController::class, 'index'])->name('events.calendar');
    Route::get('/requests', [DocumentRequestController::class, 'index'])->name('requests.index');
    Route::get('/requests/create', [DocumentRequestController::class, 'create'])->name('requests.create');
    Route::post('/requests', [DocumentRequestController::class, 'store'])->name('requests.store');
    Route::get('/requests/{documentRequest}/receipt', [DocumentRequestController::class, 'receipt'])->name('requests.receipt');
    Route::get('/requests/{documentRequest}/pay/callback', [DocumentRequestController::class, 'paymentCallback'])->name('requests.pay.callback');
    Route::get('/requests/{documentRequest}/pay/cancel', [DocumentRequestController::class, 'paymentCancelled'])->name('requests.pay.cancel');
    Route::post('/requests/{documentRequest}/pay/retry', [DocumentRequestController::class, 'retryPayment'])->name('requests.pay.retry');
    Route::get('/rentals', [EquipmentRentalController::class, 'index'])->name('rentals.index');
    Route::get('/rentals/create', [EquipmentRentalController::class, 'create'])->name('rentals.create');
    Route::post('/rentals', [EquipmentRentalController::class, 'store'])->name('rentals.store');
    Route::get('/rentals/{rental}/receipt', [EquipmentRentalController::class, 'receipt'])->name('rentals.receipt');
    Route::get('/rentals/{rental}/pay/callback', [EquipmentRentalController::class, 'paymentCallback'])->name('rentals.pay.callback');
    Route::get('/rentals/{rental}/pay/cancel', [EquipmentRentalController::class, 'paymentCancelled'])->name('rentals.pay.cancel');
    Route::post('/rentals/{rental}/pay/retry', [EquipmentRentalController::class, 'retryPayment'])->name('rentals.pay.retry');
    Route::post('/rentals/{rental}/refund-request', [EquipmentRentalController::class, 'requestRefund'])->name('rentals.refund.request');
    
    // Admin-only area (example, expands in later modules)
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', function () {
            return 'Admin area OK — you have access.';
        })->name('home');
        Route::get('/facilities', [FacilityController::class, 'index'])->name('facilities.index');
        Route::post('/facilities', [FacilityController::class, 'store'])->name('facilities.store');
        Route::post('/facilities/{facility}/toggle', [FacilityController::class, 'toggle'])->name('facilities.toggle');
        Route::get('/bookings', [BookingApprovalController::class, 'index'])->name('bookings.index');
        Route::post('/bookings/{booking}/approve', [BookingApprovalController::class, 'approve'])->name('bookings.approve');
        Route::post('/bookings/{booking}/reject', [BookingApprovalController::class, 'reject'])->name('bookings.reject');
        Route::post('/bookings/{booking}/mark-paid', [BookingApprovalController::class, 'markPaid'])->name('bookings.markPaid');
        Route::get('/residents', [ResidentController::class, 'index'])->name('residents.index');
        Route::post('/residents/{user}/approve', [ResidentController::class, 'approve'])->name('residents.approve');
        Route::post('/residents/{user}/reject', [ResidentController::class, 'reject'])->name('residents.reject');
        Route::post('/residents/{user}/reconsider', [ResidentController::class, 'reconsider'])->name('residents.reconsider');
        Route::get('/transactions', [TransactionTypeController::class, 'index'])->name('transactions.index');
        Route::post('/transactions', [TransactionTypeController::class, 'store'])->name('transactions.store');
        Route::get('/transactions/{transactionType}/edit', [TransactionTypeController::class, 'edit'])->name('transactions.edit');
        Route::put('/transactions/{transactionType}', [TransactionTypeController::class, 'update'])->name('transactions.update');
        Route::post('/transactions/{transactionType}/requirements', [TransactionTypeController::class, 'addRequirement'])->name('transactions.requirements.add');
        Route::delete('/transactions/{transactionType}/requirements/{requirement}', [TransactionTypeController::class, 'deleteRequirement'])->name('transactions.requirements.delete');
        Route::get('/events', [EventController::class, 'index'])->name('events.index');
        Route::post('/events', [EventController::class, 'store'])->name('events.store');
        Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');
        Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
        Route::post('/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
        Route::get('/announcements/{announcement}/edit', [AnnouncementController::class, 'edit'])->name('announcements.edit');
        Route::put('/announcements/{announcement}', [AnnouncementController::class, 'update'])->name('announcements.update');
        Route::post('/announcements/{announcement}/publish', [AnnouncementController::class, 'publishNow'])->name('announcements.publish');
        Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');
        Route::get('/requests', [AdminDocumentRequestController::class, 'index'])->name('requests.index');
        Route::post('/requests/{documentRequest}/validate', [AdminDocumentRequestController::class, 'validateRequest'])->name('requests.validate');
        Route::post('/requests/{documentRequest}/reject', [AdminDocumentRequestController::class, 'reject'])->name('requests.reject');
        Route::post('/requests/{documentRequest}/claimed', [AdminDocumentRequestController::class, 'markClaimed'])->name('requests.claimed');
        Route::post('/requests/{documentRequest}/mark-paid', [AdminDocumentRequestController::class, 'markPaid'])->name('requests.markPaid');
        Route::get('/equipment', [EquipmentController::class, 'index'])->name('equipment.index');
        Route::post('/equipment', [EquipmentController::class, 'store'])->name('equipment.store');
        Route::post('/equipment/{equipment}/toggle', [EquipmentController::class, 'toggle'])->name('equipment.toggle');
        Route::get('/rentals', [AdminEquipmentRentalController::class, 'index'])->name('rentals.index');
        Route::post('/rentals/{rental}/approve', [AdminEquipmentRentalController::class, 'approve'])->name('rentals.approve');
        Route::post('/rentals/{rental}/reject', [AdminEquipmentRentalController::class, 'reject'])->name('rentals.reject');
        Route::post('/rentals/{rental}/release', [AdminEquipmentRentalController::class, 'release'])->name('rentals.release');
        Route::post('/rentals/{rental}/return', [AdminEquipmentRentalController::class, 'markReturned'])->name('rentals.return');
        Route::post('/rentals/{rental}/mark-paid', [AdminEquipmentRentalController::class, 'markPaid'])->name('rentals.markPaid');
        Route::get('/transaction-history', [TransactionHistoryController::class, 'index'])->name('transaction-history.index');

        // Equipment rental cancellation / refund queue
        Route::get('/refunds', [RefundRequestController::class, 'index'])->name('refunds.index');
        Route::post('/refunds/{refundRequest}/approve', [RefundRequestController::class, 'approve'])->name('refunds.approve');
        Route::post('/refunds/{refundRequest}/reject', [RefundRequestController::class, 'reject'])->name('refunds.reject');
        Route::post('/refunds/{refundRequest}/process', [RefundRequestController::class, 'process'])->name('refunds.process');
    // booking approvals, resident verification, etc. go here later
    });

    // Official + admin shared area
    Route::middleware('role:admin,official')->prefix('reports')->name('reports.')->group(function () {
        Route::get('/requests', [ReportController::class, 'documentRequests'])->name('requests');
        Route::get('/bookings', [ReportController::class, 'bookings'])->name('bookings');
        Route::get('/rentals', [ReportController::class, 'rentals'])->name('rentals');
    });
});