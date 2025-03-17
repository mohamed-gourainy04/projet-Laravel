{{-- resources/views/rapports/temps-resolution.blade.php --}}
@extends('layouts.app')
@section('content')
    <div class="container">
        <h1>Temps moyen de résolution ({{ ucfirst($periode) }})</h1>
        <table class="table">
            <thead>
                <tr>
                    <th>Technicien</th>
                    <th>Tickets Résolus</th>
                    <th>Temps Moyen (heures)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($resultats as $resultat)
                    <tr>
                        <td>{{ $resultat['nom'] }}</td>
                        <td>{{ $resultat['total_tickets'] }}</td>
                        <td>{{ $resultat['temps_moyen'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
