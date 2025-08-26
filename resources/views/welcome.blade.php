<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Proforma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        main {
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .highlight-link {
            color: #FF4A17;
            text-decoration: none;
            font-weight: bold;
        }

        .highlight-link:hover {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <main>
        <div class="container">
            <h1 class="display-4 mb-3">Gestion Proforma</h1>
            <p class="lead">Système de gestion des proformas et factures</p>
            <a href="{{ route('login') }}" class="btn btn-primary btn-lg mt-4">Se connecter</a>
        </div>
    </main>

    <footer class="text-center bg-white border-top py-3 text-muted">
        <div>© Copyright Yadi-Group - Tous droits réservés</div>
        <div>
            Conçu par 
            <a href="https://github.com/kouassikonan57/" target="_blank" class="highlight-link">KFernand</a> · 
            Distribué par 
            <a href="mailto:Groupe@yadi.ci" target="_blank" class="highlight-link">Yadi-Group</a>
        </div>
    </footer>
</body>
</html>
