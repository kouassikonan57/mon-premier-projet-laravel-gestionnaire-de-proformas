@extends('layouts.app')

@section('content')
<div class="container">
  <h1 class="mb-4">Statistiques des ventes</h1>

  {{-- Filtre de période --}}
  <form method="GET" action="{{ route('stats.ventes') }}" class="row mb-4 g-2">
    <div class="col-auto">
      <label>Mois</label>
      <select name="month" class="form-select">
        @foreach(range(1, 12) as $m)
          <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
          </option>
        @endforeach
      </select>
    </div>
    <div class="col-auto">
      <label>Année</label>
      <select name="year" class="form-select">
        @for($y = now()->year; $y >= 2022; $y--)
          <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
        @endfor
      </select>
    </div>
    <div class="col-auto align-self-end">
      <button type="submit" class="btn btn-primary">Filtrer</button>
      @if(request('month') || request('year'))
        <a href="{{ route('stats.ventes') }}" class="btn btn-secondary">Réinitialiser</a>
      @endif
    </div>
  </form>

  {{-- Total des ventes --}}
  <div class="mb-4">
    <h4>Total des ventes (TTC) : {{ number_format($totalVentes, 0, ',', ' ') }} FCFA</h4>
  </div>

  @if($month && $year)
    <div class="mb-3">
      <h6>Période : {{ \Carbon\Carbon::create($year, $month)->translatedFormat('F Y') }}</h6>
    </div>
  @endif

  {{-- Graphiques --}}
  <div class="row mb-4">
    {{-- Top produits --}}
    <div class="col-md-4 mb-3">
      <div class="card">
        <div class="card-header">Top 5 produits vendus</div>
        <div class="card-body" style="position: relative; height:250px;">
          @if($topProducts->isEmpty())
            <p class="text-muted">Aucune donnée disponible.</p>
          @else
            <canvas id="topChart" height="250"></canvas>
          @endif
        </div>
      </div>
    </div>

    {{-- Flop produits --}}
    <div class="col-md-4 mb-3">
      <div class="card">
        <div class="card-header">Flop 5 produits</div>
        <div class="card-body" style="position: relative; height:250px;">
          @if($bottomProducts->isEmpty())
            <p class="text-muted">Aucune donnée disponible.</p>
          @else
            <canvas id="bottomChart" height="250"></canvas>
          @endif
        </div>
      </div>
    </div>

    {{-- Meilleurs clients --}}
    <div class="col-md-4 mb-3">
      <div class="card">
        <div class="card-header">Top 5 clients (CA TTC)</div>
        <div class="card-body" style="position: relative; height:250px;">
          @if($bestClients->isEmpty())
            <p class="text-muted">Aucune donnée disponible.</p>
          @else
            <canvas id="clientChart" height="250"></canvas>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- Graphique des ventes mensuelles --}}
  <div class="row mb-4">
    <div class="col-12">
      <div class="card">
        <div class="card-header">Ventes mensuelles en {{ $year ?? now()->year }}</div>
        <div class="card-body" style="position: relative; height:400px;">
          <canvas id="monthlySalesChart" height="400"></canvas>
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
              @foreach($topProducts as $p)
                <tr><td>{{ $p->designation }}</td><td>{{ $p->total_vendus }}</td></tr>
              @endforeach
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
              @foreach($bestClients as $c)
                <tr>
                  <td>{{ $c->name }}</td>
                  <td>{{ number_format($c->total_ca, 0, ',', ' ') }} FCFA</td>
                  <td>
                    {{ $totalVentes > 0 ? number_format(($c->total_ca / $totalVentes) * 100, 2, ',', ' ') : '0,00' }} %
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  // Configuration commune pour tous les graphiques
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
  @if($topProducts->isNotEmpty())
  new Chart(document.getElementById('topChart'), {
    type: 'doughnut',
    data: {
      labels: @json($topProducts->pluck('designation')),
      datasets: [{
        label: 'Quantité vendue',
        data: @json($topProducts->pluck('total_vendus')),
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
  @endif

  // Flop produits (Pie)
  @if($bottomProducts->isNotEmpty())
  new Chart(document.getElementById('bottomChart'), {
    type: 'pie',
    data: {
      labels: @json($bottomProducts->pluck('designation')),
      datasets: [{
        label: 'Quantité vendue',
        data: @json($bottomProducts->pluck('total_vendus')),
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
  @endif

  // Meilleurs clients (Bar)
  @if($bestClients->isNotEmpty())
  new Chart(document.getElementById('clientChart'), {
    type: 'bar',
    data: {
      labels: @json($bestClients->pluck('name')),
      datasets: [{
        label: 'CA TTC (FCFA)',
        data: @json($bestClients->pluck('total_ca')),
        backgroundColor: 'rgba(54, 162, 235, 0.7)',
        borderColor: 'rgba(54, 162, 235, 1)',
        borderWidth: 1
      }]
    },
    options: chartOptions
  });
  @endif

  // Ventes mensuelles (Line)
  const monthlyLabels = [
    'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
    'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
  ];

  new Chart(document.getElementById('monthlySalesChart'), {
    type: 'line',
    data: {
      labels: monthlyLabels,
      datasets: [{
        label: 'Total des ventes TTC (FCFA)',
        data: @json(array_values($salesPerMonth)),
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
</script>
@endpush