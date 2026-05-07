<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">

    <div class="d-flex justify-content-between">

        <h2>
            Welcome {{ session('user_name') }}
        </h2>

        <form method="POST" action="/logout">

            @csrf

            <button class="btn btn-danger">
                Logout
            </button>

        </form>

    </div>

</div>

</body>
</html>