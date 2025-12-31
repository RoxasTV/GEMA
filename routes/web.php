<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Public Pilgrim Routes
Route::get('/room/{slug}', [RoomController::class, 'showEntry'])->name('room.entry');
Route::post('/room/{slug}', [RoomController::class, 'processEntry'])->name('room.process');
Route::get('/room/{slug}/live', [RoomController::class, 'showLive'])->name('room.live');
Route::get('/api/room/{slug}/prayer', [RoomController::class, 'getCurrentPrayer'])->name('room.prayer');

// Authenticated Guide Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [GuideController::class, 'dashboard'])->name('dashboard');

    // Room Management
    Route::post('/guide/room', [GuideController::class, 'createRoom'])->name('guide.room.create');
    Route::get('/guide/room/{room}', [GuideController::class, 'showRoom'])->name('guide.room');
    Route::post('/guide/room/{room}/toggle', [GuideController::class, 'toggleRoom'])->name('guide.room.toggle');
    Route::post('/guide/room/{room}/prayer', [GuideController::class, 'updatePrayer'])->name('guide.room.prayer');
    Route::post('/guide/room/{room}/mute', [GuideController::class, 'muteAll'])->name('guide.room.mute');
    Route::post('/guide/room/{room}/unmute', [GuideController::class, 'unmuteAll'])->name('guide.room.unmute');
    Route::delete('/guide/room/{room}', [GuideController::class, 'deleteRoom'])->name('guide.room.delete');
    Route::get('/guide/room/{room}/participants', [GuideController::class, 'getParticipantCount'])->name('guide.room.participants');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
