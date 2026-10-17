@php
    $rows       = $payload['items'] ?? [];
    $head       = collect($rows)->firstWhere('type', 'head');
    $body       = collect($rows)->where('type', '!=', 'head')->values();
    $rowHeaders = !empty($payload['row_headers']);
    $title      = $payload['title'] ?? null;
@endphp

<div class="block block-table">
    @if($title)<div class="table-title">{!! \App\Support\LearningView::html($title) !!}</div>@endif
    <div class="table-scroll" tabindex="0" role="region" aria-label="{{ trim(strip_tags($title ?? 'Table')) }}">
        <table class="lms-table{{ $rowHeaders ? ' has-row-headers' : '' }}">
            @if($head)
                <thead>
                    <tr>
                        @foreach($head['cells'] ?? [] as $cell)
                            <th scope="col">{!! \App\Support\LearningView::html($cell['paragraph'] ?? null) !!}</th>
                        @endforeach
                    </tr>
                </thead>
            @endif
            <tbody>
                @foreach($body as $row)
                    <tr>
                        @foreach($row['cells'] ?? [] as $cell)
                            @if($rowHeaders && $loop->first)
                                <th scope="row">{!! \App\Support\LearningView::html($cell['paragraph'] ?? null) !!}</th>
                            @else
                                <td>{!! \App\Support\LearningView::html($cell['paragraph'] ?? null) !!}</td>
                            @endif
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
