<?php
// app/Http/Controllers/DashboardController.php
namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Statistiques globales visibles par tous
        $stats = [
            'total_tickets' => Ticket::count(),
            'tickets_ouverts' => Ticket::whereIn('statut', ['Ouvert', 'En cours'])->count(),
            'tickets_resolus' => Ticket::where('statut', 'Résolu')->count(),
        ];
        
        // Statistiques spécifiques selon le rôle
        if ($user->isAdmin()) {
            // Statistiques pour les administrateurs
            $tickets_par_priorite = Ticket::select('priorite', DB::raw('count(*) as total'))
                ->groupBy('priorite')
                ->get()
                ->pluck('total', 'priorite')
                ->toArray();
                
            $tickets_par_statut = Ticket::select('statut', DB::raw('count(*) as total'))
                ->groupBy('statut')
                ->get()
                ->pluck('total', 'statut')
                ->toArray();
                
            $tickets_recents = Ticket::with(['employe', 'technicien'])
                ->orderBy('date_creation', 'desc')
                ->limit(5)
                ->get();
                
            $tickets_critiques = Ticket::where('priorite', 'Critique')
                ->whereIn('statut', ['Ouvert', 'En cours'])
                ->with(['employe', 'technicien'])
                ->orderBy('date_creation', 'asc')
                ->get();
                
            $tickets_sans_technicien = Ticket::whereNull('id_technicien')
                ->whereIn('statut', ['Ouvert'])
                ->with(['employe'])
                ->orderBy('date_creation', 'asc')
                ->get();
                
            // Temps moyen de résolution par technicien
            $techniciens = User::where('role', 'Technicien')->get();
            $performance_techniciens = [];
            
            foreach ($techniciens as $technicien) {
                $tickets_resolus = Ticket::where('id_technicien', $technicien->id)
                    ->where('statut', 'Résolu')
                    ->get();
                    
                $total_temps = 0;
                $count = 0;
                
                foreach ($tickets_resolus as $ticket) {
                    $date_creation = new \DateTime($ticket->date_creation);
                    $date_resolution = new \DateTime($ticket->date_mise_a_jour);
                    $interval = $date_creation->diff($date_resolution);
                    $total_temps += $interval->days * 24 + $interval->h;
                    $count++;
                }
                
                $performance_techniciens[$technicien->id] = [
                    'nom' => $technicien->nom,
                    'tickets_resolus' => $count,
                    'temps_moyen' => $count > 0 ? round($total_temps / $count, 2) : 0,
                ];
            }
                
            return view('dashboard.admin', compact(
                'stats', 
                'tickets_par_priorite', 
                'tickets_par_statut', 
                'tickets_recents', 
                'tickets_critiques',
                'tickets_sans_technicien',
                'performance_techniciens'
            ));
        } 
        elseif ($user->isTechnician()) {
            // Statistiques pour les techniciens
            $mes_tickets = Ticket::where('id_technicien', $user->id)
                ->with(['employe'])
                ->orderBy('priorite', 'desc')
                ->orderBy('date_creation', 'asc')
                ->get();
                
            $tickets_ouverts = Ticket::whereNull('id_technicien')
                ->where('statut', 'Ouvert')
                ->with(['employe'])
                ->orderBy('priorite', 'desc')
                ->orderBy('date_creation', 'asc')
                ->get();
                
            $mes_tickets_par_statut = Ticket::where('id_technicien', $user->id)
                ->select('statut', DB::raw('count(*) as total'))
                ->groupBy('statut')
                ->get()
                ->pluck('total', 'statut')
                ->toArray();
                
            $mes_tickets_par_priorite = Ticket::where('id_technicien', $user->id)
                ->select('priorite', DB::raw('count(*) as total'))
                ->groupBy('priorite')
                ->get()
                ->pluck('total', 'priorite')
                ->toArray();
                
            return view('dashboard.technicien', compact(
                'stats', 
                'mes_tickets', 
                'tickets_ouverts', 
                'mes_tickets_par_statut',
                'mes_tickets_par_priorite'
            ));
        }
        else {
            // Statistiques pour les employés
            $mes_tickets = Ticket::where('id_employe', $user->id)
                ->with(['technicien'])
                ->orderBy('date_creation', 'desc')
                ->get();
                
            $mes_tickets_par_statut = Ticket::where('id_employe', $user->id)
                ->select('statut', DB::raw('count(*) as total'))
                ->groupBy('statut')
                ->get()
                ->pluck('total', 'statut')
                ->toArray();
                
            return view('dashboard.employe', compact(
                'stats', 
                'mes_tickets', 
                'mes_tickets_par_statut'
            ));
        }
    }
}