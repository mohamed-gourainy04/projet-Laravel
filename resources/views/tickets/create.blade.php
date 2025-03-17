@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Créer un Nouveau Ticket</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('tickets.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="title" class="form-label">Titre du Ticket</label>
            <input type="text" id="title" name="title" class="form-control" value="{{ old('title') }}" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea id="description" name="description" class="form-control" rows="4" required>{{ old('description') }}</textarea>
        </div>

        <div class="mb-3">
            <label for="priority" class="form-label">Priorité</label>
            <select id="priority" name="priority" class="form-control" required>
                <option value="low">Basse</option>
                <option value="medium">Moyenne</option>
                <option value="high">Haute</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="assigned_to" class="form-label">Assigner à (optionnel)</label>
            <select id="assigned_to" name="assigned_to" class="form-control">
                <option value="">Non assigné</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Créer le Ticket</button>
        <a href="{{ route('tickets.index') }}" class="btn btn-secondary">Annuler</a>
    </form>
</div>
@endsection
