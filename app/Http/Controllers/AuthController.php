<?php
// app/Http/Controllers/AuthController.php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }
    
    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        
        if (Auth::attempt(['email' => $credentials['email'], 'mot_de_passe' => $credentials['password']])) {
            $request->session()->regenerate();
            
            return redirect()->intended('dashboard');
        }
        
        return back()->withErrors([
            'email' => 'Les identifiants fournis ne correspondent pas à nos enregistrements.',
        ])->onlyInput('email');
    }
    
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }
    
    public function register()
    {
        return view('auth.register');
    }
    
    public function store(Request $request)
    {
        $this->validate($request, [
            'nom' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'mot_de_passe' => 'required|string|min:8|confirmed',
        ]);
        
        $user = User::create([
            'nom' => $request->nom,
            'email' => $request->email,
            'mot_de_passe' => Hash::make($request->mot_de_passe),
            'role' => 'Employé', // Par défaut, les nouveaux utilisateurs sont des employés
            'date_inscription' => now(),
        ]);
        
        Auth::login($user);
        
        return redirect()->route('dashboard');
    }
    
    public function forgotPassword()
    {
        return view('auth.forgot-password');
    }
    
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users',
        ]);
        
        // Ici, vous implémenteriez la logique d'envoi d'email pour la réinitialisation
        // du mot de passe, mais cette fonctionnalité nécessite généralement un service 
        // de messagerie configuré
        
        return back()->with('status', 'Un lien de réinitialisation a été envoyé à votre adresse email.');
    }
}