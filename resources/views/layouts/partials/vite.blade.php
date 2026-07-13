@if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@else
    <style>
        body {
            margin: 0;
            font-family: Figtree, Instrument Sans, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #f8fafc;
            color: #0f172a;
        }

        a { color: inherit; }
        button, input, select, textarea { font: inherit; }
    </style>
@endif
