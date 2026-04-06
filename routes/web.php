<?php

use App\Http\Controllers\Admin\KnowledgeBaseArticleController;
use App\Http\Controllers\Auth\LanCoreAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KnowledgeBaseController;
use App\Http\Controllers\Staff\TicketBoardController;
use App\Http\Controllers\TicketAssignmentController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketReplyController;
use App\Http\Controllers\TicketStatusController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

// Landing page — auto-redirects to LanCore SSO when enabled (unless there is a
// flash error, which means the user just returned from a failed SSO attempt).
Route::get('/', function (Request $request) {
    if (! auth()->check() && config('lancore.enabled') && ! $request->session()->has('error')) {
        return redirect()->route('lancore.redirect');
    }

    return inertia('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
        'lanCoreEnabled' => (bool) config('lancore.enabled'),
    ]);
})->name('home');

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
});

// LanCore SSO routes
Route::prefix('auth')->name('auth.')->group(function () {
    Route::get('redirect', [LanCoreAuthController::class, 'redirect'])->name('redirect');
    Route::get('callback', [LanCoreAuthController::class, 'callback'])->name('callback');
    Route::get('status', [LanCoreAuthController::class, 'status'])->name('status');
});

Route::prefix('auth/lancore')->name('lancore.')->group(function () {
    Route::get('redirect', [LanCoreAuthController::class, 'redirect'])->name('redirect');
    Route::get('callback', [LanCoreAuthController::class, 'callback'])->name('callback');
    Route::get('status', [LanCoreAuthController::class, 'status'])->name('status');
});

// Ticket routes (authenticated users)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('tickets/create', [TicketController::class, 'create'])->name('tickets.create');
    Route::post('tickets', [TicketController::class, 'store'])->name('tickets.store');
    Route::get('tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');

    Route::post('tickets/{ticket}/replies', [TicketReplyController::class, 'store'])->name('tickets.replies.store');
    Route::delete('tickets/{ticket}/replies/{reply}', [TicketReplyController::class, 'destroy'])->name('tickets.replies.destroy');

    Route::patch('tickets/{ticket}/status', [TicketStatusController::class, 'update'])->name('tickets.status.update');
});

// Staff routes
Route::middleware(['auth', 'verified', 'staff'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('tickets', [TicketBoardController::class, 'index'])->name('tickets.index');
    Route::patch('tickets/{ticket}/assign', [TicketAssignmentController::class, 'update'])->name('tickets.assign');
    Route::delete('tickets/{ticket}/assign', [TicketAssignmentController::class, 'destroy'])->name('tickets.unassign');
});

// Knowledge base (public read)
Route::get('kb', [KnowledgeBaseController::class, 'index'])->name('kb.index');
Route::get('kb/{article:slug}', [KnowledgeBaseController::class, 'show'])->name('kb.show');

// Admin: Knowledge base management
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin/kb')->name('admin.kb.')->group(function () {
    Route::get('/', [KnowledgeBaseArticleController::class, 'index'])->name('index');
    Route::get('create', [KnowledgeBaseArticleController::class, 'create'])->name('create');
    Route::post('/', [KnowledgeBaseArticleController::class, 'store'])->name('store');
    Route::get('{article}/edit', [KnowledgeBaseArticleController::class, 'edit'])->name('edit');
    Route::patch('{article}', [KnowledgeBaseArticleController::class, 'update'])->name('update');
    Route::delete('{article}', [KnowledgeBaseArticleController::class, 'destroy'])->name('destroy');
});

require __DIR__.'/settings.php';
