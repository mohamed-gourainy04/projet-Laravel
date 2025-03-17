{{-- resources/views/rapports/activite-par-jour.blade.php --}}
@extends('layouts.app')
@section('content')
    <div class="container">
        <h1>Activité des Tickets (30 derniers jours)</h1>
        <table class="table">
            <thead>
                <tr>
                    <th>Jour</th>
                    <th>Nombre de Tickets</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tickets_par_jour as $ticket)
                    <tr>
                        <td>{{ $ticket->jour }}</td>
                        <td>{{ $ticket->total }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection