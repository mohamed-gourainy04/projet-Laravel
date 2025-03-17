<?php 

// app/Models/Ticket.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'titre', 'description', 'statut', 'priorite', 
        'date_creation', 'date_mise_a_jour', 'id_employe', 'id_technicien'
    ];

    public function employe()
    {
        return $this->belongsTo(User::class, 'id_employe');
    }

    public function technicien()
    {
        return $this->belongsTo(User::class, 'id_technicien');
    }

    public function commentaires()
    {
        return $this->hasMany(Commentaire::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}
