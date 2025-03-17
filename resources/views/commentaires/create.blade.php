@extends('layouts.app')

@section('title', 'Ajouter un commentaire')

@section('content')
<div class="container">
    <h2>Ajouter un commentaire</h2>
    
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('commentaires.store', $ticket->id) }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="contenu" class="form-label">Commentaire :</label>
            <textarea name="contenu" id="contenu" class="form-control" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Envoyer</button>
        <a href="{{ route('tickets.show', $ticket->id) }}" class="btn btn-secondary">Annuler</a>
    </form>
</div>
@endsection
