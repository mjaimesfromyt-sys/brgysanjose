<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\OtpVerificationController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
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
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\EquipmentController;
use App\Http\Controllers\Admin\EquipmentRentalController as AdminEquipmentRentalController;
use App\Http\Controllers\Admin\TransactionHistoryController;
use App\Http\Controllers\Admin\RefundRequestController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Admin\StaffController;

// Public pages — barangay news and events are readable without an account.
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/announcements', [HomeController::class, 'announcements'])->name('announcements.index');
Route::get('/announcements/{announcement}', [HomeController::class, 'show'])->name('announcements.show');
Route::get('/verify/{code}', [\App\Http\Controllers\DocumentVerificationController::class, 'verify'])->middleware('throttle:30,1')->name('document.verify');
Route::get('/map', [\App\Http\Controllers\GisMapController::class, 'index'])->name('map.index');

// Guest-only auth routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:3,1')->name('register.store');
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
    Route::get('/verify-otp', [OtpVerificationController::class, 'show'])->name('otp.verify.form');
    Route::post('/verify-otp', [OtpVerificationController::class, 'verify'])->middleware('throttle:5,1')->name('otp.verify');
    Route::post('/verify-otp/resend', [OtpVerificationController::class, 'resend'])->middleware('throttle:2,1')->name('otp.resend');
    // Password reset (signed, time-limited email link)
    Route::get('/forgot-password', [ForgotPasswordController::class, 'show'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'emailResetLink'])->middleware('throttle:2,1')->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'show'])->middleware('signed')->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'update'])->name('password.update');

    Route::get('/auth/google', [SocialAuthController::class, 'redirectToGoogle'])->name('google.login');
    Route::get('/auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback'])->name('google.callback');    
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/activity-logs/log-print', [\App\Http\Controllers\ActivityLogController::class, 'logPrint'])->name('activity_logs.log_print');
    
    // Resident Profile Routes
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/photo', [\App\Http\Controllers\ProfileController::class, 'updatePhoto'])->name('profile.photo.update');
    Route::delete('/profile/photo', [\App\Http\Controllers\ProfileController::class, 'removePhoto'])->name('profile.photo.remove');
    Route::put('/profile/details', [\App\Http\Controllers\ProfileController::class, 'updateDetails'])->name('profile.details.update');
    Route::put('/profile/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // Captain's Appointments (resident side)
    Route::get('/appointments', [\App\Http\Controllers\CaptainAppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/appointments/create', [\App\Http\Controllers\CaptainAppointmentController::class, 'create'])->name('appointments.create');
    Route::post('/appointments', [\App\Http\Controllers\CaptainAppointmentController::class, 'store'])->name('appointments.store');
    Route::get('/appointments/slots', [\App\Http\Controllers\CaptainAppointmentController::class, 'slots'])->name('appointments.slots');

    // Official PDF Document Generation
    Route::get('/documents/{token}/pdf', [\App\Http\Controllers\DocumentPdfController::class, 'generate'])->name('documents.pdf');

    // In-app notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/poll', [NotificationController::class, 'poll'])->name('notifications.poll');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.readAll');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/schedule', [\App\Http\Controllers\BookingController::class, 'checkSchedule'])->name('bookings.schedule');
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
    
    // ================= ADMIN AREA (Both Super Admin & Sub-Admins) =================
    Route::middleware('role:admin,super_admin')->prefix('admin')->name('admin.')->group(function () {
        // Captain's Appointments & Schedule Management
        Route::get('/appointments', [\App\Http\Controllers\Admin\CaptainScheduleController::class, 'index'])->name('appointments.index');
        Route::post('/appointments/{id}/status', [\App\Http\Controllers\Admin\CaptainScheduleController::class, 'updateStatus'])->name('appointments.update-status');
        Route::post('/appointments/unavailability', [\App\Http\Controllers\Admin\CaptainScheduleController::class, 'storeUnavailability'])->name('appointments.unavailability.store');
        Route::delete('/appointments/unavailability/{id}', [\App\Http\Controllers\Admin\CaptainScheduleController::class, 'destroyUnavailability'])->name('appointments.unavailability.destroy');

        // 👉 AJAX polling para sa sidebar badge counters
        Route::get('/pending-counts', function () {
            return response()->json(\Illuminate\Support\Facades\Cache::remember('admin.pending_counts', 30, fn () => [
                'residents' => \App\Models\User::where('role', 'resident')->where('status', 'pending')->count(),
                'requests'  => \App\Models\DocumentRequest::where('status', 'pending')->count(),
                'bookings'  => \App\Models\Booking::where('status', 'pending')->count(),
                'rentals'   => \App\Models\EquipmentRental::where('status', 'pending')->count(),
                'refunds'   => \App\Models\RefundRequest::where('status', 'requested')->count(),
            ]));
        })->name('pendingCounts');
        Route::get('/', function () {
            return redirect()->route('admin.transaction-history.index');
        })->name('home');

        // Operations: Accessible by ALL Sub-Admins & Super Admin
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
        Route::put('/equipment/{equipment}', [EquipmentController::class, 'update'])->name('equipment.update');
        Route::get('/rentals', [AdminEquipmentRentalController::class, 'index'])->name('rentals.index');
        Route::post('/rentals/{rental}/approve', [AdminEquipmentRentalController::class, 'approve'])->name('rentals.approve');
        Route::post('/rentals/{rental}/reject', [AdminEquipmentRentalController::class, 'reject'])->name('rentals.reject');
        Route::post('/rentals/{rental}/release', [AdminEquipmentRentalController::class, 'release'])->name('rentals.release');
        Route::post('/rentals/{rental}/return', [AdminEquipmentRentalController::class, 'markReturned'])->name('rentals.return');
        Route::post('/rentals/{rental}/mark-paid', [AdminEquipmentRentalController::class, 'markPaid'])->name('rentals.markPaid');

        // 👉 Magpabilin sa sidebar para sa tanang Sub-Admin & Super Admin:
        Route::get('/transaction-history', [TransactionHistoryController::class, 'index'])->name('transaction-history.index');

        // Refunds
        Route::get('/refunds', [RefundRequestController::class, 'index'])->name('refunds.index');
        Route::post('/refunds/{refundRequest}/approve', [RefundRequestController::class, 'approve'])->name('refunds.approve');
        Route::post('/refunds/{refundRequest}/reject', [RefundRequestController::class, 'reject'])->name('refunds.reject');
        Route::post('/refunds/{refundRequest}/process', [RefundRequestController::class, 'process'])->name('refunds.process');

        // ================= SUPER ADMIN ONLY SECTION =================
        Route::middleware('role:super_admin')->group(function () {
            // Activity Log (Super Admin Ra Gyud Makasulod)
            Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');

            // Staff & Sub-Admin Credentials Management
            Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
            Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
            Route::put('/staff/{user}', [StaffController::class, 'update'])->name('staff.update');
            Route::post('/staff/{user}/toggle', [StaffController::class, 'toggleStatus'])->name('staff.toggle');
        });
    });

    // Official + admin shared area
    Route::middleware('role:admin,super_admin,official')->prefix('reports')->name('reports.')->group(function () {
        Route::get('/requests', [ReportController::class, 'documentRequests'])->name('requests');
        Route::get('/bookings', [ReportController::class, 'bookings'])->name('bookings');
        Route::get('/rentals', [ReportController::class, 'rentals'])->name('rentals');
    });
});
// PayMongo webhook — authenticated by the Paymongo-Signature header,
// not by sessions or CSRF tokens.
Route::post('/webhooks/paymongo', \App\Http\Controllers\PayMongoWebhookController::class)
    ->name('webhooks.paymongo');

// 👉 Super Admin Settings para sa Barangay Officials & Letterhead
Route::middleware(['auth', 'role:super_admin'])->prefix('admin/settings')->name('admin.settings.')->group(function () {
    Route::get('/officials', [\App\Http\Controllers\Admin\BarangaySettingController::class, 'index'])->name('officials.index');
    Route::put('/officials', [\App\Http\Controllers\Admin\BarangaySettingController::class, 'update'])->name('officials.update');
});
