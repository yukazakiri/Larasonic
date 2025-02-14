<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\User\OauthController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\User\LoginLinkController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\TeamMemberPointsController;

Route::get('/', [WelcomeController::class, 'home'])->name('home');

Route::prefix('auth')->group(
    function () {
        // OAuth
        Route::get('/redirect/{provider}', [OauthController::class, 'redirect'])->name('oauth.redirect');
        Route::get('/callback/{provider}', [OauthController::class, 'callback'])->name('oauth.callback');
        // Magic Link
        Route::middleware('throttle:login-link')->group(function () {
            Route::post('/login-link', [LoginLinkController::class, 'store'])->name('login-link.store');
            Route::get('/login-link/{token}', [LoginLinkController::class, 'login'])
                ->name('login-link.login')
                ->middleware('signed');
        });
    }
);

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::delete('/auth/destroy/{provider}', [OauthController::class, 'destroy'])->name('oauth.destroy');

    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');

    Route::resource('/subscriptions', SubscriptionController::class)
        ->names('subscriptions')
        ->only(['index', 'create', 'store', 'show']);

    Route::resource('exams', ExamController::class);

    Route::get('/exams/{exam}/builder', [ExamController::class, 'builder'])
        ->name('exams.builder');

    Route::get('/exams/{exam}/options', [ExamController::class, 'options'])
        ->name('exams.options');

    Route::put('/exams/{exam}/publish', [ExamController::class, 'publish'])->name('exams.publish');

    Route::delete('/exams/{exam}', [ExamController::class, 'destroy'])
        ->name('exams.destroy')
        ->where('exam', '[0-9]+');

    Route::get('/exams/{exam}/print', [ExamController::class, 'print'])->name('exams.print');

    Route::get('/user-dashboard', UserDashboardController::class)->name('user-dashboard');

    Route::resource('schedules', ScheduleController::class);

    Route::resource('activities', ActivityController::class);
    Route::post('activities/{activity}/assign-points', [ActivityController::class, 'assignPoints'])
        ->name('activities.assign-points');
    Route::post('activities/{activity}/groups', [ActivityController::class, 'manageGroups'])
        ->name('activities.groups.manage');
    Route::put('activities/{activity}/members/{member}/points', [ActivityController::class, 'updateMemberPoints'])
        ->name('activities.members.points');

    Route::get('/teams/{team}/member-points', TeamMemberPointsController::class)
        ->name('teams.member-points')
        ->middleware(['auth', 'verified']);
});
