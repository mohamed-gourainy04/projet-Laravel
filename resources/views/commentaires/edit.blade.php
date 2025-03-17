@extends('layouts.app')

@section('title', 'Modifier un commentaire')

@section('content')
<div class="container">
    <h2>Modifier le commentaire</h2>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('commentaires.update', $commentaire->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="contenu" class="form-label">Commentaire :</label>
            <textarea name="contenu" id="contenu" class="form-control" required>{{ $commentaire->contenu }}</textarea>
        </div>
        <button type="submit" class="btn btn-success">Mettre à jour</button>
        <a href="{{ route('tickets.show', $ticket->id) }}" class="btn btn-secondary">Annuler</a>
    </form>
</div>
@endsection
