{{-- resources/views/tickets/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Liste des Tickets')
@section('content')
    <h1>Tickets</h1>
    <a href="{{ route('tickets.create') }}" class="btn btn-primary">Créer un Ticket</a>
    <ul>
        @foreach($tickets as $ticket)
            <li>{{ $ticket->titre }} - <a href="{{ route('tickets.show', $ticket->id) }}">Voir</a></li>
        @endforeach
    </ul>
@endsection