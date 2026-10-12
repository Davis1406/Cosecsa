{{-- Starts the JSON rows request for a data-rows-url table while the page's
     scripts are still downloading; dtAjax() in custom.js picks it up. --}}
<script>
    window.dtRowsPrefetch = window.dtRowsPrefetch || {};
    window.dtRowsPrefetch[@json($url)] = fetch(@json($url), {
        credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    }).then(function (r) { return r.ok ? r.json() : Promise.reject(r.status); });
</script>
