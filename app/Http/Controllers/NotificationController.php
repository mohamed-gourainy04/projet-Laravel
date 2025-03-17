<?php
// app/Http/Controllers/RapportController.php
namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('notifications.index', compact('notifications'));
    }
    
    public function markAsRead(Notification $notification)
    {
        // Vérifier que la notification appartient à l'utilisateur connecté
        if ($notification->user_id !== Auth::id()) {
            return redirect()->route('notifications.index')
                ->with('error', 'Vous n\'avez pas les permissions nécessaires.');
        }
        
        $notification->update([
            'lu' => true
        ]);
        
        return redirect()->back()
            ->with('success', 'Notification marquée comme lue.');
    }
    
    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->where('lu', false)
            ->update(['lu' => true]);
            
        return redirect()->route('notifications.index')
            ->with('success', 'Toutes les notifications ont été marquées comme lues.');
    }
    
    public function destroy(Notification $notification)
    {
        // Vérifier que la notification appartient à l'utilisateur connecté
        if ($notification->user_id !== Auth::id()) {
            return redirect()->route('notifications.index')
                ->with('error', 'Vous n\'avez pas les permissions nécessaires.');
        }
        
        $notification->delete();
        
        return redirect()->route('notifications.index')
            ->with('success', 'Notification supprimée avec succès.');
    }
    
    public function destroyAll()
    {
        Notification::where('user_id', Auth::id())->delete();
        
        return redirect()->route('notifications.index')
            ->with('success', 'Toutes les notifications ont été supprimées.');
    }
    
    public function unreadCount()
    {
        $count = Notification::where('user_id', Auth::id())
            ->where('lu', false)
            ->count();
            
        return response()->json(['count' => $count]);
    }
    
    public function getLatest()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->where('lu', false)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        return response()->json([
            'notifications' => $notifications,
            'count' => $notifications->count()
        ]);
    }
}