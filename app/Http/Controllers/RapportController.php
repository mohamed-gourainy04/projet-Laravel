<?php
// app/Http/Controllers/RapportController.php
namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RapportController extends Controller
{
    public function __construct()
    {
        // Seuls les administrateurs peuvent accéder aux rapports
        $this->middleware('admin');
    }
    
    public function index()
    {
        return view('rapports.index');
    }
    
    public function ticketsParStatut(Request $request)
    {
        $periode = $request->input('periode', 'semaine');
        $date_debut = $this->getDateDebut($periode);
        
        $tickets = Ticket::where('date_creation', '>=', $date_debut)
            ->select('statut', DB::raw('count(*) as total'))
            ->groupBy('statut')
            ->get();
            
        return view('rapports.tickets-par-statut', compact('tickets', 'periode'));
    }
    
    public function ticketsParPriorite(Request $request)
    {
        $periode = $request->input('periode', 'semaine');
        $date_debut = $this->getDateDebut($periode);
        
        $tickets = Ticket::where('date_creation', '>=', $date_debut)
            ->select('priorite', DB::raw('count(*) as total'))
            ->groupBy('priorite')
            ->get();
            
        return view('rapports.tickets-par-priorite', compact('tickets', 'periode'));
    }
    
    public function tempsResolution(Request $request)
    {
        $periode = $request->input('periode', 'semaine');
        $date_debut = $this->getDateDebut($periode);
        
        $techniciens = User::where('role', 'Technicien')->get();
        $resultats = [];
        
        foreach ($techniciens as $technicien) {
            $tickets_resolus = Ticket::where('id_technicien', $technicien->id)
                ->where('statut', 'Résolu')
                ->where('date_mise_a_jour', '>=', $date_debut)
                ->get();
                
            $total_tickets = $tickets_resolus->count();
            $total_heures = 0;
            
            foreach ($tickets_resolus as $ticket) {
                $date_creation = new \DateTime($ticket->date_creation);
                $date_resolution = new \DateTime($ticket->date_mise_a_jour);
                $interval = $date_creation->diff($date_resolution);
                $heures = $interval->days * 24 + $interval->h;
                $total_heures += $heures;
            }
            
            $temps_moyen = $total_tickets > 0 ? round($total_heures / $total_tickets, 2) : 0;
            
            $resultats[] = [
                'id' => $technicien->id,
                'nom' => $technicien->nom,
                'total_tickets' => $total_tickets,
                'temps_moyen' => $temps_moyen
            ];
        }
        
        // Trier par temps moyen de résolution
        usort($resultats, function($a, $b) {
            if ($a['total_tickets'] == 0) return 1;
            if ($b['total_tickets'] == 0) return -1;
            return $a['temps_moyen'] - $b['temps_moyen'];
        });
        
        return view('rapports.temps-resolution', compact('resultats', 'periode'));
    }
    
    public function activiteParJour(Request $request)
    {
        $date_debut = Carbon::now()->subDays(30);
        
        $tickets_par_jour = Ticket::where('date_creation', '>=', $date_debut)
            ->select(DB::raw('DATE(date_creation) as jour'), DB::raw('count(*) as total'))
            ->groupBy('jour')
            ->orderBy('jour')
            ->get();
            
        return view('rapports.activite-par-jour', compact('tickets_par_jour'));
    }
    
    public function chargeParTechnicien()
    {
        $techniciens = User::where('role', 'Technicien')->get();
        $resultats = [];
        
        foreach ($techniciens as $technicien) {
            $tickets_ouverts = Ticket::where('id_technicien', $technicien->id)
                ->whereIn('statut', ['Ouvert', 'En cours'])
                ->count();
                
            $tickets_resolus = Ticket::where('id_technicien', $technicien->id)
                ->where('statut', 'Résolu')
                ->count();
                
            $tickets_fermes = Ticket::where('id_technicien', $technicien->id)
                ->where('statut', 'Fermé')
                ->count();
                
            $tickets_critiques = Ticket::where('id_technicien', $technicien->id)
                ->where('priorite', 'Critique')
                ->whereIn('statut', ['Ouvert', 'En cours'])
                ->count();
                
            $resultats[] = [
                'id' => $technicien->id,
                'nom' => $technicien->nom,
                'tickets_ouverts' => $tickets_ouverts,
                'tickets_resolus' => $tickets_resolus,
                'tickets_fermes' => $tickets_fermes,
                'tickets_critiques' => $tickets_critiques,
                'charge_totale' => $tickets_ouverts + ($tickets_critiques * 2) // Les tickets critiques comptent double
            ];
        }
        
        // Trier par charge de travail
        usort($resultats, function($a, $b) {
            return $b['charge_totale'] - $a['charge_totale'];
        });
        
        return view('rapports.charge-par-technicien', compact('resultats'));
    }
    
    public function exportCsv(Request $request)
    {
        $type = $request->input('type', 'tous');
        $periode = $request->input('periode', 'mois');
        $date_debut = $this->getDateDebut($periode);
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="rapport-tickets-' . $type . '-' . date('Y-m-d') . '.csv"',
        ];
        
        $query = Ticket::where('date_creation', '>=', $date_debut);
        
        if ($type != 'tous') {
            $query->where('statut', ucfirst($type));
        }
        
        $tickets = $query->with(['employe', 'technicien'])->get();
        
        $callback = function() use ($tickets) {
            $file = fopen('php://output', 'w');
            
            // En-têtes CSV
            fputcsv($file, [
                'ID', 
                'Titre', 
                'Description', 
                'Statut', 
                'Priorité', 
                'Date de création', 
                'Date de mise à jour', 
                'Employé', 
                'Technicien'
            ]);
            
            foreach ($tickets as $ticket) {
                fputcsv($file, [
                    $ticket->id,
                    $ticket->titre,
                    $ticket->description,
                    $ticket->statut,
                    $ticket->priorite,
                    $ticket->date_creation,
                    $ticket->date_mise_a_jour,
                    $ticket->employe ? $ticket->employe->nom : 'N/A',
                    $ticket->technicien ? $ticket->technicien->nom : 'Non assigné'
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    private function getDateDebut($periode)
    {
        switch ($periode) {
            case 'jour':
                return Carbon::now()->startOfDay();
            case 'semaine':
                return Carbon::now()->startOfWeek();
            case 'mois':
                return Carbon::now()->startOfMonth();
            case 'trimestre':
                return Carbon::now()->startOfQuarter();
            case 'annee':
                return Carbon::now()->startOfYear();
            default:
                return Carbon::now()->subDays(7);
        }
    }
}