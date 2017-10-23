/*globals
    jQuery,
    ajaxurl
*/

(function ($) {

    "use strict";
    var sbUtils = window.sbUtils || {};

    // Copy post
    $('.row-actions').on('click', 'a.duplicate', function (e) {
        e.preventDefault();

        var refresh = $(this).data('location');

        $.post(ajaxurl, {
            'action'  : 'duplicate_post',
            'post_id' : $(this).data('post-id')
        }, function (data) {
            if (data === 'OK') {
                window.location.href = refresh;
            }
        });

    });

    // Template meta boxes
    sbUtils.toggleMetabox = function () {

        var select = $('#page_template').val();

        if (select === undefined) {
            return;
        }

        if (select === '') {
            select = 'default';
        }

        $('.sb-template-meta-box').hide();

        var template = select.split('/');
        template = template.pop();

        if (template === 'default') {
            $('.sb-default').fadeIn();
            return;
        }

        $('.sb-' + template.replace('.php', '')).fadeIn();
        return;

    };

    sbUtils.toggleMetabox();

    // Toggle Profiles metabox
    $('#page_template').on('change', sbUtils.toggleMetabox);

}(jQuery));