<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="PHILA-CE – Plateforme de formation de la Cité d'Exaucement">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title inertia>{{ config('app.name', 'PHILA-CE') }}</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/js/app.jsx'])
  @inertiaHead
</head>
<body class="font-sans antialiased">
  @inertia
</body>
</html>
