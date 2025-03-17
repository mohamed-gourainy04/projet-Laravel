@extends('layouts.app')

@section('title', 'Commentaires')

@section('content')
<div class="container">
    <h2>Commentaires</h2>
    
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @foreach($ticket->commentaires as $commentaire)
        <div class="card mb-3">
            <div class="card-body">
                <p>{{ $commentaire->contenu }}</p>
                <small class="text-muted">Posté par {{ $commentaire->user->name }} le {{ $commentaire->date_creation }}</small>
                
                @if(Auth::id() === $commentaire->user_id || Auth::user()->isAdmin())
                    <a href="{{ route('commentaires.edit', $commentaire->id) }}" class="btn btn-warning btn-sm">Modifier</a>
                    <form action="{{ route('commentaires.destroy', $commentaire->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Voulez-vous vraiment supprimer ce commentaire ?')">Supprimer</button>
                    </form>
                @endif
            </div>
        </div>
    @endforeach
</div>
@endsection
