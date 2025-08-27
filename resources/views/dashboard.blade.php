@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-0">Tableau de bord</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active" aria-current="page">Accueil</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <!-- Stat Cards -->
    <!-- Remplacez les cartes existantes par celles-ci -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-start border-primary shadow h-100 py-2" id="proformas-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Proformas</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="proformas-count">{{ $proformasCount }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-start border-success shadow h-100 py-2" id="factures-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Factures</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="factures-count">{{ $facturesCount }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-file-invoice-dollar fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-start border-info shadow h-100 py-2" id="clients-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Clients</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="clients-count">{{ $clientsCount }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-start border-warning shadow h-100 py-2" id="articles-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Articles</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="articles-count">{{ $articlesCount }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-box-open fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Dernières proformas -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Dernières proformas</h6>
                <a href="{{ route('proformas.index') }}" class="btn btn-sm btn-primary">Voir tout</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Référence</th>
                                <th>Client</th>
                                <th>Montant</th>
                            </tr>
                        </thead>
                        <tbody id="recent-proformas">
                            @forelse($recentProformas as $proforma)
                            <tr>
                                <td>{{ $proforma->reference }}</td>
                                <td>{{ $proforma->client->name ?? 'N/A' }}</td>
                                <td>{{ number_format($proforma->amount, 2) }} F CFA</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center">Aucune proforma récente</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Activités récentes -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Activités récentes</h6>
            </div>
            <div class="card-body">
                <div class="list-group" id="recent-activities">
                    @forelse($recentActivities as $activity)
                    <div class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">{{ $activity->description }}</h6>
                            <small>{{ $activity->created_at->diffForHumans() }}</small>
                        </div>
                        <p class="mb-1 text-muted">{{ $activity->action }} - {{ $activity->entity_type }}</p>
                    </div>
                    @empty
                    <div class="list-group-item">
                        Aucune activité récente
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Graphique -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Statistiques des proformas</h6>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li><a class="dropdown-item" href="#">7 derniers jours</a></li>
                        <li><a class="dropdown-item" href="#">30 derniers jours</a></li>
                        <li><a class="dropdown-item" href="#">Cette année</a></li>
                    </ul>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="proformasChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://js.pusher.com/7.2/pusher.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Graphique des proformas
        const ctx = document.getElementById('proformasChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'],
                datasets: [{
                    label: 'Proformas créées',
                    data: [12, 19, 3, 5, 2, 3, 15, 8, 7, 10, 6, 9],
                    backgroundColor: 'rgba(78, 115, 223, 0.05)',
                    borderColor: 'rgba(78, 115, 223, 1)',
                    pointBackgroundColor: 'rgba(78, 115, 223, 1)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgba(78, 115, 223, 1)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value;
                            }
                        }
                    }
                }
            }
        });

        // CODE PUSHER POUR LES MISES À JOUR EN TEMPS RÉEL
        initializePusher();
    });

    function initializePusher() {
        // Vérifier si Pusher est configuré
        const pusherKey = '{{ env("PUSHER_APP_KEY") }}';
        const pusherCluster = '{{ env("PUSHER_APP_CLUSTER") }}';
        
        if (!pusherKey || pusherKey === '' || !pusherCluster || pusherCluster === '') {
            console.warn('Pusher n\'est pas configuré. Les mises à jour en temps réel sont désactivées.');
            return;
        }

        try {
            // Initialiser Pusher
            const pusher = new Pusher(pusherKey, {
                cluster: pusherCluster,
                encrypted: true
            });

            // S'abonner au canal
            const channel = pusher.subscribe('admin-dashboard');

            // Écouter les événements
            channel.bind('App\\Events\\NouveauClientCree', function(data) {
                console.log('Événement client reçu:', data);
                updateCounter('clients', data.count);
                addActivity(data.activity);
                highlightCard('clients-card');
            });

            channel.bind('App\\Events\\NouvelleProformaCree', function(data) {
                console.log('Événement proforma reçu:', data);
                updateCounter('proformas', data.count);
                addProforma(data.proforma);
                addActivity(data.activity);
                highlightCard('proformas-card');
            });

            channel.bind('App\\Events\\NouvelleFactureCree', function(data) {
                console.log('Événement facture reçu:', data);
                updateCounter('factures', data.count);
                addActivity(data.activity);
                highlightCard('factures-card');
            });

            channel.bind('App\\Events\\NouvelArticleCree', function(data) {
                console.log('Événement article reçu:', data);
                updateCounter('articles', data.count);
                addActivity(data.activity);
                highlightCard('articles-card');
            });

            console.log('Pusher initialisé avec succès');

        } catch (error) {
            console.error('Erreur lors de l\'initialisation de Pusher:', error);
        }
    }

    // Fonctions utilitaires pour les mises à jour
    function updateCounter(type, count) {
        const element = document.getElementById(`${type}-count`);
        if (element) {
            element.textContent = count;
            console.log(`Compteur ${type} mis à jour: ${count}`);
        }
    }

    function addActivity(activity) {
        const activitiesContainer = document.getElementById('recent-activities');
        if (activitiesContainer && activity) {
            const activityElement = document.createElement('div');
            activityElement.className = 'list-group-item list-group-item-action';
            activityElement.innerHTML = `
                <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1">${activity.description || 'Nouvelle activité'}</h6>
                    <small>à l'instant</small>
                </div>
                <p class="mb-1 text-muted">${activity.action || 'Action'} - ${activity.entity_type || 'Type'}</p>
            `;
            
            // Ajouter au début et limiter à 10 éléments
            activitiesContainer.insertBefore(activityElement, activitiesContainer.firstChild);
            if (activitiesContainer.children.length > 10) {
                activitiesContainer.removeChild(activitiesContainer.lastChild);
            }
        }
    }

    function addProforma(proforma) {
        const proformasContainer = document.getElementById('recent-proformas');
        if (proformasContainer && proforma) {
            // Supprimer le message vide s'il existe
            const emptyMessage = proformasContainer.querySelector('.text-center');
            if (emptyMessage) {
                proformasContainer.innerHTML = '';
            }
            
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td>${proforma.reference || 'N/A'}</td>
                <td>${proforma.client_name || 'N/A'}</td>
                <td>${proforma.amount ? parseFloat(proforma.amount).toFixed(2) + ' F CFA' : 'N/A'}</td>
            `;
            
            // Ajouter au début et limiter à 10 éléments
            proformasContainer.insertBefore(newRow, proformasContainer.firstChild);
            if (proformasContainer.children.length > 10) {
                proformasContainer.removeChild(proformasContainer.lastChild);
            }
        }
    }

    function highlightCard(cardId) {
        const card = document.getElementById(cardId);
        if (card) {
            card.classList.add('highlight');
            setTimeout(() => {
                card.classList.remove('highlight');
            }, 2000);
        }
    }
</script>

<style>
    .highlight {
        animation: highlight 2s ease;
    }
    
    @keyframes highlight {
        0% { 
            transform: translateY(0);
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.5);
        }
        50% { 
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 2rem 0 rgba(58, 59, 69, 0.7);
        }
        100% { 
            transform: translateY(0);
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.5);
        }
    }
</style>
@endsection