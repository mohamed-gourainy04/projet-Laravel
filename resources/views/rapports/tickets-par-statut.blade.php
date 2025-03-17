
{{-- resources/views/rapports/tickets-par-statut.blade.php --}}
@extends('layouts.app')
@section('content')
    <div class="container">
        <h1>Tickets par Statut ({{ ucfirst($periode) }})</h1>
        <table class="table">
            <thead>
                <tr>
                    <th>Statut</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tickets as $ticket)
                    <tr>
                        <td>{{ $ticket->statut }}</td>
                        <td>{{ $ticket->total }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection