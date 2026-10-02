<div class="modal fade" id="lmsPreviewModal" tabindex="-1" role="dialog" aria-labelledby="lmsPreviewModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title" id="lmsPreviewModalTitle">Course preview</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="lmsPreviewFrame" src="about:blank" title="Course preview" style="width:100%; height:78vh; border:0; display:block;"></iframe>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(function () {
        var $modal = $('#lmsPreviewModal');
        var $frame = $('#lmsPreviewFrame');
        if (!$modal.length) return;

        $(document).on('click', '#lmsPreviewLink', function (e) {
            e.preventDefault();
            $frame.attr('src', this.href);
            $modal.modal('show');
        });

        $modal.on('hidden.bs.modal', function () {
            $frame.attr('src', 'about:blank');
        });
    });
</script>
@endpush