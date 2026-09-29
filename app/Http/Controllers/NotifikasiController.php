<?php

namespace App\Http\Controllers;

use App\Support\AdminNotif;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotifikasiController extends Controller
{
    private function q()
    {
        AdminNotif::ensureTable();

        return DB::table('admin_notifications');
    }

    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json([
            'count' => $this->q()->where('is_read', false)->count(),
        ]);
    }

    public function list(Request $request): JsonResponse
    {
        $items = $this->q()
            ->orderByDesc('created_at')
            ->limit(15)
            ->get();

        return response()->json(['items' => $items]);
    }

    public function markRead(Request $request, $id): JsonResponse
    {
        $this->q()->where('id', $id)->update([
            'is_read' => true,
            'updated_at' => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $this->q()->where('is_read', false)->update([
            'is_read' => true,
            'updated_at' => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    public function userUnreadCount(Request $request): JsonResponse
    {
        return response()->json(['count' => 0]);
    }

    public function userList(Request $request): JsonResponse
    {
        $notifs = app(PresensiSholatController::class)->buildNotifData();

        $items = [];
        foreach ($notifs as $i => $n) {
            $items[] = [
                'id' => 'user-' . $i,
                'type' => $n['type'] ?? 'success',
                'title' => $n['title'] ?? 'Notifikasi',
                'text' => $n['text'] ?? '',
                'created_at' => null,
                'is_read' => false,
                'url' => route('panel-saya.index'),
            ];
        }

        return response()->json(['items' => $items]);
    }
}