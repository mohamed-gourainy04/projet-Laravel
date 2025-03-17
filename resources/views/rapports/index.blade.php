{{-- resources/views/rapports/index.blade.php --}}
@extends('layouts.app')
@section('content')
    <div class="container">
        <h1>Rapports</h1>
        <ul>
            <li><a href="{{ route('rapports.tickets-par-statut') }}">Tickets par statut</a></li>
            <li><a href="{{ route('rapports.tickets-par-priorite') }}">Tickets par priorité</a></li>
            <li><a href="{{ route('rapports.temps-resolution') }}">Temps moyen de résolution</a></li>
            <li><a href="{{ route('rapports.activite-par-jour') }}">Activité par jour</a></li>
            <li><a href="{{ route('rapports.charge-par-technicien') }}">Charge par technicien</a></li>
        </ul>
    </div>
@endsection
