@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Tableau de Bord des Tickets</h2>

    <a href="{{ route('tickets.create') }}" class="btn btn-primary mb-3">Créer un Ticket</a>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Titre</th>
                <th>Priorité</th>
                <th>Assigné à</th>
                <th>Status</th>
                <th>Date de Création</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($tickets as $ticket)
                <tr>
                    <td>{{ $ticket->id }}</td>
                    <td>{{ $ticket->title }}</td>
                    <td>
                        <span class="badge bg-{{ $ticket->priority == 'high' ? 'danger' : ($ticket->priority == 'medium' ? 'warning' : 'success') }}">
                            {{ ucfirst($ticket->priority) }}
                        </span>
                    </td>
                    <td>{{ $ticket->assignedTo ? $ticket->assignedTo->name : 'Non assigné' }}</td>
                    <td>
                        <span class="badge bg-{{ $ticket->status == 'open' ? 'info' : 'secondary' }}">
                            {{ ucfirst($ticket->status) }}
                        </span>
                    </td>
                    <td>{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <a href="{{ route('tickets.edit', $ticket->id) }}" class="btn btn-sm btn-warning">Modifier</a>
                        <form action="{{ route('tickets.destroy', $ticket->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce ticket ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
