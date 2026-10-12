{{-- Countries map. Replaces the table and cards on admin/countries/list.
     Expects $countryRows: array of [name, iso2, continent, hospitals, trainees, fellows, examiners, url]
     (see README.md for the controller snippet). --}}
<link rel="stylesheet" href="{{ asset('dist/css/countries-map.css') }}">

<div class="cm" data-json="countries-map-data" data-geo="{{ asset('dist/data/world-map.json') }}">
    <div class="cm-card">
        <div class="cm-head">
            <div>
                <h2 class="cm-title">Interactive Network Map</h2>
                <div class="cm-sub">Click a highlighted country to see its hospitals, trainees, fellows and examiners.</div>
            </div>
            <div class="cm-tools">
                <label class="sr-only" for="cm-find">Find a country</label>
                <input id="cm-find" class="cm-find" type="text" placeholder="Find a country…" autocomplete="off">
                <div class="cm-seg">
                    <button type="button" data-view="africa">Africa</button>
                    <button type="button" data-view="world">World</button>
                </div>
            </div>
        </div>

        <div class="cm-stage" tabindex="0" aria-label="Map. Arrow keys move, plus and minus zoom, Escape closes the popup.">
            <div class="cm-hint">Drag to move</div>
            <div class="cm-zoom">
                <button type="button" data-act="in" aria-label="Zoom in"><i class="fas fa-plus"></i></button>
                <button type="button" data-act="out" aria-label="Zoom out"><i class="fas fa-minus"></i></button>
                <button type="button" data-act="reset" aria-label="Reset view"><i class="fas fa-undo"></i></button>
            </div>
            <div class="cm-legend">
                <span><i style="background:var(--cm-has)"></i>Has records</span>
                <span><i style="background:var(--cm-land)"></i>No records</span>
                <span><i style="background:var(--cm-primary)"></i>Selected</span>
            </div>
            <div class="cm-pop" hidden>
                <div class="cm-pop-head">
                    <div class="cm-flag"><i class="fas fa-flag"></i></div>
                    <div><div class="cm-pop-name"></div><div class="cm-pop-net">COSECSA network</div></div>
                    <button type="button" class="cm-x" aria-label="Close"><i class="fas fa-times"></i></button>
                </div>
                <div class="cm-grid">
                    <div class="cm-stat"><b>Hospitals</b><span data-k="hospitals">0</span></div>
                    <div class="cm-stat trainees"><b>Trainees</b><span data-k="trainees">0</span></div>
                    <div class="cm-stat fellows"><b>Fellows</b><span data-k="fellows">0</span></div>
                    <div class="cm-stat examiners"><b>Examiners</b><span data-k="examiners">0</span></div>
                </div>
                <a class="cm-go" href="#">View country details <i class="fas fa-chevron-right"></i></a>
            </div>
        </div>

        <div class="cm-jump">
            <div class="cm-jump-label">Jump to a country</div>
            <div class="cm-groups"></div>
        </div>
    </div>

    <script type="application/json" id="countries-map-data">@json(['countries' => $countryRows, 'continents' => config('continents')])</script>
</div>

<script src="{{ asset('dist/js/pages/countries-map.js') }}" defer></script>
