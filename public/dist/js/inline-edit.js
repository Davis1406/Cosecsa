/**
 * ═══════════════════════════════════════
 * INLINE PENCIL EDIT – COSECSA
 * Reusable quick-edit component
 * ═══════════════════════════════════════
 *
 * Usage:
 *   <span class="ie-field" data-ie="fieldname" data-ie-type="text|select|email|number"
 *         data-ie-value="current" data-ie-options='{"opt":"Label"}'
 *         data-ie-url="/admin/module/{id}/quick-update" data-ie-csrf="token">
 *       <span class="ie-value">Current Value</span>
 *   </span>
 *
 * Options (data-ie-* attributes):
 *   data-ie          – field name sent to API
 *   data-ie-type     – input type: text | email | number | select | date
 *   data-ie-value    – current value
 *   data-ie-options  – JSON map of {value: label} for select fields
 *   data-ie-url      – AJAX POST endpoint (must accept field + value)
 *   data-ie-csrf     – CSRF token
 *   data-ie-label      – human label for the popover title (optional)
 *   data-ie-readonly   – if present, field is display-only (no edit)
 *   data-ie-searchable – for type=select: render as a searchable select2
 *                        dropdown instead of a plain native <select>
 *   data-ie-tags       – for type=select + data-ie-searchable: also let
 *                        staff type a value that isn't in data-ie-options
 *                        (e.g. free-text fields being migrated to a
 *                        canonical list — existing values that don't match
 *                        anything in the list stay editable/selectable)
 */

(function () {
    'use strict';

    /* ── Singleton state ─────────────────────────────────────────── */
    var $activePopover = null;
    var $activeField = null;
    var closeTimeout = null;

    /* ── Helpers ─────────────────────────────────────────────────── */
    function escapeHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    function fieldLabel($el) {
        return $el.data('ie-label') || $el.data('ie').replace(/_/g, ' ').replace(/\b\w/g, function (c) { return c.toUpperCase(); });
    }

    /* ── Build editor input based on type ────────────────────────── */
    function buildEditor($field) {
        var type = $field.data('ie-type') || 'text';
        var current = String($field.data('ie-value') || '');
        var html = '';

        if (type === 'select') {
            var opts = $field.data('ie-options');
            if (typeof opts === 'string') {
                try { opts = JSON.parse(opts); } catch (e) { opts = {}; };
            }
            var searchable = $field.data('ie-searchable');
            html = '<select class="ie-editor-select" data-ie-editor' + (searchable ? ' style="width:100%"' : '') + '>';
            html += '<option value=""></option>';
            // For tag-style fields the current value may be free text that
            // doesn't appear in data-ie-options (e.g. pre-existing data that
            // predates a canonical list) — keep it selectable rather than
            // silently losing it the moment the popover opens.
            if ($field.data('ie-tags') && current && !Object.prototype.hasOwnProperty.call(opts, current)) {
                var hasMatchingValue = Object.keys(opts).some(function (k) { return String(opts[k]) === current; });
                if (!hasMatchingValue) {
                    html += '<option value="' + escapeHtml(current) + '" selected>' + escapeHtml(current) + '</option>';
                }
            }
            $.each(opts, function (val, label) {
                var selected = String(val) === current ? ' selected' : '';
                html += '<option value="' + escapeHtml(String(val)) + '"' + selected + '>' + escapeHtml(String(label)) + '</option>';
            });
            html += '</select>';
        } else if (type === 'date') {
            html = '<input class="ie-editor-input" type="date" data-ie-editor value="' + escapeHtml(current) + '">';
        } else {
            var inputType = type === 'email' ? 'email' : (type === 'number' ? 'number' : 'text');
            html = '<input class="ie-editor-input" type="' + inputType + '" data-ie-editor value="' + escapeHtml(current) + '">';
        }
        return html;
    }

    /* ── Create popover element ──────────────────────────────────── */
    function createPopover() {
        var $pop = $('<div class="ie-popover">' +
            '<div class="ie-popover-header">' +
                '<span class="ie-popover-title"></span>' +
                '<button class="ie-popover-close" type="button">&times;</button>' +
            '</div>' +
            '<div class="ie-popover-body"></div>' +
            '<div class="ie-editor-footer">' +
                '<button class="ie-btn-cancel" type="button">Cancel</button>' +
                '<button class="ie-btn-save" type="button">Save</button>' +
            '</div>' +
        '</div>');
        $('body').append($pop);
        return $pop;
    }

    /* ── Position popover below the field ────────────────────────── */
    function positionPopover($pop, $field) {
        var fieldRect = $field[0].getBoundingClientRect();
        var popWidth = 340; // matches .ie-popover's max-width

        // Try to position below the field, aligned left
        var top = fieldRect.bottom + 6;
        var left = fieldRect.left;

        // Ensure it doesn't go off-screen right
        if (left + popWidth > window.innerWidth - 16) {
            left = window.innerWidth - popWidth - 16;
        }
        // Ensure it doesn't go off-screen left
        if (left < 16) {
            left = 16;
        }
        // If it would go below viewport, show above
        if (top + 200 > window.innerHeight) {
            top = fieldRect.top - 6;
            $pop.css({ top: 'auto', bottom: (window.innerHeight - fieldRect.top + 6) + 'px', left: left + 'px' });
        } else {
            $pop.css({ top: top + 'px', left: left + 'px', bottom: 'auto' });
        }
    }

    /* ── Show the editor popover ─────────────────────────────────── */
    function showPopover($field) {
        // Close any existing popover first
        hidePopover();

        var $pop = createPopover();
        $activeField = $field;
        $activePopover = $pop;

        // Set title
        $pop.find('.ie-popover-title').text('Edit: ' + fieldLabel($field));

        // Build and insert editor
        $pop.find('.ie-popover-body').html(buildEditor($field));

        // Position and show
        positionPopover($pop, $field);
        $pop.show();

        // Searchable select (data-ie-searchable) — upgrade the plain
        // <select> to select2. dropdownParent must be the popover itself:
        // it's appended straight to <body> and can be repositioned/removed
        // at any time, so select2's default (attaching to <body> globally)
        // would leave a stray dropdown behind. tags:true (data-ie-tags)
        // additionally lets staff type a value not in the option list.
        if ($field.data('ie-type') === 'select' && $field.data('ie-searchable') && window.jQuery && $.fn.select2) {
            $pop.find('.ie-editor-select').select2({
                dropdownParent: $pop,
                width: '100%',
                tags: !!$field.data('ie-tags'),
                placeholder: '— Select —',
                allowClear: true
            }).on('select2:open', function () {
                // select2 steals focus into its own search box — the
                // Enter-to-save handler below is bound to the original
                // [data-ie-editor], so re-point it there too.
                setTimeout(function () {
                    var $search = document.querySelector('.select2-container--open .select2-search__field');
                    if ($search) $search.focus();
                }, 0);
            });
        } else {
            // Focus the input (select2 fields focus their own search box instead)
            $pop.find('[data-ie-editor]').focus();
        }

        // Wire up events
        $pop.find('.ie-popover-close').on('click', hidePopover);
        $pop.find('.ie-btn-cancel').on('click', hidePopover);
        $pop.find('.ie-btn-save').on('click', function () { savePopover(); });

        // Enter to save, Escape to cancel
        $pop.find('[data-ie-editor]').on('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                savePopover();
            }
            if (e.key === 'Escape') {
                hidePopover();
            }
        });
    }

    /* ── Hide the editor popover ─────────────────────────────────── */
    function hidePopover() {
        if ($activePopover) {
            $activePopover.remove();
            $activePopover = null;
            $activeField = null;
        }
    }

    /* ── Save via AJAX ───────────────────────────────────────────── */
    function savePopover() {
        if (!$activeField || !$activePopover) return;

        var $field = $activeField;
        var $pop = $activePopover;
        var $input = $pop.find('[data-ie-editor]');
        var $btn = $pop.find('.ie-btn-save');
        var newVal = $input.val();
        var url = $field.data('ie-url');
        var csrf = $field.data('ie-csrf');
        var fieldName = $field.data('ie');

        if (!url) {
            console.error('[InlineEdit] No data-ie-url set on field:', fieldName);
            return;
        }

        // Disable controls during save
        $input.prop('disabled', true);
        $btn.prop('disabled', true).html('<span class="ie-spinner"></span> Saving…');

        $.ajax({
            url: url,
            method: 'POST',
            data: {
                _token: csrf,
                field: fieldName,
                value: newVal
            },
            success: function (res) {
                // Update the display value
                var displayValue = res.label || newVal;
                $field.find('.ie-value').text(displayValue);
                $field.data('ie-value', newVal);

                // Flash success
                $field.addClass('ie-flash-success');
                setTimeout(function () { $field.removeClass('ie-flash-success'); }, 900);

                hidePopover();
            },
            error: function (xhr) {
                // Flash error
                $field.addClass('ie-flash-error');
                setTimeout(function () { $field.removeClass('ie-flash-error'); }, 900);

                // Re-enable controls
                $input.prop('disabled', false);
                $btn.prop('disabled', false).html('Save');

                var msg = 'Save failed.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                alert(msg);
            }
        });
    }

    /* ── Close popover on outside click ──────────────────────────── */
    $(document).on('click', function (e) {
        if ($activePopover && $activePopover.length) {
            if (!$(e.target).closest('.ie-popover, .ie-field, .ie-field-block').length) {
                hidePopover();
            }
        }
    });

    /* ── Bind click handlers to all inline-edit fields ───────────── */
    function bindInlineEditFields() {
        // Delegate to handle dynamically added fields
        $(document).off('click.ieField', '.ie-field[data-ie], .ie-field-block[data-ie]');
        $(document).on('click.ieField', '.ie-field[data-ie], .ie-field-block[data-ie]', function (e) {
            var $field = $(this);

            // Don't edit if readonly
            if ($field.data('ie-readonly') !== undefined) return;

            // Don't edit if clicking a link
            if ($(e.target).closest('a').length) return;

            e.preventDefault();
            e.stopPropagation();

            // Toggle if same field
            if ($activeField && $activeField.is($field)) {
                hidePopover();
                return;
            }

            showPopover($field);
        });
    }

    /* ── Public API ──────────────────────────────────────────────── */
    window.InlineEdit = {
        init: bindInlineEditFields,
        hide: hidePopover,
        refresh: function ($field, newValue, displayLabel) {
            $field.data('ie-value', newValue);
            $field.find('.ie-value').text(displayLabel || newValue);
        }
    };

    /* ── Auto-init on DOM ready ──────────────────────────────────── */
    $(document).ready(function () {
        bindInlineEditFields();
    });

})();
