<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Order History')</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: '#1e2a78',
                        navyDark: '#16205c',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-black min-h-screen flex items-start justify-center p-6">

    <div class="w-full max-w-3xl bg-white rounded-xl shadow-2xl overflow-hidden">
        @yield('content')
    </div>

</body>
</html>