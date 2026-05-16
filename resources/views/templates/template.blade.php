<!DOCTYPE html>
<html lang="auto" theme="auto">
<head>
	<title>Hello</title>
	<meta name="keywords" content="PHP, Laravel, Framework"> <!-- Keywords -->	
	<meta name="description" content="CustomFrame"> <!-- Description -->
	<meta name="author" content="Artem Ivanov"> <!-- Author -->
	<meta charset="UTF-8"> <!-- Character set -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Device support -->
	<!-- <link rel="stylesheet" href="app.css"> CSS -->
	<!-- <script src="app.js"></script> JS -->
</head>
<body>
    @include('components/foo.blade.php')
    @yield('content')
    @include('components/bar.blade.php')
</body>
</html>