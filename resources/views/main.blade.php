<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" href="/favicon.ico">

    <title>Delivery MCRM</title>
    @vite('resources/css/main.css')
</head>
<body>
    <div id="app"></div>
    @vite('resources/js/main.js')
</body>
</html>
