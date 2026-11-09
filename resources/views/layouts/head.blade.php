<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" type="image/svg+xml" href="{{ asset('logo.svg') }}">
<script>
    try {
        if (localStorage.getItem('abc-theme') === 'dark') document.documentElement.classList.add('dark');
    } catch (e) {}
</script>
@vite(['resources/css/app.css', 'resources/js/app.js'])
