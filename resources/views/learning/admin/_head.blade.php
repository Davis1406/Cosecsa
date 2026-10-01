<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-8">
                <h4 class="mb-0">{{ $title }}</h4>
                @isset($subtitle)<div class="text-muted" style="font-size:13px;">{{ $subtitle }}</div>@endisset
            </div>
            <div class="col-sm-4 text-right">{{ $actions ?? '' }}</div>
        </div>
    </div>
</section>
