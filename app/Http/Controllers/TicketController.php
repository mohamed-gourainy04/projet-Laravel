<?php

// app/Http/Controllers/TicketController.php
namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        if ($user->isAdmin()) {
            $tickets = Ticket::with(['employe', 'technicien'])->orderBy('date_creation', 'desc')->get();
        } elseif ($user->isTechnician()) {
            $tickets = Ticket::where('id_technicien', $user->id)
                ->orWhereNull('id_technicien')
                ->with(['employe', 'technicien'])
                ->orderBy('date_creation', 'desc')
                ->get();
        } else {
            $tickets = Ticket::where('id_employe', $user->id)
                ->with(['employe', 'technicien'])
                ->orderBy('date_creation', 'desc')
                ->get();
        }
        
        return view('tickets.index', compact('tickets'));
    }

    public function create()
    {
        return view('tickets.create');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'titre' => 'required|max:255',
            'description' => 'required',
            'priorite' => 'required|in:Faible,Moyenne,Élevée,Critique',
        ]);

        $ticket = Ticket::create([
            'titre' => $request->titre,
            'description' => $request->description,
            'priorite' => $request->priorite,
            'id_employe' => Auth::id(),
            'statut' => 'Ouvert',
            'date_creation' => now(),
            'date_mise_a_jour' => now(),
        ]);

        // Notification pour les techniciens qu'un nouveau ticket est créé
        $techniciens = User::where('role', 'Technicien')->get();
        foreach ($techniciens as $technicien) {
            Notification::create([
                'user_id' => $technicien->id,
                'ticket_id' => $ticket->id,
                'message' => "Un nouveau ticket a été créé: " . $ticket->titre,
                'lu' => false,
            ]);
        }

        return redirect()->route('tickets.show', $ticket->id)
            ->with('success', 'Ticket créé avec succès.');
    }

    public function show(Ticket $ticket)
    {
        $ticket->load(['employe', 'technicien', 'commentaires.user']);
        $techniciens = User::where('role', 'Technicien')->get();
        
        return view('tickets.show', compact('ticket', 'techniciens'));
    }

    public function edit(Ticket $ticket)
    {
        // Vérifier les permissions
        $user = Auth::user();
        if (!$user->isAdmin() && $user->id !== $ticket->id_technicien && $user->id !== $ticket->id_employe) {
            return redirect()->route('tickets.index')
                ->with('error', 'Vous n\'avez pas les permissions nécessaires pour modifier ce ticket.');
        }
        
        $techniciens = User::where('role', 'Technicien')->get();
        return view('tickets.edit', compact('ticket', 'techniciens'));
    }

    public function update(Request $request, Ticket $ticket)
    {
        $this->validate($request, [
            'titre' => 'required|max:255',
            'description' => 'required',
            'statut' => 'required|in:Ouvert,En cours,Résolu,Fermé',
            'priorite' => 'required|in:Faible,Moyenne,Élevée,Critique',
            'id_technicien' => 'nullable|exists:users,id',
        ]);

        $oldStatus = $ticket->statut;
        $oldTechnicien = $ticket->id_technicien;

        $ticket->update([
            'titre' => $request->titre,
            'description' => $request->description,
            'statut' => $request->statut,
            'priorite' => $request->priorite,
            'id_technicien' => $request->id_technicien,
            'date_mise_a_jour' => now(),
        ]);

        // Si le statut a changé, créer une notification pour l'employé
        if ($oldStatus != $request->statut) {
            Notification::create([
                'user_id' => $ticket->id_employe,
                'ticket_id' => $ticket->id,
                'message' => "Le statut de votre ticket a été mis à jour en: " . $request->statut,
                'lu' => false,
            ]);
        }

        // Si un nouveau technicien est assigné, créer une notification pour lui
        if ($oldTechnicien != $request->id_technicien && $request->id_technicien) {
            Notification::create([
                'user_id' => $request->id_technicien,
                'ticket_id' => $ticket->id,
                'message' => "Vous avez été assigné à un nouveau ticket: " . $ticket->titre,
                'lu' => false,
            ]);
        }

        return redirect()->route('tickets.show', $ticket->id)
            ->with('success', 'Ticket mis à jour avec succès.');
    }

    public function destroy(Ticket $ticket)
    {
        // Vérifier les permissions
        $user = Auth::user();
        if (!$user->isAdmin()) {
            return redirect()->route('tickets.index')
                ->with('error', 'Seul un administrateur peut supprimer un ticket.');
        }
        
        $ticket->delete();
        return redirect()->route('tickets.index')
            ->with('success', 'Ticket supprimé avec succès.');
    }

    public function assignTechnician(Request $request, Ticket $ticket)
    {
        $this->validate($request, [
            'id_technicien' => 'required|exists:users,id',
        ]);

        $ticket->update([
            'id_technicien' => $request->id_technicien,
            'statut' => 'En cours',
            'date_mise_a_jour' => now(),
        ]);

        Notification::create([
            'user_id' => $request->id_technicien,
            'ticket_id' => $ticket->id,
            'message' => "Vous avez été assigné à un nouveau ticket: " . $ticket->titre,
            'lu' => false,
        ]);

        return redirect()->route('tickets.show', $ticket->id)
            ->with('success', 'Technicien assigné avec succès.');
    }
    
    public function changeStatus(Request $request, Ticket $ticket)
    {
        $this->validate($request, [
            'statut' => 'required|in:Ouvert,En cours,Résolu,Fermé',
        ]);
        
        $ticket->update([
            'statut' => $request->statut,
            'date_mise_a_jour' => now(),
        ]);
        
        // Notification pour l'employé
        Notification::create([
            'user_id' => $ticket->id_employe,
            'ticket_id' => $ticket->id,
            'message' => "Le statut de votre ticket a été mis à jour en: " . $request->statut,
            'lu' => false,
        ]);
        
        return redirect()->route('tickets.show', $ticket->id)
            ->with('success', 'Statut du ticket mis à jour avec succès.');
    }
    
    public function dashboard()
    {
        // Vérifier les permissions
        $user = Auth::user();
        if (!$user->isAdmin()) {
            return redirect()->route('tickets.index')
                ->with('error', 'Seul un administrateur peut accéder au tableau de bord.');
        }
        
        // Statistiques pour le tableau de bord
        $stats = [
            'total_ouvert' => Ticket::whereIn('statut', ['Ouvert', 'En cours'])->count(),
            'total_resolu' => Ticket::where('statut', 'Résolu')->count(),
            'total_ferme' => Ticket::where('statut', 'Fermé')->count(),
            'tickets_critiques' => Ticket::where('priorite', 'Critique')->whereIn('statut', ['Ouvert', 'En cours'])->count(),
        ];
        
        // Temps moyen de résolution par technicien
        $techniciens = User::where('role', 'Technicien')->get();
        $temps_resolution = [];
        
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
            
            $temps_resolution[$technicien->id] = [
                'nom' => $technicien->nom,
                'tickets_resolus' => $count,
                'temps_moyen' => $count > 0 ? round($total_temps / $count, 2) : 0,
            ];
        }
        
        return view('tickets.dashboard', compact('stats', 'temps_resolution'));
    }
}