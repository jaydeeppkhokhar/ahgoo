<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="container mt-5">
        <h1>Welcome to the Admin Dashboard</h1>

        <a href="{{ url('/admin/logout') }}" class="btn btn-danger">Logout</a>
    </div>
</body>
</html>
