<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\CommentaireController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RapportController;
use App\Http\Controllers\SettingsController;

// 🔒 Authentification
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// 🏠 Dashboard (Accès protégé)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 📌 Gestion des utilisateurs
    Route::resource('users', UserController::class);

    // 🎫 Gestion des tickets
    Route::resource('tickets', TicketController::class);

    // 💬 Gestion des commentaires
    Route::resource('commentaires', CommentaireController::class)->except(['create', 'edit']);

    // 🔔 Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');

    // 📊 Rapports
    Route::get('/rapports', [RapportController::class, 'index'])->name('rapports.index');

    // ⚙️ Paramètres
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/update', [SettingsController::class, 'update'])->name('settings.update');

    // 📌 Gestion des relations entre modèles

    // 🔹 Tickets d'un utilisateur spécifique
    Route::get('/users/{user}/tickets', [UserController::class, 'tickets'])->name('users.tickets');

    // 🔹 Tickets assignés à un technicien spécifique
    Route::get('/techniciens/{user}/tickets', [UserController::class, 'assignedTickets'])->name('techniciens.tickets');

    // 🔹 Commentaires sur un ticket spécifique
    Route::get('/tickets/{ticket}/commentaires', [TicketController::class, 'commentaires'])->name('tickets.commentaires');

    // 🔹 Notifications d'un utilisateur
    Route::get('/users/{user}/notifications', [UserController::class, 'notifications'])->name('users.notifications');
});
