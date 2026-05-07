<!DOCTYPE html>
<html>
<head>
    <title>Login</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">

    <div class="card p-4 shadow">

        <h2 class="mb-4">Login</h2>

        <form method="POST" action="/login">

            @csrf

            <div class="mb-3">
                <label>Email</label>

                <input type="email" name="email" class="form-control">
            </div>

            <div class="mb-3">
                <label>Password</label>

                <input type="password" name="password" class="form-control">
            </div>

            <button class="btn btn-success">
                Login
            </button>

        </form>

    </div>

</div>

</body>
</html>