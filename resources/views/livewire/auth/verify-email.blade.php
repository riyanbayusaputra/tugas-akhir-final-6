<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email - {{ $store->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
       tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: "{{$store->primary_color ?? '#ff6666'}}",
                        secondary: "{{$store->secondary_color ?? '#818CF8'}}",
                        accent: '#C7D2FE',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50">
    <!-- Verification Page -->
    <div class="max-w-[480px] mx-auto bg-white min-h-screen relative shadow-lg">
        <div class="p-6">
            <!-- Logo & Verification Text -->
            <div class="text-center mb-8 pt-8">
                <div class="w-24 h-24 bg-white rounded-full mx-auto flex items-center justify-center mb-6">
                    <img src="{{ asset('storage/' . $store->image) }}" alt="Logo" class="w-18 h-18">
                </div>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Verifikasi Email</h1>
                <p class="text-gray-500 mb-4">Silakan verifikasi email Anda untuk mengakses semua fitur</p>
                
                @if (session('message'))
                    <div class="p-4 mb-4 text-sm text-primary bg-primary/10 rounded-xl">
                        {{ session('message') }}
                    </div>
                @endif
                
                <p class="text-gray-600 text-sm mb-6">
                    Sebelum melanjutkan, mohon periksa email Anda untuk link verifikasi.
                    Jika Anda tidak menerima email verifikasi, silakan klik tombol di bawah untuk mengirim ulang.
                </p>

                <form method="POST" action="{{ route('verification.send') }}" class="space-y-4">
                    @csrf
                    <button type="submit" class="w-full bg-primary text-white py-3 rounded-xl font-medium hover:bg-primary/90 transition-colors">
                        Kirim Ulang Email Verifikasi
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</body>
</html>