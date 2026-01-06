<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistema de Encuestas')</title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="@yield('meta_description', 'Sistema de Encuestas - Participa en nuestras encuestas')">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('og_title', 'Sistema de Encuestas')">
    <meta property="og:description" content="@yield('og_description', 'Participa en esta encuesta y comparte tu opinión')">
    <meta property="og:image" content="@yield('og_image_full', url('images/default-survey-preview.jpg'))">
    <meta property="og:image:secure_url" content="@yield('og_image_full', url('images/default-survey-preview.jpg'))">
    <meta property="og:image:type" content="image/jpeg">
    <meta property="og:image:width" content="@yield('og_image_width', '1200')">
    <meta property="og:image:height" content="@yield('og_image_height', '630')">
    <meta property="og:image:alt" content="@yield('og_title', 'Sistema de Encuestas')">
    <meta property="og:locale" content="es_CO">
    <meta property="og:site_name" content="Cultura Popular Bucaramanga">

    <!-- Metadatos adicionales para mejor presentación -->
    <meta property="fb:app_id" content="">
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="@yield('og_title', 'Sistema de Encuestas')">
    <meta property="twitter:description" content="@yield('og_description', 'Participa en esta encuesta y comparte tu opinión')">
    <meta property="twitter:image" content="@yield('og_image', asset('images/default-survey-preview.jpg'))">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" type="image/svg+xml" href="{{ asset('favicon-alt.svg') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon.svg') }}">
    <meta name="theme-color" content="#fdd71a">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh;">
    @yield('content')

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Meta Pixel Code -->
    <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '1184935605921555');
        fbq('track', 'PageView');
    </script>
    <noscript>
        <img height="1" width="1" style="display:none"
             src="https://www.facebook.com/tr?id=1184935605921555&ev=PageView&noscript=1" />
    </noscript>
    <!-- End Meta Pixel Code -->
</body>
</html>

