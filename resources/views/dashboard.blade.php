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

<!-- Indicateur de chargement -->
<div id="loading" class="text-center mb-4" style="display: none;">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Chargement...</span>
    </div>
    <p class="mt-2">Chargement des données...</p>
</div>

<!-- Conteneur principal -->
<div id="dashboardContainer">
    <!-- Les données seront chargées ici par JavaScript -->
</div>

<!-- Template pour les cartes de statistiques -->
<template id="statsCardsTemplate">
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-primary shadow h-100 py-2" id="proformas-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Proformas</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="proformas-count">0</div>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="factures-count">0</div>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="clients-count">0</div>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="articles-count">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-box-open fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<!-- Template pour le contenu du dashboard -->
<template id="dashboardContentTemplate">
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
                                    <th>Créée</th>
                                </tr>
                            </thead>
                            <tbody id="recent-proformas">
                                <!-- Rempli par JavaScript -->
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
                        <!-- Rempli par JavaScript -->
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
                            <li><a class="dropdown-item period-filter" href="#" data-period="7">7 derniers jours</a></li>
                            <li><a class="dropdown-item period-filter" href="#" data-period="30">30 derniers jours</a></li>
                            <li><a class="dropdown-item period-filter" href="#" data-period="365">Cette année</a></li>
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

    <!-- Indicateur de mise à jour -->
    <div class="row">
        <div class="col-12 text-end">
            <small class="text-muted">Dernière mise à jour: <span id="updateTime"></span></small>
        </div>
    </div>
</template>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Variables globales
let proformasChart = null;
let currentPeriod = 365; // Par défaut: cette année

document.addEventListener('DOMContentLoaded', function() {
    // Charger les données initiales
    loadDashboardData();

    // Configuration des événements
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('period-filter')) {
            e.preventDefault();
            currentPeriod = parseInt(e.target.dataset.period);
            loadDashboardData();
        }
    });

    // Actualisation automatique toutes les 30 secondes
    setInterval(loadDashboardData, 30000);
});

function loadDashboardData() {
    showLoading();
    
    fetch(`{{ route('dashboard.data') }}?period=${currentPeriod}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('Erreur réseau');
        return response.json();
    })
    .then(data => {
        renderDashboard(data);
        hideLoading();
    })
    .catch(error => {
        console.error('Erreur:', error);
        hideLoading();
        showError('Erreur lors du chargement des données du dashboard');
    });
}

function renderDashboard(data) {
    const container = document.getElementById('dashboardContainer');
    
    // Charger les templates si nécessaire
    if (!container.querySelector('.card')) {
        const statsTemplate = document.getElementById('statsCardsTemplate').content.cloneNode(true);
        const contentTemplate = document.getElementById('dashboardContentTemplate').content.cloneNode(true);
        container.appendChild(statsTemplate);
        container.appendChild(contentTemplate);
    }

    // Mettre à jour les compteurs
    updateCounter('proformas', data.proformasCount);
    updateCounter('factures', data.facturesCount);
    updateCounter('clients', data.clientsCount);
    updateCounter('articles', data.articlesCount);

    // Mettre à jour les proformas récentes
    updateRecentProformas(data.recentProformas);

    // Mettre à jour les activités récentes
    updateRecentActivities(data.recentActivities);

    // Mettre à jour le graphique
    updateChart(data.monthlyData);

    // Mettre à jour l'heure de mise à jour
    document.getElementById('updateTime').textContent = data.lastUpdate;
}

function updateCounter(type, count) {
    const element = document.getElementById(`${type}-count`);
    if (element) {
        element.textContent = count;
    }
}

function updateRecentProformas(proformas) {
    const container = document.getElementById('recent-proformas');
    if (!container) return;

    if (proformas.length === 0) {
        container.innerHTML = '<tr><td colspan="4" class="text-center">Aucune proforma récente</td></tr>';
        return;
    }

    container.innerHTML = proformas.map(proforma => `
        <tr>
            <td>${proforma.reference || 'N/A'}</td>
            <td>${proforma.client_name || 'N/A'}</td>
            <td>${proforma.amount ? parseFloat(proforma.amount).toFixed(2) + ' F CFA' : 'N/A'}</td>
            <td><small class="text-muted">${proforma.created_at}</small></td>
        </tr>
    `).join('');
}

function updateRecentActivities(activities) {
    const container = document.getElementById('recent-activities');
    if (!container) return;

    if (activities.length === 0) {
        container.innerHTML = '<div class="list-group-item">Aucune activité récente</div>';
        return;
    }

    container.innerHTML = activities.map(activity => `
        <div class="list-group-item list-group-item-action">
            <div class="d-flex w-100 justify-content-between">
                <h6 class="mb-1">${activity.description || 'Nouvelle activité'}</h6>
                <small>${activity.created_at}</small>
            </div>
            <p class="mb-1 text-muted">${activity.action || 'Action'} - ${activity.entity_type || 'Type'}</p>
        </div>
    `).join('');
}

function updateChart(monthlyData) {
    const ctx = document.getElementById('proformasChart');
    if (!ctx) return;

    const monthLabels = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
    
    // Détruire le graphique existant s'il y en a un
    if (proformasChart) {
        proformasChart.destroy();
    }

    proformasChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: monthLabels,
            datasets: [{
                label: 'Proformas créées',
                data: monthlyData,
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
}

function showLoading() {
    document.getElementById('loading').style.display = 'block';
}

function hideLoading() {
    document.getElementById('loading').style.display = 'none';
}

function showError(message) {
    const container = document.getElementById('dashboardContainer');
    container.innerHTML = `
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> ${message}
            <button class="btn btn-sm btn-outline-danger ms-2" onclick="loadDashboardData()">Réessayer</button>
        </div>
    `;
}

// Fonctions d'animation pour les mises à jour en temps réel
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
<script>
    // Fonction pour formater les dates en français
    function formatDateToFrench(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        
        const diffInMs = now - date;
        const diffInMinutes = Math.floor(diffInMs / (1000 * 60));
        const diffInHours = Math.floor(diffInMs / (1000 * 60 * 60));
        const diffInDays = Math.floor(diffInMs / (1000 * 60 * 60 * 24));
        
        if (diffInMinutes < 1) {
            return 'à l\'instant';
        } else if (diffInMinutes < 60) {
            return `il y a ${diffInMinutes} ${diffInMinutes === 1 ? 'minute' : 'minutes'}`;
        } else if (diffInHours < 24) {
            return `il y a ${diffInHours} ${diffInHours === 1 ? 'heure' : 'heures'}`;
        } else if (diffInDays < 7) {
            return `il y a ${diffInDays} ${diffInDays === 1 ? 'jour' : 'jours'}`;
        } else if (diffInDays < 30) {
            const weeks = Math.floor(diffInDays / 7);
            return `il y a ${weeks} ${weeks === 1 ? 'semaine' : 'semaines'}`;
        } else if (diffInDays < 365) {
            const months = Math.floor(diffInDays / 30);
            return `il y a ${months} ${months === 1 ? 'mois' : 'mois'}`;
        } else {
            const years = Math.floor(diffInDays / 365);
            return `il y a ${years} ${years === 1 ? 'an' : 'ans'}`;
        }
    }

    // Mettez à jour les fonctions d'affichage :
    function updateRecentProformas(proformas) {
        const container = document.getElementById('recent-proformas');
        if (!container) return;

        if (proformas.length === 0) {
            container.innerHTML = '<tr><td colspan="4" class="text-center">Aucune proforma récente</td></tr>';
            return;
        }

        container.innerHTML = proformas.map(proforma => `
            <tr>
                <td>${proforma.reference || 'N/A'}</td>
                <td>${proforma.client_name || 'N/A'}</td>
                <td>${proforma.amount ? parseFloat(proforma.amount).toFixed(2) + ' F CFA' : 'N/A'}</td>
                <td><small class="text-muted">${formatDateToFrench(proforma.created_at)}</small></td>
            </tr>
        `).join('');
    }

    function updateRecentActivities(activities) {
        const container = document.getElementById('recent-activities');
        if (!container) return;

        if (activities.length === 0) {
            container.innerHTML = '<div class="list-group-item">Aucune activité récente</div>';
            return;
        }

        container.innerHTML = activities.map(activity => `
            <div class="list-group-item list-group-item-action">
                <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1">${activity.description || 'Nouvelle activité'}</h6>
                    <small>${formatDateToFrench(activity.created_at)}</small>
                </div>
                <p class="mb-1 text-muted">${activity.action || 'Action'} - ${activity.entity_type || 'Type'}</p>
            </div>
        `).join('');
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

#loading {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(255, 255, 255, 0.9);
    padding: 20px;
    border-radius: 10px;
    z-index: 1000;
}
</style>
@endpush