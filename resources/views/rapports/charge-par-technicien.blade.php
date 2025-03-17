{-- resources/views/rapports/charge-par-technicien.blade.php --}}
@extends('layouts.app')
@section('content')
    <div class="container">
        <h1>Charge par Technicien</h1>
        <table class="table">
            <thead>
                <tr>
                    <th>Technicien</th>
                    <th>Tickets Ouverts</th>
                    <th>Tickets Résolus</th>
                    <th>Tickets Fermés</th>
                    <th>Tickets Critiques</th>
                    <th>Charge Totale</th>
                </tr>
            </thead>
            <tbody>
                @foreach($resultats as $resultat)
                    <tr>
                        <td>{{ $resultat['nom'] }}</td>
                        <td>{{ $resultat['tickets_ouverts'] }}</td>
                        <td>{{ $resultat['tickets_resolus'] }}</td>
                        <td>{{ $resultat['tickets_fermes'] }}</td>
                        <td>{{ $resultat['tickets_critiques'] }}</td>
                        <td>{{ $resultat['charge_totale'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
