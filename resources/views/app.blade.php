<!DOCTYPE html>
<html lang="en">

<head>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- AlpineJS -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        .swal2-timer-progress-bar {
            background: #accf9c !important;
        }
    </style>
</head>

<body>
    @yield('content')
    {{-- Success Message --}}
    @if(session('success'))
        <script>
            Swal.fire({
                toast: true,
                position: 'top',
                icon: 'success',
                title: '{{ session('success') }}',
                showConfirmButton: false,
                backdrop: true,
                timer: 3000,
                timerProgressBar: true,
                background: 'rgba(17,24,39,0.85)',
                color: '#fff',
            });
        </script>
    @endif

    {{-- Error Messages --}}
    @if($errors->any())
        <script>
            Swal.fire({
                toast: true,
                position: 'top',
                icon: 'error',
                title: '{{ $errors->first() }}',
                showConfirmButton: false,
                timer: 3500,
                timerProgressBar: true,
                background: 'rgba(17,24,39,0.85)',
                color: '#fff'
            });
        </script>
    @endif

    {{-- Warning Message --}}
    @if(session('warning'))
        <script>
            Swal.fire({
                toast: true,
                position: 'top',
                icon: 'warning',
                title: '{{ session('warning') }}',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                background: '#111827',
                color: '#fff'
            });
        </script>
    @endif
    {{-- Info Message --}}
    @if(session('info'))
        <script>
            Swal.fire({
                toast: true,
                position: 'center',
                icon: 'info',
                title: '{{ session('info') }}',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                background: '#111827',
                color: '#fff'
            });
        </script>
    @endif
</body>

</html>