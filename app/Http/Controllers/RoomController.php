<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Participant;
use App\Services\LiveKitService;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function __construct(
        private LiveKitService $liveKitService
    ) {}

    /**
     * Show pilgrim entry form (gateway page)
     */
    public function showEntry(string $slug)
    {
        $room = Room::where('slug', $slug)->first();

        if (!$room) {
            abort(404, 'Room tidak ditemukan');
        }

        if (!$room->is_active) {
            return view('room.inactive', compact('room'));
        }

        return view('room.login', compact('room'));
    }

    /**
     * Process pilgrim entry
     */
    public function processEntry(Request $request, string $slug)
    {
        $request->validate([
            'pilgrim_id' => 'required|string|max:100',
        ], [
            'pilgrim_id.required' => 'ID Jamaah wajib diisi',
        ]);

        $room = Room::where('slug', $slug)->where('is_active', true)->firstOrFail();

        // Store pilgrim ID in session
        session(['pilgrim_id' => $request->pilgrim_id, 'room_slug' => $slug]);

        // Create participant record
        Participant::create([
            'room_id' => $room->id,
            'pilgrim_id' => $request->pilgrim_id,
            'joined_at' => now(),
        ]);

        return redirect()->route('room.live', $slug);
    }

    /**
     * Show live room interface for pilgrim
     */
    public function showLive(string $slug)
    {
        $pilgrimId = session('pilgrim_id');

        if (!$pilgrimId || session('room_slug') !== $slug) {
            return redirect()->route('room.entry', $slug);
        }

        $room = Room::where('slug', $slug)
            ->where('is_active', true)
            ->with('currentPrayer')
            ->first();

        if (!$room) {
            return redirect()->route('room.entry', $slug)
                ->with('error', 'Room tidak aktif');
        }

        $token = $this->liveKitService->generatePilgrimToken($slug, $pilgrimId);
        $livekitHost = config('services.livekit.ws_url');

        return view('room.live', compact('room', 'token', 'pilgrimId', 'livekitHost'));
    }

    /**
     * API: Get current prayer for room
     */
    public function getCurrentPrayer(string $slug)
    {
        $room = Room::where('slug', $slug)->with('currentPrayer')->firstOrFail();

        return response()->json([
            'prayer' => $room->currentPrayer,
        ]);
    }
}
