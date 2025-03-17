<?php
// app/Models/User.php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'nom', 'email', 'password', 'role', 'date_inscription'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function ticketsCreated()
    {
        return $this->hasMany(Ticket::class, 'id_employe');
    }

    public function ticketsAssigned()
    {
        return $this->hasMany(Ticket::class, 'id_technicien');
    }

    public function commentaires()
    {
        return $this->hasMany(Commentaire::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function isAdmin()
    {
        return $this->role === 'Admin';
    }

    public function isTechnician()
    {
        return $this->role === 'Technicien';
    }

    public function isEmployee()
    {
        return $this->role === 'Employé';
    }
}
