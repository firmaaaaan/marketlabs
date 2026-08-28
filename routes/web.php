<?php

use App\Http\Controllers\Admin\AdminActivityLogController;
use App\Http\Controllers\Admin\AdminBenchFeeController;
use App\Http\Controllers\Admin\AdminBorrowingController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminDocumentDownloadController;
use App\Http\Controllers\Admin\AdminEventController;
use App\Http\Controllers\Admin\AdminFaqController;
use App\Http\Controllers\Admin\AdminFooterController;
use App\Http\Controllers\Admin\AdminHealthCheckupController;
use App\Http\Controllers\Admin\AdminHealthCheckupTypeController;
use App\Http\Controllers\Admin\AdminInvoiceController;
use App\Http\Controllers\Admin\AdminLaboratoriumController;
use App\Http\Controllers\Admin\AdminMitrasController;
use App\Http\Controllers\Admin\AdminResearchProposalController;
use App\Http\Controllers\Admin\AdminSampleAttributeController;
use App\Http\Controllers\Admin\AdminSampleTestController;
use App\Http\Controllers\Admin\AdminSampleUnitController;
use App\Http\Controllers\Admin\AdminScheduleController;
use App\Http\Controllers\Admin\AdminTestimonialController;
use App\Http\Controllers\Admin\AdminTestParameterController;
use App\Http\Controllers\Admin\AdminToolController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminWhatsAppController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BorrowingController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\HealthCheckupController;
use App\Http\Controllers\LaboranController;
use App\Http\Controllers\LaboranHealthCheckupController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResearchProposalController;
use App\Http\Controllers\SampleTestController;
use App\Http\Controllers\TestCartController;
use App\Http\Controllers\ToolController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingPageController::class, 'index'])->name('home');

Route::post('/kontak', [ContactController::class, 'send'])->middleware('throttle:contact')->name('contact.send');

Route::get('/alat', [ToolController::class, 'index'])->name('tools.index');
Route::get('/alat/{tool}', [ToolController::class, 'show'])->name('tools.show');

Route::get('/katalog-pengujian', [SampleTestController::class, 'catalog'])->name('sample-tests.catalog');

Route::get('/katalog-pemeriksaan', [HealthCheckupController::class, 'catalog'])->name('health-checkups.catalog');

Route::get('/event', [EventController::class, 'index'])->name('events.index');

Route::get('/keranjang', [CartController::class, 'index'])->middleware('throttle:public')->name('cart.index');
Route::get('/keranjang/json', [CartController::class, 'json'])->middleware('throttle:public')->name('cart.json');
Route::post('/keranjang/{tool}', [CartController::class, 'add'])->middleware('throttle:public')->name('cart.add');
Route::patch('/keranjang/{tool}', [CartController::class, 'update'])->middleware('throttle:public')->name('cart.update');
Route::delete('/keranjang/{tool}', [CartController::class, 'remove'])->middleware('throttle:public')->name('cart.remove');
Route::delete('/keranjang', [CartController::class, 'clear'])->middleware('throttle:public')->name('cart.clear');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:auth');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:register');

    Route::get('/lupa-password', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
    Route::post('/lupa-password', [PasswordResetController::class, 'sendResetLink'])->middleware('throttle:auth')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:auth')->name('password.update');
});

Route::middleware(['auth', 'throttle.mutations'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Profil — bisa diakses meskipun profil belum lengkap.
    Route::get('/profil/lengkapi', [ProfileController::class, 'complete'])->name('profile.complete');
    Route::patch('/profil/lengkapi', [ProfileController::class, 'completeUpdate'])->name('profile.complete.update');
    Route::get('/profil', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profil/password', [ProfileController::class, 'changePassword'])->name('profile.password');

    // Semua fitur lain — wajib profil lengkap (hanya user biasa).
    Route::middleware('profile.complete')->group(function () {

        Route::get('/pemeriksaan-kesehatan', [HealthCheckupController::class, 'index'])->name('health-checkups.index');
        Route::get('/pemeriksaan-kesehatan/booking', [HealthCheckupController::class, 'create'])->name('health-checkups.create');
        Route::get('/pemeriksaan-kesehatan/perkiraan', [HealthCheckupController::class, 'estimate'])->name('health-checkups.estimate');
        Route::post('/pemeriksaan-kesehatan', [HealthCheckupController::class, 'store'])->name('health-checkups.store');
        Route::get('/pemeriksaan-kesehatan/{checkup}/antrian', [HealthCheckupController::class, 'ticket'])->name('health-checkups.ticket');
        Route::get('/pemeriksaan-kesehatan/{checkup}', [HealthCheckupController::class, 'show'])->name('health-checkups.show');
        Route::get('/pemeriksaan-kesehatan/{checkup}/invoice', [HealthCheckupController::class, 'invoice'])->name('health-checkups.invoice');
        Route::get('/pemeriksaan-kesehatan/{checkup}/surat-hasil', [HealthCheckupController::class, 'certificate'])->name('health-checkups.certificate');
        Route::delete('/pemeriksaan-kesehatan/{checkup}', [HealthCheckupController::class, 'cancel'])->name('health-checkups.cancel');

        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/notifikasi', [NotificationController::class, 'all'])->name('notifications.all');
        Route::post('/notifications/read', [NotificationController::class, 'markRead'])->name('notifications.read');

        Route::post('/event/{event:slug}/daftar', [EventController::class, 'store'])->name('events.store');
        Route::get('/event/{event:slug}/cari-teman', [EventController::class, 'searchFriend'])->middleware('throttle:search')->name('events.search-friend');
        Route::get('/event/saya', [EventController::class, 'my'])->name('events.my');
        Route::get('/event/absensi/{token}', [EventController::class, 'attendance'])->name('events.attendance');
        Route::post('/event/absensi/{token}', [EventController::class, 'attendanceStore'])->name('events.attendance.store');
        Route::get('/event/sertifikat/{registration}', [EventController::class, 'certificate'])->name('events.certificate');
        Route::get('/event/sertifikat/{registration}/unduh', [EventController::class, 'certificateDownload'])->name('events.certificate.download');

        Route::get('/peminjaman/{borrowing}/dokumen', [FileController::class, 'borrowingDocument'])->middleware('throttle:files')->name('borrowings.document');
        Route::get('/riset/{proposal}/dokumen/{type}', [FileController::class, 'researchDocument'])->middleware('throttle:files')->name('research.document');
        Route::get('/pengujian/{test}/hasil', [FileController::class, 'sampleTestResult'])->middleware('throttle:files')->name('sample-tests.result-download');
        Route::get('/pemeriksaan-kesehatan/{checkup}/hasil', [FileController::class, 'healthCheckupResult'])->middleware('throttle:files')->name('health-checkups.result-download');
        Route::get('/event/{registration}/jawaban/{key}', [FileController::class, 'eventAnswerFile'])->middleware('throttle:files')->name('events.answer-file');

        Route::get('/peminjaman', [BorrowingController::class, 'index'])->name('borrowings.index');
        Route::get('/peminjaman/export', [BorrowingController::class, 'export'])->name('borrowings.export');
        Route::get('/peminjaman/baru', [BorrowingController::class, 'create'])->name('borrowings.create');
        Route::post('/peminjaman', [BorrowingController::class, 'store'])->name('borrowings.store');
        Route::get('/peminjaman/{borrowing}', [BorrowingController::class, 'show'])->name('borrowings.show');
        Route::get('/peminjaman/{borrowing}/invoice', [BorrowingController::class, 'invoice'])->name('borrowings.invoice');
        Route::delete('/peminjaman/{borrowing}', [BorrowingController::class, 'cancel'])->name('borrowings.cancel');

        Route::get('/riset', [ResearchProposalController::class, 'index'])->name('research.index');
        Route::get('/riset/cari-anggota', [ResearchProposalController::class, 'searchMember'])->middleware('throttle:search')->name('research.search-member');
        Route::get('/riset/baru', [ResearchProposalController::class, 'create'])->name('research.create');
        Route::post('/riset', [ResearchProposalController::class, 'store'])->name('research.store');
        Route::get('/riset/{proposal}/invoice', [ResearchProposalController::class, 'invoice'])->name('research.invoice');
        Route::get('/riset/{proposal}/logbook', [ResearchProposalController::class, 'logbook'])->name('research.logbook');
        Route::get('/riset/{proposal}/logbook/print', [ResearchProposalController::class, 'logbookPrint'])->name('research.logbook.print');
        Route::post('/riset/{proposal}/logbook', [ResearchProposalController::class, 'storeLogbook'])->name('research.logbook.store');
        Route::delete('/riset/{proposal}/logbook/{logbook}', [ResearchProposalController::class, 'destroyLogbook'])->name('research.logbook.destroy');
        Route::get('/riset/{proposal}', [ResearchProposalController::class, 'show'])->name('research.show');
        Route::delete('/riset/{proposal}', [ResearchProposalController::class, 'cancel'])->name('research.cancel');

        Route::get('/pengujian', [SampleTestController::class, 'index'])->name('sample-tests.index');
        Route::get('/pengujian/keranjang/json', [TestCartController::class, 'json'])->name('test-cart.json');
        Route::get('/pengujian/keranjang', [TestCartController::class, 'index'])->name('test-cart.index');
        Route::post('/pengujian/keranjang/{parameter}', [TestCartController::class, 'add'])->name('test-cart.add');
        Route::delete('/pengujian/keranjang/{parameter}', [TestCartController::class, 'remove'])->name('test-cart.remove');
        Route::delete('/pengujian/keranjang', [TestCartController::class, 'clear'])->name('test-cart.clear');
        Route::get('/pengujian/checkout', [SampleTestController::class, 'checkout'])->name('sample-tests.checkout');
        Route::post('/pengujian', [SampleTestController::class, 'store'])->name('sample-tests.store');
        Route::get('/pengujian/parameter/{parameter}', [SampleTestController::class, 'parameter'])->name('sample-tests.parameter');
        Route::get('/pengujian/{test}/invoice', [SampleTestController::class, 'invoice'])->name('sample-tests.invoice');
        Route::get('/pengujian/{test}', [SampleTestController::class, 'show'])->name('sample-tests.show');
        Route::delete('/pengujian/{test}', [SampleTestController::class, 'cancel'])->name('sample-tests.cancel');

    }); // end profile.complete middleware

    // Laboran routes — tanpa syarat profil lengkap.
    Route::get('/laboran', [LaboranController::class, 'index'])->name('laboran.index')->middleware('laboran');
    Route::post('/laboran/pengujian/{test}/status', [LaboranController::class, 'updateStatus'])->name('laboran.tests.status')->middleware('laboran');
    Route::post('/laboran/pengujian/{test}/payment', [LaboranController::class, 'updatePayment'])->name('laboran.tests.payment')->middleware('laboran');
    Route::get('/laboran/pengujian/{test}/cetak', [LaboranController::class, 'print'])->name('laboran.tests.print')->middleware('laboran');

    Route::middleware('laboran')->name('laboran.')->group(function () {
        Route::patch('/laboran/pemeriksaan/{checkup}/status', [LaboranHealthCheckupController::class, 'updateStatus'])->name('health-checkups.status');
        Route::patch('/laboran/pemeriksaan/{checkup}/payment', [LaboranHealthCheckupController::class, 'updatePayment'])->name('health-checkups.payment');
        Route::patch('/laboran/pemeriksaan/{checkup}/result', [LaboranHealthCheckupController::class, 'updateResult'])->name('health-checkups.result');
    });

    // Admin routes — tanpa syarat profil lengkap.
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::get('/tools/export', [AdminToolController::class, 'export'])->middleware('throttle:admin-ops')->name('tools.export');
        Route::get('/tools/template', [AdminToolController::class, 'template'])->name('tools.template');
        Route::post('/tools/import', [AdminToolController::class, 'import'])->middleware('throttle:admin-ops')->name('tools.import');
        Route::resource('tools', AdminToolController::class)->except(['show']);

        Route::resource('categories', AdminCategoryController::class)->only(['index', 'store', 'update', 'destroy']);

        Route::get('/notifications', [AdminBorrowingController::class, 'notifications'])->name('notifications');
        Route::get('/notifikasi', [AdminBorrowingController::class, 'notificationsAll'])->name('notifications.all');
        Route::get('/borrowings', [AdminBorrowingController::class, 'index'])->name('borrowings.index');
        Route::get('/borrowings/export', [AdminBorrowingController::class, 'export'])->middleware('throttle:admin-ops')->name('borrowings.export-excel');
        Route::get('/borrowings/{borrowing}', [AdminBorrowingController::class, 'show'])->name('borrowings.show');
        Route::get('/borrowings/{borrowing}/invoice', [AdminBorrowingController::class, 'invoice'])->name('borrowings.invoice');
        Route::patch('/borrowings/{borrowing}/status', [AdminBorrowingController::class, 'updateStatus'])->name('borrowings.status');
        Route::patch('/borrowings/{borrowing}/billing', [AdminBorrowingController::class, 'updateBilling'])->name('borrowings.billing');
        Route::patch('/borrowings/{borrowing}/payment', [AdminBorrowingController::class, 'updatePayment'])->name('borrowings.payment');

        Route::get('/riset', [AdminResearchProposalController::class, 'index'])->name('research.index');
        Route::get('/riset/export', [AdminResearchProposalController::class, 'export'])->middleware('throttle:admin-ops')->name('research.export');
        Route::get('/riset/{proposal}', [AdminResearchProposalController::class, 'show'])->name('research.show');
        Route::get('/riset/{proposal}/logbook', [AdminResearchProposalController::class, 'logbook'])->name('research.logbook');
        Route::get('/riset/{proposal}/logbook/print', [AdminResearchProposalController::class, 'logbookPrint'])->name('research.logbook.print');
        Route::patch('/riset/{proposal}/status', [AdminResearchProposalController::class, 'updateStatus'])->name('research.status');
        Route::patch('/riset/{proposal}/assignment', [AdminResearchProposalController::class, 'updateAssignment'])->name('research.assignment');
        Route::patch('/riset/{proposal}/payment', [AdminResearchProposalController::class, 'updatePayment'])->name('research.payment');
        Route::patch('/riset/{proposal}/penalty', [AdminResearchProposalController::class, 'updatePenalty'])->name('research.penalty');
        Route::get('/riset/{proposal}/invoice', [AdminResearchProposalController::class, 'invoice'])->name('research.invoice');

        Route::get('/bench-fee', [AdminBenchFeeController::class, 'index'])->name('bench-fee.index');
        Route::put('/bench-fee', [AdminBenchFeeController::class, 'update'])->name('bench-fee.update');

        Route::get('/whatsapp', [AdminWhatsAppController::class, 'index'])->name('whatsapp.index');
        Route::put('/whatsapp', [AdminWhatsAppController::class, 'update'])->name('whatsapp.update');

        Route::get('/invoice', [AdminInvoiceController::class, 'index'])->name('invoice.index');
        Route::put('/invoice', [AdminInvoiceController::class, 'update'])->name('invoice.update');

        Route::get('/jadwal-layanan', [AdminScheduleController::class, 'index'])->name('schedule.index');
        Route::put('/jadwal-layanan', [AdminScheduleController::class, 'update'])->name('schedule.update');
        Route::post('/jadwal-layanan/petugas', [AdminScheduleController::class, 'storeWeekly'])->name('schedule.weekly-store');
        Route::post('/jadwal-layanan/petugas/salin', [AdminScheduleController::class, 'copyPrevious'])->name('schedule.weekly-copy');
        Route::delete('/jadwal-layanan/petugas/{schedule}', [AdminScheduleController::class, 'destroyWeekly'])->name('schedule.weekly-destroy');

        Route::resource('testimonials', AdminTestimonialController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('faqs', AdminFaqController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('mitras', AdminMitrasController::class)->only(['index', 'store', 'update', 'destroy']);

        Route::get('/footer', [AdminFooterController::class, 'index'])->name('footer.index');
        Route::put('/footer', [AdminFooterController::class, 'updateSettings'])->name('footer.settings-update');
        Route::post('/footer/logo', [AdminFooterController::class, 'storeLogo'])->name('footer.logo-store');
        Route::put('/footer/logo/{logo}', [AdminFooterController::class, 'updateLogo'])->name('footer.logo-update');
        Route::delete('/footer/logo/{logo}', [AdminFooterController::class, 'destroyLogo'])->name('footer.logo-destroy');
        Route::post('/footer/logo/{logo}/move/{direction}', [AdminFooterController::class, 'moveLogo'])->name('footer.logo-move');

        Route::get('/activity-logs', [AdminActivityLogController::class, 'index'])->name('activity-logs.index');

        Route::get('/document-downloads', [AdminDocumentDownloadController::class, 'index'])->name('document-downloads.index');
        Route::get('/document-downloads/preview', [AdminDocumentDownloadController::class, 'preview'])->name('document-downloads.preview');
        Route::get('/document-downloads/download', [AdminDocumentDownloadController::class, 'download'])->name('document-downloads.download');

        Route::get('/events/export', [AdminEventController::class, 'export'])->middleware('throttle:admin-ops')->name('events.export');
        Route::get('/events/{event}/export-peserta', [AdminEventController::class, 'exportParticipants'])->middleware('throttle:admin-ops')->name('events.export-participants');
        Route::get('/events/{event}/export-presensi', [AdminEventController::class, 'exportAttendance'])->middleware('throttle:admin-ops')->name('events.export-attendance');
        Route::get('/events/{event}/certificate', [AdminEventController::class, 'certificate'])->name('events.certificate');
        Route::post('/events/{event}/certificate', [AdminEventController::class, 'saveCertificate'])->name('events.certificate.save');
        Route::delete('/events/{event}/certificate/back', [AdminEventController::class, 'deleteCertificateBack'])->name('events.certificate.back-delete');
        Route::post('/events/{event}/certificate/generate', [AdminEventController::class, 'generateCertificates'])->middleware('throttle:admin-ops')->name('events.certificate.generate');
        Route::patch('/events/{event}/registrations/{registration}/status', [AdminEventController::class, 'updateRegistrationStatus'])->name('events.registrations.status');
        Route::patch('/events/{event}/registrations/bulk-status', [AdminEventController::class, 'bulkUpdateStatus'])->name('events.registrations.bulk-status');
        Route::patch('/events/{event}/registrations/{registration}/attendance', [AdminEventController::class, 'markAttendance'])->name('events.registrations.attendance');
        Route::resource('events', AdminEventController::class);

        Route::get('/pemeriksaan-kesehatan', [AdminHealthCheckupController::class, 'index'])->name('health-checkups.index');
        Route::get('/pemeriksaan-kesehatan/{checkup}', [AdminHealthCheckupController::class, 'show'])->name('health-checkups.show');
        Route::patch('/pemeriksaan-kesehatan/{checkup}/status', [AdminHealthCheckupController::class, 'updateStatus'])->name('health-checkups.status');
        Route::patch('/pemeriksaan-kesehatan/{checkup}/result', [AdminHealthCheckupController::class, 'updateResult'])->name('health-checkups.result');
        Route::patch('/pemeriksaan-kesehatan/{checkup}/payment', [AdminHealthCheckupController::class, 'updatePayment'])->name('health-checkups.payment');
        Route::get('/pemeriksaan-kesehatan/{checkup}/invoice', [AdminHealthCheckupController::class, 'invoice'])->name('health-checkups.invoice');
        Route::resource('health-checkup-types', AdminHealthCheckupTypeController::class)->only(['index', 'store', 'update', 'destroy']);

        Route::resource('sample-units', AdminSampleUnitController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('test-parameters', AdminTestParameterController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::patch('/test-parameters/{testParameter}/toggle', [AdminTestParameterController::class, 'toggleActive'])->name('test-parameters.toggle');

        Route::get('/sample-attributes', [AdminSampleAttributeController::class, 'index'])->name('sample-attributes.index');
        Route::post('/sample-attributes/forms', [AdminSampleAttributeController::class, 'storeForm'])->name('sample-attributes.forms.store');
        Route::put('/sample-attributes/forms/{sampleForm}', [AdminSampleAttributeController::class, 'updateForm'])->name('sample-attributes.forms.update');
        Route::delete('/sample-attributes/forms/{sampleForm}', [AdminSampleAttributeController::class, 'destroyForm'])->name('sample-attributes.forms.destroy');
        Route::post('/sample-attributes/types', [AdminSampleAttributeController::class, 'storeType'])->name('sample-attributes.types.store');
        Route::put('/sample-attributes/types/{sampleType}', [AdminSampleAttributeController::class, 'updateType'])->name('sample-attributes.types.update');
        Route::delete('/sample-attributes/types/{sampleType}', [AdminSampleAttributeController::class, 'destroyType'])->name('sample-attributes.types.destroy');

        Route::get('/pengujian', [AdminSampleTestController::class, 'index'])->name('sample-tests.index');
        Route::get('/pengujian/baru', [AdminSampleTestController::class, 'create'])->name('sample-tests.create');
        Route::post('/pengujian', [AdminSampleTestController::class, 'store'])->name('sample-tests.store');
        Route::get('/pengujian/{test}/edit', [AdminSampleTestController::class, 'edit'])->name('sample-tests.edit');
        Route::patch('/pengujian/{test}', [AdminSampleTestController::class, 'update'])->name('sample-tests.update');
        Route::delete('/pengujian/{test}', [AdminSampleTestController::class, 'destroy'])->name('sample-tests.destroy');
        Route::get('/pengujian/{test}', [AdminSampleTestController::class, 'show'])->name('sample-tests.show');
        Route::patch('/pengujian/{test}/status', [AdminSampleTestController::class, 'updateStatus'])->name('sample-tests.status');
        Route::patch('/pengujian/{test}/assignment', [AdminSampleTestController::class, 'updateAssignment'])->name('sample-tests.assignment');
        Route::patch('/pengujian/{test}/result', [AdminSampleTestController::class, 'updateResult'])->name('sample-tests.result');
        Route::post('/pengujian/{test}/hasil', [AdminSampleTestController::class, 'uploadResultFile'])->name('sample-tests.result-file');
        Route::patch('/pengujian/{test}/payment', [AdminSampleTestController::class, 'updatePayment'])->name('sample-tests.payment');
        Route::get('/pengujian/{test}/invoice', [AdminSampleTestController::class, 'invoice'])->name('sample-tests.invoice');
        Route::get('/pengujian/{test}/cetak', [AdminSampleTestController::class, 'print'])->name('sample-tests.print');

        Route::resource('laboratoriums', AdminLaboratoriumController::class)->except(['show']);

        // Superadmin-only routes
        Route::middleware('superadmin')->group(function () {
            Route::get('/users/export', [AdminUserController::class, 'export'])->name('users.export');
            Route::get('/users/template', [AdminUserController::class, 'template'])->name('users.template');
            Route::post('/users/import', [AdminUserController::class, 'import'])->name('users.import');
            Route::resource('users', AdminUserController::class)->except(['show']);

            Route::get('/activity-logs', [AdminActivityLogController::class, 'index'])->name('activity-logs.index');

            Route::get('/menus', [\App\Http\Controllers\Admin\MenuController::class, 'index'])->name('menus.index');
            Route::post('/menus/item', [\App\Http\Controllers\Admin\MenuController::class, 'storeMenuItem'])->name('menus.item.store');
            Route::put('/menus/item/{menuItem}', [\App\Http\Controllers\Admin\MenuController::class, 'updateMenuItem'])->name('menus.item.update');
            Route::delete('/menus/item/{menuItem}', [\App\Http\Controllers\Admin\MenuController::class, 'destroyMenuItem'])->name('menus.item.destroy');
            Route::post('/menus/sort', [\App\Http\Controllers\Admin\MenuController::class, 'sortMenuItem'])->name('menus.item.sort');
            Route::post('/menus/item/{menuItem}/toggle', [\App\Http\Controllers\Admin\MenuController::class, 'toggleMenuItem'])->name('menus.item.toggle');
            Route::put('/menus/section/{section}', [\App\Http\Controllers\Admin\MenuController::class, 'updateSection'])->name('menus.section.update');
            Route::post('/menus/section/{section}/toggle', [\App\Http\Controllers\Admin\MenuController::class, 'toggleSection'])->name('menus.section.toggle');
            Route::post('/menus/sections/sort', [\App\Http\Controllers\Admin\MenuController::class, 'sortSection'])->name('menus.section.sort');
            Route::put('/menus/branding', [\App\Http\Controllers\Admin\MenuController::class, 'updateBranding'])->name('menus.branding.update');

            Route::get('/footer', [AdminFooterController::class, 'index'])->name('footer.index');
            Route::put('/footer', [AdminFooterController::class, 'updateSettings'])->name('footer.settings-update');
            Route::post('/footer/logo', [AdminFooterController::class, 'storeLogo'])->name('footer.logo-store');
            Route::put('/footer/logo/{logo}', [AdminFooterController::class, 'updateLogo'])->name('footer.logo-update');
            Route::delete('/footer/logo/{logo}', [AdminFooterController::class, 'destroyLogo'])->name('footer.logo-destroy');
            Route::post('/footer/logo/{logo}/move/{direction}', [AdminFooterController::class, 'moveLogo'])->name('footer.logo-move');
        });
    });
});

// Didaftarkan setelah route /event spesifik agar /event/saya, /event/absensi/..., /event/sertifikat/... tidak tertelan slug.
Route::get('/event/{event:slug}', [EventController::class, 'show'])->name('events.show');
