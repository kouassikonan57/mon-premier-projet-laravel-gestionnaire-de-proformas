<!DOCTYPE html>
<html lang="fr" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Proforma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <style>
        :root {
            --sidebar-width: 280px;
            --sidebar-collapsed-width: 80px;
            --sidebar-bg: #2b313e;
            --sidebar-dark-bg: #1e222d;
            --sidebar-text: #a0a4b1;
            --sidebar-active: #fff;
            --content-bg: #f8f9fa;
            --primary-color: #3b82f6;
        }

        body {
            overflow-x: hidden;
            background-color: var(--content-bg);
            transition: background-color 0.3s ease;
        }

        /* Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: var(--sidebar-bg);
            color: white;
            transition: all 0.3s ease;
            z-index: 1000;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar-collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar-collapsed .sidebar-item-text,
        .sidebar-collapsed .sidebar-group-arrow,
        .sidebar-collapsed .menu-header {
            display: none !important;
        }

        .sidebar-brand {
            padding: 1.5rem;
            font-weight: 600;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            height: 70px;
        }

        .sidebar-menu {
            padding: 1rem 0;
            list-style: none;
        }

        .sidebar-menu li.menu-header {
            padding: 0.5rem 1.5rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            color: var(--sidebar-text);
            font-weight: 600;
            margin-top: 1rem;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: var(--sidebar-text);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            color: var(--sidebar-active);
            background: rgba(255,255,255,0.1);
            border-left-color: var(--primary-color);
        }

        .sidebar-menu i {
            margin-right: 12px;
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
            transition: margin-right 0.3s;
        }

        .sidebar-collapsed .sidebar-menu i {
            margin-right: 0;
        }

        .submenu {
            list-style: none;
            padding-left: 0;
            background: rgba(0,0,0,0.1);
            overflow: hidden;
            max-height: 0;
            transition: max-height 0.3s ease;
        }

        .submenu.show {
            max-height: 500px;
        }

        .submenu a {
            padding-left: 3.5rem;
            font-size: 0.9rem;
            border-left: none;
        }

        .sidebar-collapsed .submenu {
            display: none !important;
        }

        .has-dropdown > a::after {
            content: '\f078';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            margin-left: auto;
            transition: transform 0.3s;
            font-size: 0.8rem;
        }

        .has-dropdown.show > a::after {
            transform: rotate(180deg);
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        .main-content-collapsed {
            margin-left: var(--sidebar-collapsed-width);
        }

        /* Navbar */
        .navbar {
            padding: 1rem 1.5rem;
            background: white;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
            position: sticky;
            top: 0;
            z-index: 999;
            height: 70px;
            display: flex;
            justify-content: flex-end;
        }

        /* Dark Mode */
        [data-bs-theme="dark"] {
            --content-bg: #212529;
        }

        [data-bs-theme="dark"] .sidebar {
            background: var(--sidebar-dark-bg);
        }

        [data-bs-theme="dark"] .navbar {
            background: var(--sidebar-dark-bg);
            color: white;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.5);
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0 !important;
            }

            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0,0,0,0.5);
                z-index: 999;
                display: none;
            }

            .sidebar-overlay.show {
                display: block;
            }
        }

        /* Dashboard Cards */
        .stat-card {
            border-left: 4px solid;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card-primary {
            border-left-color: var(--primary-color);
        }

        .stat-card-success {
            border-left-color: #10b981;
        }

        .stat-card-info {
            border-left-color: #3b82f6;
        }

        .stat-card-warning {
            border-left-color: #f59e0b;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar Overlay (mobile) -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-brand">
                <img src="/images/5-g.png" alt="Groupe YDIA">
                <span class="sidebar-item-text">Gestion Proforma</span>
                <button class="btn btn-sm btn-dark" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            
            <ul class="sidebar-menu">
                <li>
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt"></i>
                        <span class="sidebar-item-text">Tableau de bord</span>
                    </a>
                </li>
                
                @php
                    $user = auth()->user();
                    $filialeCode = $user?->filiale?->code ?? null;
                    $isAdmin = $user?->role === 'admin' || ($user && method_exists($user, 'isAdmin') && $user->isAdmin());
                @endphp
                
                <!-- Filiales - Affichage conditionnel -->
                @if($user && $isAdmin)
                    <!-- ADMIN voit toutes les filiales -->
                    <li class="has-dropdown">
                        <a href="#ddcs">
                            <i class="fas fa-building"></i>
                            <span class="sidebar-item-text">DDCS</span>
                        </a>
                        <ul class="submenu">
                            <li><a href="{{ route('clients.index') }}" class="{{ request()->routeIs('clients.*') ? 'active' : '' }}">Clients</a></li>
                            <li><a href="{{ route('catalog-articles.index') }}" class="{{ request()->routeIs('catalog-articles.*') ? 'active' : '' }}">Catalogue Articles</a></li>
                            <li><a href="{{ route('proformas.index') }}" class="{{ request()->routeIs('proformas.*') ? 'active' : '' }}">Proformas</a></li>
                            <li><a href="{{ route('factures.index') }}" class="{{ request()->routeIs('factures.*') ? 'active' : '' }}">Factures</a></li>
                        </ul>
                    </li>
                    
                    <li class="has-dropdown">
                        <a href="#yadi">
                            <i class="fas fa-car"></i>
                            <span class="sidebar-item-text">YADI CAR CENTER</span>
                        </a>
                        <ul class="submenu">
                            <li><a href="{{ route('clients.index') }}" class="{{ request()->routeIs('clients.*') ? 'active' : '' }}">Clients</a></li>
                            <li><a href="{{ route('catalog-articles.index') }}" class="{{ request()->routeIs('catalog-articles.*') ? 'active' : '' }}">Catalogue Articles</a></li>
                            <li><a href="{{ route('proformas.index') }}" class="{{ request()->routeIs('proformas.*') ? 'active' : '' }}">Proformas</a></li>
                            <li><a href="{{ route('factures.index') }}" class="{{ request()->routeIs('factures.*') ? 'active' : '' }}">Factures</a></li>
                        </ul>
                    </li>
                    
                    <li class="has-dropdown">
                        <a href="#yadi-construction">
                            <i class="fas fa-hard-hat"></i>
                            <span class="sidebar-item-text">YDIA CONSTRUCTION</span>
                        </a>
                        <ul class="submenu">
                            <li><a href="{{ route('clients.index') }}" class="{{ request()->routeIs('clients.*') ? 'active' : '' }}">Clients</a></li>
                            <li><a href="{{ route('catalog-articles.index') }}" class="{{ request()->routeIs('catalog-articles.*') ? 'active' : '' }}">Catalogue Articles</a></li>
                            <li><a href="{{ route('proformas.index') }}" class="{{ request()->routeIs('proformas.*') ? 'active' : '' }}">Proformas</a></li>
                            <li><a href="{{ route('factures.index') }}" class="{{ request()->routeIs('factures.*') ? 'active' : '' }}">Factures</a></li>
                        </ul>
                    </li>
                    
                    <li class="has-dropdown">
                        <a href="#vroom">
                            <i class="fas fa-bolt"></i>
                            <span class="sidebar-item-text">VROOM</span>
                        </a>
                        <ul class="submenu">
                            <li><a href="{{ route('clients.index') }}" class="{{ request()->routeIs('clients.*') ? 'active' : '' }}">Clients</a></li>
                            <li><a href="{{ route('catalog-articles.index') }}" class="{{ request()->routeIs('catalog-articles.*') ? 'active' : '' }}">Catalogue Articles</a></li>
                            <li><a href="{{ route('proformas.index') }}" class="{{ request()->routeIs('proformas.*') ? 'active' : '' }}">Proformas</a></li>
                            <li><a href="{{ route('factures.index') }}" class="{{ request()->routeIs('factures.*') ? 'active' : '' }}">Factures</a></li>
                        </ul>
                    </li>
                @elseif($user)
                    <!-- UTILISATEUR voit seulement sa filiale -->
                    @if($filialeCode === 'DDCS-001')
                    <li class="has-dropdown show">
                        <a href="#ddcs">
                            <i class="fas fa-building"></i>
                            <span class="sidebar-item-text">DDCS</span>
                        </a>
                        <ul class="submenu show">
                            <li><a href="{{ route('clients.index') }}" class="{{ request()->routeIs('clients.*') ? 'active' : '' }}">Clients</a></li>
                            <li><a href="{{ route('catalog-articles.index') }}" class="{{ request()->routeIs('catalog-articles.*') ? 'active' : '' }}">Catalogue Articles</a></li>
                            <li><a href="{{ route('proformas.index') }}" class="{{ request()->routeIs('proformas.*') ? 'active' : '' }}">Proformas</a></li>
                            <li><a href="{{ route('factures.index') }}" class="{{ request()->routeIs('factures.*') ? 'active' : '' }}">Factures</a></li>
                        </ul>
                    </li>
                    @elseif($filialeCode === 'YADI-002')
                    <li class="has-dropdown show">
                        <a href="#yadi">
                            <i class="fas fa-car"></i>
                            <span class="sidebar-item-text">YADI CAR CENTER</span>
                        </a>
                        <ul class="submenu show">
                            <li><a href="{{ route('clients.index') }}" class="{{ request()->routeIs('clients.*') ? 'active' : '' }}">Clients</a></li>
                            <li><a href="{{ route('catalog-articles.index') }}" class="{{ request()->routeIs('catalog-articles.*') ? 'active' : '' }}">Catalogue Articles</a></li>
                            <li><a href="{{ route('proformas.index') }}" class="{{ request()->routeIs('proformas.*') ? 'active' : '' }}">Proformas</a></li>
                            <li><a href="{{ route('factures.index') }}" class="{{ request()->routeIs('factures.*') ? 'active' : '' }}">Factures</a></li>
                        </ul>
                    </li>
                    @elseif($filialeCode === 'YDIA_CONSTRUCTION-003')
                    <li class="has-dropdown show">
                        <a href="#yadi-construction">
                            <i class="fas fa-hard-hat"></i>
                            <span class="sidebar-item-text">YDIA CONSTRUCTION</span>
                        </a>
                        <ul class="submenu show">
                            <li><a href="{{ route('clients.index') }}" class="{{ request()->routeIs('clients.*') ? 'active' : '' }}">Clients</a></li>
                            <li><a href="{{ route('catalog-articles.index') }}" class="{{ request()->routeIs('catalog-articles.*') ? 'active' : '' }}">Catalogue Articles</a></li>
                            <li><a href="{{ route('proformas.index') }}" class="{{ request()->routeIs('proformas.*') ? 'active' : '' }}">Proformas</a></li>
                            <li><a href="{{ route('factures.index') }}" class="{{ request()->routeIs('factures.*') ? 'active' : '' }}">Factures</a></li>
                        </ul>
                    </li>
                    @elseif($filialeCode === 'VROOM-004')
                    <li class="has-dropdown show">
                        <a href="#vroom">
                            <i class="fas fa-bolt"></i>
                            <span class="sidebar-item-text">VROOM</span>
                        </a>
                        <ul class="submenu show">
                            <li><a href="{{ route('clients.index') }}" class="{{ request()->routeIs('clients.*') ? 'active' : '' }}">Clients</a></li>
                            <li><a href="{{ route('catalog-articles.index') }}" class="{{ request()->routeIs('catalog-articles.*') ? 'active' : '' }}">Catalogue Articles</a></li>
                            <li><a href="{{ route('proformas.index') }}" class="{{ request()->routeIs('proformas.*') ? 'active' : '' }}">Proformas</a></li>
                            <li><a href="{{ route('factures.index') }}" class="{{ request()->routeIs('factures.*') ? 'active' : '' }}">Factures</a></li>
                        </ul>
                    </li>
                    @else
                    <li class="menu-header">AUCUNE FILIALE ASSIGNÉE</li>
                    <li>
                        <a href="#">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span class="sidebar-item-text">Contactez l'administrateur</span>
                        </a>
                    </li>
                    @endif
                @endif
                
                <!-- Outils - Accessibles à tous les utilisateurs connectés -->
                @auth
                <li class="menu-header">OUTILS</li>
                <li>
                    <a href="{{ route('historique.index') }}" class="{{ request()->routeIs('historique.*') ? 'active' : '' }}">
                        <i class="fas fa-history"></i>
                        <span class="sidebar-item-text">Historique</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('logs.index') }}" class="{{ request()->routeIs('logs.*') ? 'active' : '' }}">
                        <i class="fas fa-clipboard-list"></i>
                        <span class="sidebar-item-text">Journal des actions</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('stats.ventes') }}" class="{{ request()->routeIs('stats.*') ? 'active' : '' }}">
                        <i class="fas fa-chart-bar"></i>
                        <span class="sidebar-item-text">Statistiques</span>
                    </a>
                </li>
                
                <!-- Section Admin - SEULEMENT si admin -->
                @if($isAdmin)
                    <li class="menu-header">ADMINISTRATION</li>
                    <li>
                        <a href="{{ route('users.create') }}">
                            <i class="fas fa-user-plus"></i>
                            <span class="sidebar-item-text">Nouvel Utilisateur</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('filiales.create') }}">
                            <i class="fas fa-plus-circle"></i>
                            <span class="sidebar-item-text">Nouvelle Filiale</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('users.index') }}">
                            <i class="fas fa-users"></i>
                            <span class="sidebar-item-text">Gestion Utilisateurs</span>
                        </a>
                    </li>
                @endif
                @endauth
            </ul>
            
            <!-- Footer sidebar -->
            <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                <button class="btn btn-sm btn-dark w-100 text-start" id="themeToggle">
                    <i class="fas fa-moon"></i>
                    <span class="sidebar-item-text">Mode sombre</span>
                </button>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="main-content" id="mainContent">
            <nav class="navbar">
                <button class="btn btn-outline-secondary d-lg-none me-auto" id="mobileSidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="ms-auto d-flex align-items-center">
                @auth
                    <span class="navbar-text me-3">
                        Connecté en tant que {{ Auth::user()->name }}
                        @if($filialeCode)
                            ({{ $filialeCode }})
                        @endif
                    </span>
                    
                    <div class="dropdown">
                        <button class="btn btn-secondary dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i> Profil</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i> Paramètres</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt me-2"></i> Déconnexion
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-primary me-2">Connexion</a>
                @endauth
                </div>
            </nav>

            <div class="container-fluid p-4">
                @yield('content')
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Gestion du thème clair/sombre
        const themeToggle = document.getElementById('themeToggle');
        themeToggle.addEventListener('click', function() {
            const html = document.documentElement;
            const theme = html.getAttribute('data-bs-theme');
            const newTheme = theme === 'dark' ? 'light' : 'dark';
            
            html.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            
            const icon = this.querySelector('i');
            const text = this.querySelector('.sidebar-item-text');
            icon.className = newTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
            text.textContent = newTheme === 'dark' ? 'Mode clair' : 'Mode sombre';
        });

        // Gestion du menu latéral
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const mainContent = document.getElementById('mainContent');
        
        let isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        
        function applySidebarState() {
            if (window.innerWidth <= 992) {
                sidebar.classList.remove('sidebar-collapsed');
                mainContent.classList.remove('main-content-collapsed');
                sidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
            } else {
                if (isCollapsed) {
                    sidebar.classList.add('sidebar-collapsed');
                    mainContent.classList.add('main-content-collapsed');
                } else {
                    sidebar.classList.remove('sidebar-collapsed');
                    mainContent.classList.remove('main-content-collapsed');
                }
            }
        }
        
        function toggleSidebar() {
            if (window.innerWidth <= 992) {
                sidebar.classList.toggle('show');
                sidebarOverlay.classList.toggle('show');
            } else {
                isCollapsed = !isCollapsed;
                localStorage.setItem('sidebarCollapsed', isCollapsed);
                applySidebarState();
            }
        }
        
        sidebarToggle.addEventListener('click', toggleSidebar);
        sidebarOverlay.addEventListener('click', toggleSidebar);

        // Initialisation de l'état du sidebar
        applySidebarState();
        
        // Rafraîchir l'état au redimensionnement
        window.addEventListener('resize', applySidebarState);

        // Gestion des dropdowns
        document.querySelectorAll('.has-dropdown > a').forEach(item => {
            item.addEventListener('click', function(e) {
                if (window.innerWidth <= 992) return;
                
                e.preventDefault();
                const parent = this.parentElement;
                const submenu = this.nextElementSibling;
                
                parent.classList.toggle('show');
                submenu.classList.toggle('show');
            });
        });

        // Fermer le sidebar si on clique sur un lien en mode mobile
        document.querySelectorAll('.sidebar-menu a').forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 992) {
                    toggleSidebar();
                }
            });
        });

        // Initialisation du thème au chargement
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
            
            const themeIcon = themeToggle.querySelector('i');
            const themeText = themeToggle.querySelector('.sidebar-item-text');
            themeIcon.className = savedTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
            themeText.textContent = savedTheme === 'dark' ? 'Mode clair' : 'Mode sombre';
        });

        // Bouton de menu mobile
        const mobileSidebarToggle = document.getElementById('mobileSidebarToggle');
        if (mobileSidebarToggle) {
            mobileSidebarToggle.addEventListener('click', () => {
                sidebar.classList.toggle('show');
                sidebarOverlay.classList.toggle('show');
            });
        }
        
    </script>
    
    @stack('scripts')
</body>
</html>