{{-- Offline marking support for the examiner pages: caches the marking pages,
     queues marks entered without a connection and syncs them later.
     See public/dist/js/examiner-offline.js and public/examiner-sw.js. --}}
<script>
    window.EXAMINER_OFFLINE = {
        userId: {{ (int) Auth::id() }},
        swUrl: @json(url('examiner-sw.js')),
        scope: @json(rtrim(parse_url(url('examiner'), PHP_URL_PATH), '/') . '/'),
        manifestUrl: @json(route('examiner.offline.manifest', [], false)),
        tokenUrl: @json(route('examiner.offline.token', [], false)),
    };
</script>
<script src="{{ asset('dist/js/examiner-offline.js') }}?v={{ filemtime(public_path('dist/js/examiner-offline.js')) }}"></script>
