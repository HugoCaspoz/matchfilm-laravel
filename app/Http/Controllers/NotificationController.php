<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $notifications = Notification::where('user_id', Auth::id())
                                    ->with('fromUser')
                                    ->orderBy('created_at', 'desc')
                                    ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead($id)
    {
        $notification = Notification::where('id', $id)
                                   ->where('user_id', Auth::id())
                                   ->firstOrFail();

        $notification->read = true;
        $notification->save();

        return redirect()->back();
    }

    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
                   ->where('read', false)
                   ->update(['read' => true]);

        return redirect()->back();
    }

    public function getUnreadCount()
    {
        $count = Notification::where('user_id', Auth::id())
                            ->where('read', false)
                            ->count();

        return response()->json(['count' => $count]);
    }
}
