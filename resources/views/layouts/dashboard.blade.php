<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Proforma</title>

    <!-- Bootstrap & FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
        }
        .sidebar {
            width: 260px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: #1a1a2e;
            color: #fff;
            overflow-y: auto;
        }
        .sidebar .brand {
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid #444;
        }
        .sidebar .brand img {
            height: 40px;
        }
        .sidebar .nav-item {
            padding: 10px 20px;
            cursor: pointer;
        }
        .sidebar .nav-item:hover {
            background: #16213e;
        }
        .sidebar .submenu {
            padding-left: 20px;
            display: none;
        }
        .sidebar .nav-item.open .submenu {
            display: block;
        }
        .content {
            margin-left: 260px;
            padding: 20px;
        }
    </style>
</head>
<body>

    @php
        $user = auth()->user();
        $filialeCode = $user->filiale->code ?? null;
    @endphp

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Logo Groupe -->
        <div class="brand">
            <img src="/images/groupe-logo.png" alt="Groupe YDIA">
            <span><strong>Groupe YDIA</strong></span>
        </div>

        {{-- Affichage pour ADMIN : toutes les filiales --}}
        @if($user->role === 'admin')
            {{-- Yadi Car Center --}}
            <div class="nav-item">
                <img src="/images/2-y.png" alt="Yadi" width="20"> Yadi Car Center
                <div class="submenu">
                    <a href="{{ route('clients.index') }}" class="d-block text-white">Clients</a>
                    <a href="{{ route('catalog-articles.index') }}" class="d-block text-white">Catalogue</a>
                    <a href="{{ route('proformas.index') }}" class="d-block text-white">Proformas</a>
                    <a href="{{ route('factures.index') }}" class="d-block text-white">Factures</a>
                    <a href="{{ route('historique.index') }}" class="d-block text-white">Historique</a>
                    <a href="{{ route('logs.index') }}" class="d-block text-white">Journal</a>
                    <a href="{{ route('stats.ventes') }}" class="d-block text-white">Statistiques</a>
                </div>
            </div>

            {{-- DDCS --}}
            <div class="nav-item">
                <img src="/images/4.png" alt="DDCS" width="20"> DDCS
                <div class="submenu">
                    <a href="#" class="d-block text-white">Clients</a>
                    <a href="#" class="d-block text-white">Catalogue</a>
                    <a href="#" class="d-block text-white">Proformas</a>
                    <a href="#" class="d-block text-white">Factures</a>
                    <a href="#" class="d-block text-white">Historique</a>
                    <a href="#" class="d-block text-white">Journal</a>
                    <a href="#" class="d-block text-white">Statistiques</a>
                </div>
            </div>

            {{-- Ydia Construction --}}
            <div class="nav-item">
                <img src="/images/1-yc.png" alt="Ydia Construction" width="20"> Ydia Construction
                <div class="submenu">
                    <a href="#" class="d-block text-white">Clients</a>
                    <a href="#" class="d-block text-white">Catalogue</a>
                    <a href="#" class="d-block text-white">Proformas</a>
                    <a href="#" class="d-block text-white">Factures</a>
                    <a href="#" class="d-block text-white">Historique</a>
                    <a href="#" class="d-block text-white">Journal</a>
                    <a href="#" class="d-block text-white">Statistiques</a>
                </div>
            </div>

            {{-- Vroom --}}
            <div class="nav-item">
                <img src="/images/3.png" alt="Vroom" width="20"> Vroom
                <div class="submenu">
                    <a href="#" class="d-block text-white">Clients</a>
                    <a href="#" class="d-block text-white">Catalogue</a>
                    <a href="#" class="d-block text-white">Proformas</a>
                    <a href="#" class="d-block text-white">Factures</a>
                    <a href="#" class="d-block text-white">Historique</a>
                    <a href="#" class="d-block text-white">Journal</a>
                    <a href="#" class="d-block text-white">Statistiques</a>
                </div>
            </div>
        
        {{-- Affichage pour UTILISATEUR : sa filiale uniquement --}}
        @else
            @if($filialeCode === 'YADI-002')
                <div class="nav-item open">
                    <img src="/images/2-y.png" alt="Yadi" width="20"> Yadi Car Center
                    <div class="submenu">
                        <a href="{{ route('clients.index') }}" class="d-block text-white">Clients</a>
                        <a href="{{ route('catalog-articles.index') }}" class="d-block text-white">Catalogue</a>
                        <a href="{{ route('proformas.index') }}" class="d-block text-white">Proformas</a>
                        <a href="{{ route('factures.index') }}" class="d-block text-white">Factures</a>
                        <a href="{{ route('historique.index') }}" class="d-block text-white">Historique</a>
                        <a href="{{ route('logs.index') }}" class="d-block text-white">Journal</a>
                        <a href="{{ route('stats.ventes') }}" class="d-block text-white">Statistiques</a>
                    </div>
                </div>
            @elseif($filialeCode === 'DDCS-001')
                <div class="nav-item open">
                    <img src="/images/4.png" alt="DDCS" width="20"> DDCS
                    <div class="submenu">
                        <a href="#" class="d-block text-white">Clients</a>
                        <a href="#" class="d-block text-white">Catalogue</a>
                        <a href="#" class="d-block text-white">Proformas</a>
                        <a href="#" class="d-block text-white">Factures</a>
                        <a href="#" class="d-block text-white">Historique</a>
                        <a href="#" class="d-block text-white">Journal</a>
                        <a href="#" class="d-block text-white">Statistiques</a>
                    </div>
                </div>
            @elseif($filialeCode === 'YDIA_CONSTRUCTION-003')
                <div class="nav-item open">
                    <img src="/images/1-yc.png" alt="Ydia Construction" width="20"> Ydia Construction
                    <div class="submenu">
                        <a href="#" class="d-block text-white">Clients</a>
                        <a href="#" class="d-block text-white">Catalogue</a>
                        <a href="#" class="d-block text-white">Proformas</a>
                        <a href="#" class="d-block text-white">Factures</a>
                        <a href="#" class="d-block text-white">Historique</a>
                        <a href="#" class="d-block text-white">Journal</a>
                        <a href="#" class="d-block text-white">Statistiques</a>
                    </div>
                </div>
            @elseif($filialeCode === 'VROOM-004')
                <div class="nav-item open">
                    <img src="/images/3.png" alt="Vroom" width="20"> Vroom
                    <div class="submenu">
                        <a href="#" class="d-block text-white">Clients</a>
                        <a href="#" class="d-block text-white">Catalogue</a>
                        <a href="#" class="d-block text-white">Proformas</a>
                        <a href="#" class="d-block text-white">Factures</a>
                        <a href="#" class="d-block text-white">Historique</a>
                        <a href="#" class="d-block text-white">Journal</a>
                        <a href="#" class="d-block text-white">Statistiques</a>
                    </div>
                </div>
            @endif
        @endif
    </div>

    <!-- Contenu -->
    <div class="content">
        @yield('content')
    </div>

    <script>
        // Menu déroulant
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', () => {
                item.classList.toggle('open');
            });
        });
    </script>
</body>
</html>
