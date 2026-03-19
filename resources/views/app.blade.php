<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title inertia>{{ env('APP_NAME', 'Akrahealth') }}</title>
    <link href="/images/favicon.webp" rel="shortcut icon" type="/images/favicon.webp" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Favicons -->
    <link href="/images/logo.webp" rel="icon">
    <link href="/images/akrahealth.webp" rel="Logo">

    {{--  <link href="{{asset('assets/css/nosh-timeline.css')}}" rel="stylesheet" />  --}}
    <link href="{{ asset('vendor/boxicons/css/boxicons.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet" />
    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/typography.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/style.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/responsive.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/EcommerceResponsive.css') }}" rel="stylesheet" />
    <!------- cdn ----->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/instantsearch.css@7.3.1/themes/reset-min.css"
        integrity="sha256-t2ATOGCtAIZNnzER679jwcFcKYfLlw01gli6F6oszk8=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/instantsearch.css@7.3.1/themes/algolia-min.css"
        integrity="sha256-HB49n/BZjuqiCtQQf49OdZn63XuKFaxcIHWf0HNKte8=" crossorigin="anonymous">



    <!-- Magnific Popup CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/magnific-popup.min.css">

    <!------- cdn End ----->
    <link href="{{ asset('css/ForntEnd/style.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/ForntEnd/app.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet" />
    <!--candly--->



    <!-- Calendly link widget begin -->
    <link href="https://assets.calendly.com/assets/external/widget.css" rel="stylesheet">
    <script src="https://assets.calendly.com/assets/external/widget.js" type="text/javascript" async></script>
    <!-- Calendly link widget end -->
    @routes
    @vite('resources/js/app.js')
    @inertiaHead
 
<script type="text/javascript">
(function(c,l,a,r,i,t,y){ c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)}; 
t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
})(window, document, "clarity", "script", "v8hr1p0h6h"); </script>
</head>

<body data-aos-easing="ease-in-out"data-aos-duration="1000" data-aos-delay="0">
    @inertia

</body>

<!-- Essential Scripts - Load in correct order -->
<script async src="https://www.instagram.com/embed.js"></script>

<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{ asset('js/popper.min.js') }}"></script>
<script src="{{ asset('js/bootstrap.min.js') }}"></script>
<script src="{{ asset('js/custom.js') }}"></script>
<script src="https://cdn.botpress.cloud/webchat/v3.3/inject.js"></script>
<script src="https://files.bpcontent.cloud/2025/11/13/12/20251113122449-5V4JI7V2.js" defer></script>
<!-- Instagram -->
</html>
