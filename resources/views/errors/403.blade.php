<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>403 Forbidden</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-50">
  <div class="max-w-md text-center p-8 bg-white shadow-md rounded-xl">
    <h1 class="text-5xl font-bold mb-2">Akses terhad</h1>
    <p class="text-lg mb-4">Anda belum diberi akses atau tidak dibenarkan untuk melawat laman ini</p>
    <p class="mb-6">Jika anda rasa ini adalah kesilapan, sila hubungi admin.</p>
    <a href="{{ url('/') }}" class="inline-block px-4 py-2 border rounded">Kembali ke halaman utama</a>
  </div>
</body>
</html>
