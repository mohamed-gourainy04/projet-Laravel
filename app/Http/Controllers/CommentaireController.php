<?php

// app/Http/Controllers/CommentaireController.php
namespace App\Http\Controllers;

use App\Models\Commentaire;
use App\Models\Ticket;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentaireController extends Controller
{
    public function store(Request $request, Ticket $ticket)
    {
        $this->validate($request, [
            'contenu' => 'required',
        ]);

        $commentaire = Commentaire::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'contenu' => $request->contenu,
            'date_creation' => now(),
        ]);

        // Mettre à jour la date de mise à jour du ticket
        $ticket->update([
            'date_mise_a_jour' => now(),
        ]);

        // Créer une notification pour l'employé si le commentaire est ajouté par un technicien
        if (Auth::user()->role === 'Technicien' && Auth::id() !== $ticket->id_employe) {
            Notification::create([
                'user_id' => $ticket->id_employe,
                'ticket_id' => $ticket->id,
                'message' => "Nouveau commentaire sur votre ticket: " . $ticket->titre,
                'lu' => false,
            ]);
        }

        // Créer une notification pour le technicien si le commentaire est ajouté par l'employé
        if (Auth::user()->role === 'Employé' && $ticket->id_technicien && Auth::id() !== $ticket->id_technicien) {
            Notification::create([
                'user_id' => $ticket->id_technicien,
                'ticket_id' => $ticket->id,
                'message' => "Nouveau commentaire sur le ticket: " . $ticket->titre,
                'lu' => false,
            ]);
        }

        return redirect()->route('tickets.show', $ticket->id)
            ->with('success', 'Commentaire ajouté avec succès.');
    }
    
    public function destroy(Commentaire $commentaire)
    {
        // Vérifier les permissions
        $user = Auth::user();
        if (!$user->isAdmin() && $user->id !== $commentaire->user_id) {
            return redirect()->route('tickets.show', $commentaire->ticket_id)
                ->with('error', 'Vous n\'avez pas les permissions nécessaires pour supprimer ce commentaire.');
        }
        
        $ticket_id = $commentaire->ticket_id;
        $commentaire->delete();
        
        return redirect()->route('tickets.show', $ticket_id)
            ->with('success', 'Commentaire supprimé avec succès.');
    }
    
    public function edit(Commentaire $commentaire)
    {
        // Vérifier les permissions
        $user = Auth::user();
        if (!$user->isAdmin() && $user->id !== $commentaire->user_id) {
            return redirect()->route('tickets.show', $commentaire->ticket_id)
                ->with('error', 'Vous n\'avez pas les permissions nécessaires pour modifier ce commentaire.');
        }
        
        $ticket = Ticket::find($commentaire->ticket_id);
        return view('commentaires.edit', compact('commentaire', 'ticket'));
    }
    
    public function update(Request $request, Commentaire $commentaire)
    {
        $this->validate($request, [
            'contenu' => 'required',
        ]);
        
        // Vérifier les permissions
        $user = Auth::user();
        if (!$user->isAdmin() && $user->id !== $commentaire->user_id) {
            return redirect()->route('tickets.show', $commentaire->ticket_id)
                ->with('error', 'Vous n\'avez pas les permissions nécessaires pour modifier ce commentaire.');
        }
        
        $commentaire->update([
            'contenu' => $request->contenu,
            'date_creation' => now(),
        ]);
        
        return redirect()->route('tickets.show', $commentaire->ticket_id)
            ->with('success', 'Commentaire mis à jour avec succès.');
    }
}