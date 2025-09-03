@extends('layouts.app')

@section('content')
<div class="container">
  <h1 class="mb-4">Statistiques des ventes</h1>

  {{-- Filtre de période --}}
  <div class="row mb-4 g-2">
    <div class="col-auto">
      <label>Mois</label>
      <select id="monthFilter" class="form-select">
        <option value="">Tous les mois</option>
        @foreach(range(1, 12) as $m)
          <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
          </option>
        @endforeach
      </select>
    </div>
    <div class="col-auto">
      <label>Année</label>
      <select id="yearFilter" class="form-select">
        @for($y = now()->year; $y >= 2022; $y--)
          <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
        @endfor
      </select>
    </div>
    <div class="col-auto align-self-end">
      <button id="applyFilter" class="btn btn-primary">Appliquer</button>
      <button id="refreshBtn" class="btn btn-outline-secondary">
        <i class="fas fa-sync-alt"></i>
      </button>
    </div>
    <div class="col-auto align-self-end">
      <div id="lastUpdate" class="text-muted small" style="display: none;">
        Dernière mise à jour: <span id="updateTime"></span>
      </div>
    </div>
  </div>

  {{-- Indicateur de chargement --}}
  <div id="loading" class="text-center mb-4" style="display: none;">
    <div class="spinner-border text-primary" role="status">
      <span class="visually-hidden">Chargement...</span>
    </div>
    <p class="mt-2">Chargement des données...</p>
  </div>

  {{-- Conteneur des statistiques --}}
  <div id="statisticsContainer">
    {{-- Les données seront chargées ici par JavaScript --}}
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Variables globales pour stocker les instances de graphiques
let topChart = null;
let bottomChart = null;
let clientChart = null;
let monthlySalesChart = null;

document.addEventListener('DOMContentLoaded', function() {
    // Initialiser les filtres avec les valeurs de l'URL
    const urlParams = new URLSearchParams(window.location.search);
    const month = urlParams.get('month');
    const year = urlParams.get('year') || new Date().getFullYear();
    
    if (month) document.getElementById('monthFilter').value = month;
    if (year) document.getElementById('yearFilter').value = year;

    // Charger les données initiales
    loadStatistics();

    // Événements
    document.getElementById('applyFilter').addEventListener('click', loadStatistics);
    document.getElementById('refreshBtn').addEventListener('click', loadStatistics);
    document.getElementById('monthFilter').addEventListener('change', loadStatistics);
    document.getElementById('yearFilter').addEventListener('change', loadStatistics);

    // Actualisation automatique toutes les 30 secondes
    setInterval(loadStatistics, 30000);
});

function loadStatistics() {
    showLoading();
    
    const month = document.getElementById('monthFilter').value;
    const year = document.getElementById('yearFilter').value;

    // Mettre à jour l'URL sans recharger la page
    const url = new URL(window.location);
    if (month) url.searchParams.set('month', month);
    else url.searchParams.delete('month');
    if (year) url.searchParams.set('year', year);
    else url.searchParams.delete('year');
    window.history.replaceState({}, '', url);

    fetch(`{{ route('stats.ventes.data') }}?month=${month}&year=${year}`, {
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
        renderStatistics(data);
        hideLoading();
    })
    .catch(error => {
        console.error('Erreur:', error);
        hideLoading();
        showError('Erreur lors du chargement des données');
    });
}

function renderStatistics(data) {
    const container = document.getElementById('statisticsContainer');
    
    container.innerHTML = `
        {{-- Total des ventes --}}
        <div class="mb-4">
            <h4>Total des ventes (TTC) : ${formatCurrency(data.totalVentes)} FCFA</h4>
        </div>

        ${data.month && data.year ? `
            <div class="mb-3">
                <h6>Période : ${getMonthName(data.month)} ${data.year}</h6>
            </div>
        ` : ''}

        {{-- Graphiques --}}
        <div class="row mb-4">
            {{-- Top produits --}}
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-header">Top 5 produits vendus</div>
                    <div class="card-body" style="position: relative; height:250px;">
                        ${data.topProducts.length ? '<canvas id="topChartCanvas" height="250"></canvas>' : '<p class="text-muted">Aucune donnée disponible.</p>'}
                    </div>
                </div>
            </div>

            {{-- Flop produits --}}
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-header">Flop 5 produits</div>
                    <div class="card-body" style="position: relative; height:250px;">
                        ${data.bottomProducts.length ? '<canvas id="bottomChartCanvas" height="250"></canvas>' : '<p class="text-muted">Aucune donnée disponible.</p>'}
                    </div>
                </div>
            </div>

            {{-- Meilleurs clients --}}
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-header">Top 5 clients (CA TTC)</div>
                    <div class="card-body" style="position: relative; height:250px;">
                        ${data.bestClients.length ? '<canvas id="clientChartCanvas" height="250"></canvas>' : '<p class="text-muted">Aucune donnée disponible.</p>'}
                    </div>
                </div>
            </div>
        </div>

        {{-- Graphique des ventes mensuelles --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">Ventes mensuelles en ${data.year || new Date().getFullYear()}</div>
                    <div class="card-body" style="position: relative; height:400px;">
                        <canvas id="monthlySalesChartCanvas" height="400"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tableaux de données --}}
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-header">Détail des produits</div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr><th>Produit</th><th>Quantité vendue</th></tr>
                            </thead>
                            <tbody>
                                ${data.topProducts.map(p => `
                                    <tr><td>${p.designation}</td><td>${p.total_vendus}</td></tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-header">Détail des clients</div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Client</th>
                                    <th>CA (TTC)</th>
                                    <th>% du CA</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.bestClients.map(c => `
                                    <tr>
                                        <td>${c.name}</td>
                                        <td>${formatCurrency(c.total_ca)} FCFA</td>
                                        <td>
                                            ${data.totalVentes > 0 ? ((c.total_ca / data.totalVentes) * 100).toFixed(2).replace('.', ',') : '0,00'} %
                                        </td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Mettre à jour l'indicateur de dernière mise à jour
    document.getElementById('lastUpdate').style.display = 'block';
    document.getElementById('updateTime').textContent = data.lastUpdate;

    // Initialiser les graphiques
    initCharts(data);
}

function initCharts(data) {
    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': ' + context.raw.toLocaleString('fr-FR');
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return value.toLocaleString('fr-FR');
                    }
                }
            }
        }
    };

    // Top produits (Doughnut)
    if (data.topProducts.length && document.getElementById('topChartCanvas')) {
        if (topChart) topChart.destroy();
        topChart = new Chart(document.getElementById('topChartCanvas'), {
            type: 'doughnut',
            data: {
                labels: data.topProducts.map(p => p.designation),
                datasets: [{
                    label: 'Quantité vendue',
                    data: data.topProducts.map(p => p.total_vendus),
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)'
                    ],
                    borderWidth: 1
                }]
            },
            options: chartOptions
        });
    }

    // Flop produits (Pie)
    if (data.bottomProducts.length && document.getElementById('bottomChartCanvas')) {
        if (bottomChart) bottomChart.destroy();
        bottomChart = new Chart(document.getElementById('bottomChartCanvas'), {
            type: 'pie',
            data: {
                labels: data.bottomProducts.map(p => p.designation),
                datasets: [{
                    label: 'Quantité vendue',
                    data: data.bottomProducts.map(p => p.total_vendus),
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)'
                    ],
                    borderWidth: 1
                }]
            },
            options: chartOptions
        });
    }

    // Meilleurs clients (Bar)
    if (data.bestClients.length && document.getElementById('clientChartCanvas')) {
        if (clientChart) clientChart.destroy();
        clientChart = new Chart(document.getElementById('clientChartCanvas'), {
            type: 'bar',
            data: {
                labels: data.bestClients.map(c => c.name),
                datasets: [{
                    label: 'CA TTC (FCFA)',
                    data: data.bestClients.map(c => c.total_ca),
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: chartOptions
        });
    }

    // Ventes mensuelles (Line)
    if (document.getElementById('monthlySalesChartCanvas')) {
        const monthlyLabels = [
            'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
            'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
        ];

        if (monthlySalesChart) monthlySalesChart.destroy();
        monthlySalesChart = new Chart(document.getElementById('monthlySalesChartCanvas'), {
            type: 'line',
            data: {
                labels: monthlyLabels,
                datasets: [{
                    label: 'Total des ventes TTC (FCFA)',
                    data: Object.values(data.salesPerMonth),
                    borderColor: 'rgba(54, 162, 235, 0.8)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    fill: true,
                    tension: 0.3,
                }]
            },
            options: {
                ...chartOptions,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString('fr-FR');
                            }
                        }
                    }
                }
            }
        });
    }
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('fr-FR', {
        maximumFractionDigits: 0
    }).format(amount);
}

function getMonthName(monthNumber) {
    const months = [
        'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
        'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
    ];
    return months[monthNumber - 1] || '';
}

function showLoading() {
    document.getElementById('loading').style.display = 'block';
}

function hideLoading() {
    document.getElementById('loading').style.display = 'none';
}

function showError(message) {
    const container = document.getElementById('statisticsContainer');
    container.innerHTML = `
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> ${message}
        </div>
    `;
}
</script>
@endpush