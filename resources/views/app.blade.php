<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ChargeSpotter</title>
  @routes
  @vite('resources/js/app.js')
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
  <style>
    html, body, #app {
      height: 100%;
      margin: 0;
      padding: 0;
    }
    
    .map {
      height: 400px;
    }
    
    @media (max-width: 768px) {
      .map {
        height: 40vh;
        min-height: 250px;
      }
    }
  </style>
</head>
<body>
  @inertia
</body>
</html>
