(function($) {
    $(function() {

        // Set data
        $('.global-opt.opt-all').prop('checked', isset(SMUI.objects) && isset(SMUI.objects.all));
        $('.global-opt.opt-post-types').prop('checked', isset(SMUI.objects) && isset(SMUI.objects.post_types));
        $('.global-opt.opt-taxonomies').prop('checked', isset(SMUI.objects) && isset(SMUI.objects.taxonomies));
        $('.global-opt.opt-users').prop('checked', isset(SMUI.objects) && isset(SMUI.objects.users));
        $('.exclude-post-types').val(isset(SMUI.post_types) ? SMUI.post_types : []);
        $('.exclude-post-ids').val(isset(SMUI.post_ids) ? SMUI.post_ids : '');
        $('.exclude-taxonomies').val(isset(SMUI.taxonomies) ? SMUI.taxonomies : []);
        $('.exclude-term-ids').val(isset(SMUI.term_ids) ? SMUI.term_ids : '');

        // Setup fSelect
        $('.exclude-post-types').fSelect({
            placeholder: 'Select post types to exclude',
            numDisplayed: 10
        });
        $('.exclude-taxonomies').fSelect({
            placeholder: 'Select taxonomies to exclude',
            numDisplayed: 10
        });

        // Fire toggle handler
        $(document).on('change', '.global-opt', function() {
            toggleViz();
        });

        toggleViz();
    });

    function isset(val) {
        return 'undefined' !== typeof val;
    }

    function toggleViz() {
        var exclude_all = $('.global-opt.opt-all').is(':checked');
        var exclude_pt = $('.global-opt.opt-post-types').is(':checked');
        var exclude_tax = $('.global-opt.opt-taxonomies').is(':checked');

        $('.wrap-post-types').toggleClass('hidden', exclude_all || exclude_pt);
        $('.wrap-taxonomies').toggleClass('hidden', exclude_all || exclude_tax);
        $('.global-opt.opt-post-types').closest('div').toggleClass('hidden', exclude_all);
        $('.global-opt.opt-taxonomies').closest('div').toggleClass('hidden', exclude_all);
        $('.global-opt.opt-users').closest('div').toggleClass('hidden', exclude_all);
    }
})(jQuery);
