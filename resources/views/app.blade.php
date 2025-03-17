{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('dashboard') }}">Gestion des Tickets</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="{{ route('tickets.index') }}">Tickets</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('users.index') }}">Utilisateurs</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('notifications.index') }}">Notifications</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('settings.index') }}">Paramètres</a></li>
                    <li class="nav-item"><form action="{{ route('logout') }}" method="POST"> @csrf <button class="btn btn-danger">Déconnexion</button></form></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        @yield('content')
    </div>
</body>
</html>