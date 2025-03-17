{{-- resources/views/rapports/tickets-par-priorite.blade.php --}}
@extends('layouts.app')
@section('content')
    <div class="container">
        <h1>Tickets par Priorité ({{ ucfirst($periode) }})</h1>
        <table class="table">
            <thead>
                <tr>
                    <th>Priorité</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tickets as $ticket)
                    <tr>
                        <td>{{ $ticket->priorite }}</td>
                        <td>{{ $ticket->total }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection