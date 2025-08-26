<form action="{{ $route }}" method="POST">
    @csrf
    @if($method === 'PUT')
        @method('PUT')
    @endif

    <div class="form-group">
        <label for="filiale_id">Filiale *</label>
        <select name="filiale_id" id="filiale_id" class="form-control" required {{ auth()->user()->isAdmin() ? '' : 'readonly disabled' }}>
            @foreach($filiales as $filiale)
                <option value="{{ $filiale->id }}" 
                    {{ old('filiale_id', $proforma->filiale_id ?? auth()->user()->filiale_id) == $filiale->id ? 'selected' : '' }}>
                    {{ $filiale->nom }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label>Client *</label>
        <select name="client_id" class="form-control" required>
            <option value="">-- Choisir un client --</option>
            @foreach($clients as $client)
                <option value="{{ $client->id }}"
                    {{ old('client_id', $proforma->client_id ?? '') == $client->id ? 'selected' : '' }}>
                    {{ $client->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label>Référence *</label>
        <input type="text" name="reference" class="form-control" value="{{ old('reference', $proforma->reference ?? '') }}" required>
    </div>

    <div class="mb-3">
        <label>Date *</label>
        <input type="date" name="date" class="form-control" value="{{ old('date', isset($proforma) ? $proforma->date->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
    </div>

    <div class="mb-3">
        <label for="tva_rate" class="form-label">TVA (%) *</label>
        <input type="number" name="tva_rate" id="tva_rate" class="form-control" step="0.01" min="0" max="100"
            value="{{ old('tva_rate', $proforma->tva_rate ?? 18) }}" required>
    </div>
    <div class="mb-3">
        <label>Description</label>
        <textarea name="description" class="form-control" required>{{ old('description', $proforma->description ?? '') }}</textarea>
    </div>

    <div class="mb-3">
        <label>Remise (%)</label>
        <input type="number" name="remise" class="form-control" step="0.01" min="0" max="100" value="{{ old('remise', $proforma->remise ?? 0) }}">
    </div>

    <!-- Template masqué du select -->
    <select class="form-control d-none" id="designation-template">
        <option value="">-- Choisir un article --</option>
        @foreach($catalogArticles as $catalog)
            <option value="{{ $catalog->name }}" data-price="{{ $catalog->default_price }}">{{ $catalog->name }}</option>
        @endforeach
    </select>

    <!-- Articles -->
    <h5>Articles</h5>
    <table class="table" id="articles-table">
        <thead>
            <tr>
                <th>Désignation</th>
                <th>Quantité</th>
                <th>Prix unitaire</th>
                <th>Total</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @php
                $oldArticles = old('articles', isset($proforma) ? $proforma->articles->toArray() : []);
                if (empty($oldArticles)) {
                    $oldArticles = [['designation'=>'', 'quantity'=>'', 'unit_price'=>'']];
                }
            @endphp

            @foreach ($oldArticles as $i => $article)
                <tr>
                    <td>
                        <select name="articles[{{ $i }}][designation]" class="form-control designation-select" data-index="{{ $i }}" required>
                            <option value="">-- Choisir un article --</option>
                            @foreach($catalogArticles as $catalog)
                                <option value="{{ $catalog->name }}"
                                    {{ ($article['designation'] ?? '') == $catalog->name ? 'selected' : '' }}
                                    data-price="{{ $catalog->default_price }}">
                                    {{ $catalog->name }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" name="articles[{{ $i }}][quantity]" class="form-control quantity" value="{{ $article['quantity'] }}" required>
                    </td>
                    <td>
                        <input type="number" name="articles[{{ $i }}][unit_price]" class="form-control unit-price" step="0.01" value="{{ $article['unit_price'] }}" required>
                    </td>
                    <td>
                        <input type="text" class="form-control total" readonly>
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm remove-row">❌</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-end"><strong>Total général :</strong></td>
                <td><input type="text" id="grand-total" class="form-control" readonly></td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <button type="button" class="btn btn-secondary mb-3" id="add-article"><i class="fas fa-plus"></i> Ajouter un article</button>

    <button type="submit" class="btn btn-success">
        {{ $method === 'PUT' ? 'Mettre à jour' : 'Créer' }}
    </button>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let index = {{ count($oldArticles) }};

    // Mettre à jour les totaux
    function updateTotals() {
        let grandTotal = 0;
        document.querySelectorAll('#articles-table tbody tr').forEach(row => {
            const qty = parseFloat(row.querySelector('.quantity').value) || 0;
            const price = parseFloat(row.querySelector('.unit-price').value) || 0;
            const total = qty * price;
            row.querySelector('.total').value = total.toFixed(2);
            grandTotal += total;
        });
        document.getElementById('grand-total').value = grandTotal.toFixed(2);
    }

    // Ajouter un nouvel article
    document.getElementById('add-article').addEventListener('click', function () {
        const tableBody = document.querySelector('#articles-table tbody');
        const newRow = document.createElement('tr');
        
        const selectTemplate = document.getElementById('designation-template').cloneNode(true);
        selectTemplate.classList.remove('d-none');
        selectTemplate.name = `articles[${index}][designation]`;
        selectTemplate.setAttribute('data-index', index);
        selectTemplate.classList.add('form-control', 'designation-select');

        newRow.innerHTML = `
            <td>${selectTemplate.outerHTML}</td>
            <td><input type="number" name="articles[${index}][quantity]" class="form-control quantity" required></td>
            <td><input type="number" name="articles[${index}][unit_price]" class="form-control unit-price" step="0.01" required></td>
            <td><input type="text" class="form-control total" readonly></td>
            <td><button type="button" class="btn btn-danger btn-sm remove-row">❌</button></td>
        `;
        
        tableBody.appendChild(newRow);
        index++;
        updateTotals();
    });

    // Écouter les changements de désignation d'articles
    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('designation-select')) {
            const selectedOption = e.target.options[e.target.selectedIndex];
            const unitPriceInput = e.target.closest('tr').querySelector('.unit-price');
            const defaultPrice = parseFloat(selectedOption.dataset.price || 0);

            if (unitPriceInput && defaultPrice > 0) {
                unitPriceInput.value = defaultPrice.toFixed(2);
                updateTotals();
            }
        }
    });

    // Mettre à jour les totaux à chaque modification de quantité ou de prix unitaire
    document.addEventListener('input', function (e) {
        if (e.target.classList.contains('quantity') || e.target.classList.contains('unit-price')) {
            updateTotals();
        }
    });

    // Supprimer une ligne d'article
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-row')) {
            const rows = document.querySelectorAll('#articles-table tbody tr');
            if(rows.length > 1) {
                e.target.closest('tr').remove();
                updateTotals();
            } else {
                alert('Au moins un article est requis.');
            }
        }
    });

    // Mise à jour des totaux au chargement (utile en édition)
    updateTotals();
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form'); // Assure-toi que c'est bien ton formulaire
    form.addEventListener('submit', function(event) {
        let errors = [];

        // Récupérer les valeurs
        const description = form.querySelector('#description').value.trim();
        const remise = form.querySelector('#remise').value.trim();

        // Description : optionnelle mais si remplie, max 255 caractères par exemple
        if (description.length > 255) {
            errors.push('La description ne peut pas dépasser 255 caractères.');
        }

        // Remise : optionnelle, si remplie doit être un nombre entre 0 et 100
        if (remise !== '') {
            const remiseNum = parseFloat(remise);
            if (isNaN(remiseNum) || remiseNum < 0 || remiseNum > 100) {
                errors.push('La remise doit être un nombre entre 0 et 100.');
            }
        }

        if (errors.length > 0) {
            event.preventDefault();
            alert(errors.join('\n'));
        }
    });
});
</script>
