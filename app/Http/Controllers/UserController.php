<?php

// app/Http/Controllers/UserController.php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct()
    {
        // Seuls les administrateurs peuvent accéder à ces fonctionnalités
        $this->middleware('admin')->except(['show', 'edit', 'update', 'profile']);
    }
    
    public function index()
    {
        $users = User::all();
        return view('users.index', compact('users'));
    }
    
    public function create()
    {
        return view('users.create');
    }
    
    public function store(Request $request)
    {
        $this->validate($request, [
            'nom' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'mot_de_passe' => 'required|string|min:8|confirmed',
            'role' => 'required|in:Employé,Technicien,Admin',
        ]);
        
        $user = User::create([
            'nom' => $request->nom,
            'email' => $request->email,
            'mot_de_passe' => Hash::make($request->mot_de_passe),
            'role' => $request->role,
            'date_inscription' => now(),
        ]);
        
        return redirect()->route('users.index')
            ->with('success', 'Utilisateur créé avec succès.');
    }
    
    public function show(User $user)
    {
        // L'admin peut voir tous les utilisateurs
        // Les autres utilisateurs ne peuvent voir que leur propre profil
        if (!Auth::user()->isAdmin() && Auth::id() !== $user->id) {
            return redirect()->route('dashboard')
                ->with('error', 'Vous n\'avez pas les permissions nécessaires.');
        }
        
        // Pour les techniciens, montrer leurs tickets assignés
        if ($user->role === 'Technicien') {
            $tickets = Ticket::where('id_technicien', $user->id)
                ->orderBy('date_creation', 'desc')
                ->get();
        }
        // Pour les employés, montrer leurs tickets créés
        elseif ($user->role === 'Employé') {
            $tickets = Ticket::where('id_employe', $user->id)
                ->orderBy('date_creation', 'desc')
                ->get();
        }
        else {
            $tickets = collect(); // Collection vide pour les admins
        }
        
        return view('users.show', compact('user', 'tickets'));
    }
    
    public function edit(User $user)
    {
        // L'admin peut modifier tous les utilisateurs
        // Les autres utilisateurs ne peuvent modifier que leur propre profil
        if (!Auth::user()->isAdmin() && Auth::id() !== $user->id) {
            return redirect()->route('dashboard')
                ->with('error', 'Vous n\'avez pas les permissions nécessaires.');
        }
        
        return view('users.edit', compact('user'));
    }
    
    public function update(Request $request, User $user)
    {
        // L'admin peut modifier tous les utilisateurs
        // Les autres utilisateurs ne peuvent modifier que leur propre profil
        if (!Auth::user()->isAdmin() && Auth::id() !== $user->id) {
            return redirect()->route('dashboard')
                ->with('error', 'Vous n\'avez pas les permissions nécessaires.');
        }
        
        $this->validate($request, [
            'nom' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
        ]);
        
        $userData = [
            'nom' => $request->nom,
            'email' => $request->email,
        ];
        
        // Seul l'admin peut changer le rôle
        if (Auth::user()->isAdmin() && $request->has('role')) {
            $this->validate($request, [
                'role' => 'required|in:Employé,Technicien,Admin',
            ]);
            $userData['role'] = $request->role;
        }
        
        // Mise à jour du mot de passe si fourni
        if ($request->filled('mot_de_passe')) {
            $this->validate($request, [
                'mot_de_passe' => 'string|min:8|confirmed',
            ]);
            $userData['mot_de_passe'] = Hash::make($request->mot_de_passe);
        }
        
        $user->update($userData);
        
        if (Auth::id() === $user->id) {
            return redirect()->route('profile')
                ->with('success', 'Profil mis à jour avec succès.');
        }
        
        return redirect()->route('users.index')
            ->with('success', 'Utilisateur mis à jour avec succès.');
    }
    
    public function destroy(User $user)
    {
        // Vérifier si l'utilisateur a des tickets associés
        $ticketsAsEmployee = Ticket::where('id_employe', $user->id)->count();
        $ticketsAsTechnician = Ticket::where('id_technicien', $user->id)->count();
        
        if ($ticketsAsEmployee > 0 || $ticketsAsTechnician > 0) {
            return redirect()->route('users.index')
                ->with('error', 'Impossible de supprimer cet utilisateur car il a des tickets associés.');
        }
        
        $user->delete();
        
        return redirect()->route('users.index')
            ->with('success', 'Utilisateur supprimé avec succès.');
    }
    
    public function profile()
    {
        $user = Auth::user();
        return redirect()->route('users.show', $user->id);
    }
}