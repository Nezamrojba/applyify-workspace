<?php

use Illuminate\Support\Facades\Route;
use App\Presentation\Http\Controllers\Auth\AuthController;
use App\Presentation\Http\Controllers\Admin\UserController as AdminUserController;
use App\Presentation\Http\Controllers\Admin\UniversityController as AdminUniversityController;
use App\Presentation\Http\Controllers\Admin\CourseController as AdminCourseController;
use App\Presentation\Http\Controllers\PublicApi\UniversityController as PublicUniversityController;
use App\Presentation\Http\Controllers\PublicApi\CourseController as PublicCourseController;
use App\Presentation\Http\Controllers\Application\ApplicationController as AppController;
use App\Presentation\Http\Controllers\Application\DocumentController as AppDocumentController;
use App\Presentation\Http\Controllers\Application\StageController as AppStageController;
use App\Presentation\Http\Controllers\Application\LetterController as AppLetterController;
use App\Presentation\Http\Controllers\Application\MessageController as AppMessageController;
use App\Presentation\Http\Controllers\Application\DocumentStatusController as AppDocumentStatusController;
use App\Presentation\Http\Controllers\Application\PaymentController as AppPaymentController;
use App\Presentation\Http\Controllers\Application\FinalizeController as AppFinalizeController;
use App\Presentation\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Presentation\Http\Controllers\Application\MessageReadController as AppMessageReadController;
use App\Presentation\Http\Controllers\Admin\StaffPointsController as AdminStaffPointsController;
use App\Presentation\Http\Controllers\Staff\DashboardController as StaffDashboardController;
use App\Presentation\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Presentation\Http\Controllers\Student\JourneyController as StudentJourneyController;
use App\Presentation\Http\Controllers\Admin\AuditController as AdminAuditController;
use App\Presentation\Http\Controllers\Admin\MetricsController as AdminMetricsController;
use App\Presentation\Http\Controllers\Admin\ApplicationAssignmentController as AdminAssignmentController;
use App\Presentation\Http\Controllers\Admin\CountryController as AdminCountryController;
use App\Presentation\Http\Controllers\Admin\CourseFeeStructureController as AdminCourseFeeStructureController;
use App\Presentation\Http\Controllers\Uploads\PresignController;
use App\Presentation\Http\Controllers\Uploads\LocalUploadController;
use App\Presentation\Http\Controllers\PublicApi\PreApplicationInquiryController;
use App\Presentation\Http\Controllers\Admin\FaqController as AdminFaqController;
use App\Presentation\Http\Controllers\Staff\FaqController as StaffFaqController;
use App\Presentation\Http\Controllers\PublicApi\FaqController as PublicFaqController;

Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::post('auth/logout', [AuthController::class, 'logout']);

    Route::get('applications', [AppController::class, 'index']);
    Route::get('applications/check-limit', [AppController::class, 'checkApplicationLimit']);
    Route::post('applications', [AppController::class, 'store']);
    Route::get('applications/{application}', [AppController::class, 'show']);
    Route::patch('applications/{application}/soft-delete', [AppController::class, 'softDelete']);
    Route::get('applications/{application}/documents', [AppDocumentController::class, 'index']);
    Route::post('applications/{application}/documents', [AppDocumentController::class, 'store']);
    Route::patch('applications/{application}/stages/{stageKey}/submit', [AppStageController::class, 'submit']);
    Route::patch('applications/{application}/stages/{stageKey}/approve', [AppStageController::class, 'approveStage']);
    Route::patch('applications/{application}/payment/approve', [AppController::class, 'approvePayment']);
    Route::patch('applications/{application}/payment/reject', [AppController::class, 'rejectPayment']);
    Route::patch('applications/{application}/payment/receipt', [AppController::class, 'updatePaymentReceipt']);
    Route::post('applications/{application}/letters', [AppLetterController::class, 'store']);
    Route::get('applications/{application}/messages', [AppMessageController::class, 'index']);
    Route::post('applications/{application}/messages', [AppMessageController::class, 'store']);
    Route::patch('applications/{application}/messages/{message}/read', [AppMessageReadController::class, 'mark']);
    Route::patch('applications/{application}/documents/{document}', [AppDocumentStatusController::class, 'update']);
    Route::post('applications/{application}/payments', [AppPaymentController::class, 'store']);
    Route::post('applications/{application}/finalize-arrival', [AppFinalizeController::class, 'finalize']);
    Route::get('staff/dashboard', [StaffDashboardController::class, 'index']);
    Route::get('student/dashboard', [StudentDashboardController::class, 'index']);
    Route::get('admin/settings', [AdminSettingsController::class, 'index']);
    Route::post('uploads/presign', [PresignController::class, 'presign']);
    Route::post('uploads/local', [LocalUploadController::class, 'store']);
    Route::get('student/journey/universities', [StudentJourneyController::class, 'universities']);
    Route::get('student/journey/courses', [StudentJourneyController::class, 'courses']);
});

Route::middleware(['auth:sanctum', 'app.role:super_admin'])->group(function () {
    Route::get('admin/users', [AdminUserController::class, 'index']);
    Route::post('admin/users', [AdminUserController::class, 'store']);
    Route::get('admin/users/{user}', [AdminUserController::class, 'show']);
    Route::patch('admin/users/{user}', [AdminUserController::class, 'update']);
    Route::post('admin/settings', [AdminSettingsController::class, 'store']);
    Route::get('admin/staff/points', [AdminStaffPointsController::class, 'index']);
    Route::get('admin/staff/points/summary', [AdminStaffPointsController::class, 'summary']);
    Route::post('admin/staff/points/{point}/release', [AdminStaffPointsController::class, 'release']);
    Route::get('admin/audits', [AdminAuditController::class, 'index']);
    Route::get('admin/metrics', [AdminMetricsController::class, 'index']);
    Route::post('admin/applications/{application}/reassign', [AdminAssignmentController::class, 'reassign']);
    Route::get('admin/universities', [AdminUniversityController::class, 'index']);
    Route::post('admin/universities', [AdminUniversityController::class, 'store']);
    Route::get('admin/universities/{university}', [AdminUniversityController::class, 'show']);
    Route::patch('admin/universities/{university}', [AdminUniversityController::class, 'update']);
    Route::delete('admin/universities/{university}', [AdminUniversityController::class, 'destroy']);
    Route::get('admin/countries', [AdminCountryController::class, 'index']);
    Route::post('admin/countries', [AdminCountryController::class, 'store']);
    Route::get('admin/countries/{country}', [AdminCountryController::class, 'show']);
    Route::patch('admin/countries/{country}', [AdminCountryController::class, 'update']);
    Route::delete('admin/countries/{country}', [AdminCountryController::class, 'destroy']);
    Route::get('admin/courses', [AdminCourseController::class, 'index']);
    Route::get('admin/courses/{course}', [AdminCourseController::class, 'show']);
    Route::post('admin/courses', [AdminCourseController::class, 'store']);
    Route::patch('admin/courses/{course}', [AdminCourseController::class, 'update']);
    Route::delete('admin/courses/{course}', [AdminCourseController::class, 'destroy']);
    
    Route::get('admin/courses/{course}/fee-structures', [AdminCourseFeeStructureController::class, 'index']);
    Route::post('admin/courses/{course}/fee-structures', [AdminCourseFeeStructureController::class, 'store']);
    Route::get('admin/courses/{course}/fee-structures/{feeStructure}', [AdminCourseFeeStructureController::class, 'show']);
    Route::patch('admin/courses/{course}/fee-structures/{feeStructure}', [AdminCourseFeeStructureController::class, 'update']);
    Route::delete('admin/courses/{course}/fee-structures/{feeStructure}', [AdminCourseFeeStructureController::class, 'destroy']);
    
    // FAQ Management (Super Admin)
    Route::get('admin/faqs', [AdminFaqController::class, 'index']);
    Route::post('admin/faqs', [AdminFaqController::class, 'store']);
    Route::patch('admin/faqs/{faq}', [AdminFaqController::class, 'update']);
    Route::delete('admin/faqs/{faq}', [AdminFaqController::class, 'destroy']);
    Route::post('admin/faqs/{faq}/assign-staff', [AdminFaqController::class, 'assignStaff']);
    Route::post('admin/faqs/{faq}/unassign-staff', [AdminFaqController::class, 'unassignStaff']);
});

Route::middleware(['auth:sanctum', 'permission:roles.manage'])->group(function () {
    Route::get('admin/roles', [\App\Presentation\Http\Controllers\Admin\RoleController::class, 'index']);
    Route::post('admin/roles', [\App\Presentation\Http\Controllers\Admin\RoleController::class, 'store']);
    Route::patch('admin/roles/{role}', [\App\Presentation\Http\Controllers\Admin\RoleController::class, 'update']);
    Route::post('admin/roles/{role}/permissions', [\App\Presentation\Http\Controllers\Admin\RoleController::class, 'syncPermissions']);
    Route::delete('admin/roles/{role}', [\App\Presentation\Http\Controllers\Admin\RoleController::class, 'destroy']);
});

Route::middleware(['auth:sanctum', 'permission:permissions.manage'])->group(function () {
    Route::get('admin/permissions', [\App\Presentation\Http\Controllers\Admin\PermissionController::class, 'index']);
    Route::post('admin/permissions', [\App\Presentation\Http\Controllers\Admin\PermissionController::class, 'store']);
    Route::patch('admin/permissions/{permission}', [\App\Presentation\Http\Controllers\Admin\PermissionController::class, 'update']);
    Route::delete('admin/permissions/{permission}', [\App\Presentation\Http\Controllers\Admin\PermissionController::class, 'destroy']);
});

Route::middleware(['auth:sanctum', 'permission:users.manage'])->group(function () {
    Route::post('admin/users/{user}/roles', [\App\Presentation\Http\Controllers\Admin\UserRoleController::class, 'assign']);
    Route::delete('admin/users/{user}/roles/{role}', [\App\Presentation\Http\Controllers\Admin\UserRoleController::class, 'revoke']);
    Route::get('admin/users/{user}/roles', [\App\Presentation\Http\Controllers\Admin\UserRoleController::class, 'show']);
    Route::delete('admin/users/{user}', [\App\Presentation\Http\Controllers\Admin\UserController::class, 'destroy']);
});

Route::middleware(['auth:sanctum', 'app.role:staff'])->group(function () {
    // FAQ Management (Staff - Assigned FAQs only)
    Route::get('staff/faqs', [StaffFaqController::class, 'index']);
    Route::get('staff/faqs/{faq}', [StaffFaqController::class, 'show']);
    Route::patch('staff/faqs/{faq}', [StaffFaqController::class, 'update']);
});

Route::get('universities', [PublicUniversityController::class, 'index']);
Route::get('universities/{university}', [PublicUniversityController::class, 'show']);
Route::get('courses', [PublicCourseController::class, 'index']);
Route::get('courses/{course}', [PublicCourseController::class, 'show']);
Route::post('pre-application-inquiries', [PreApplicationInquiryController::class, 'store']);
Route::get('faqs', [PublicFaqController::class, 'index']);

Route::fallback(function () {
    return response()->json(['message' => 'Not Found'], 404);
});
