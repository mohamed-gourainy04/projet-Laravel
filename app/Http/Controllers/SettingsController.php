<?php

// app/Http/Controllers/SettingsController.php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        return view('settings.edit', compact('user'));
    }
    
    public function update(Request $request)
    {
        $user = Auth::user();
        
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
        
        $user->update([
            'nom' => $request->nom,
            'email' => $request->email,
        ]);
        
        return redirect()->route('settings.edit')
            ->with('success', 'Paramètres mis à jour avec succès.');
    }
    
    public function updatePassword(Request $request)
    {
        $user = Auth::user();
        
        $this->validate($request, [
            'current_password' => ['required', function ($attribute, $value, $fail) use ($user) {
                if (!Hash::check($value, $user->mot_de_passe)) {
                    $fail('Le mot de passe actuel est incorrect.');
                }
            }],
            'mot_de_passe' => 'required|string|min:8|confirmed|different:current_password',
        ]);
        
        $user->update([
            'mot_de_passe' => Hash::make($request->mot_de_passe),
        ]);
        
        return redirect()->route('settings.edit')
            ->with('success', 'Mot de passe mis à jour avec succès.');
    }
    
    public function notifications()
    {
        $user = Auth::user();
        return view('settings.notifications', compact('user'));
    }
    
    public function updateNotifications(Request $request)
    {
        $user = Auth::user();
        
        $this->validate($request, [
            'email_notifications' => 'boolean',
            'browser_notifications' => 'boolean',
        ]);
        
        $user->update([
            'email_notifications' => $request->has('email_notifications'),
            'browser_notifications' => $request->has('browser_notifications'),
        ]);
        
        return redirect()->route('settings.notifications')
            ->with('success', 'Préférences de notifications mises à jour avec succès.');
    }
}