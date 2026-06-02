<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>404 Not Found</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen px-6 py-12">
  <div class="bg-white p-8 rounded-lg shadow-md text-center max-w-md w-full">
    <h1 class="text-6xl font-extrabold text-red-600 mb-4">404</h1>
    <p class="text-xl font-semibold text-gray-800">Oops! Page not found</p>
    <p class="mt-2 text-gray-600">The page you're looking for doesn't exist or may have been moved.</p>
    <a href="{{ url('/') }}" class="mt-6 inline-block bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-full shadow transition duration-300">
      Go Back Home
    </a>
  </div>
</body>
</html>
