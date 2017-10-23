(function ($) {

    $.fn.reverse = [].reverse;

    var sbSortable = sbSortable || {};

    sbSortable.sortByDate = function (a, b) {

        var aDate = $(a).data('date'),
            bDate = $(b).data('date');

        return ((aDate < bDate) ? -1 : ((aDate > bDate) ? 1 : 0));

    };

    sbSortable.savedOrder = function (a, b) {

        var aOrder = $(a).data('order'),
            bOrder = $(b).data('order');

        return ((aOrder < bOrder) ? -1 : ((aOrder > bOrder) ? 1 : 0));

    };

    $('#page-order-form').on('submit', function () {
        $('span.spinner').show();
        return true;
    });

    $('ul.page-order').sortable({ placeholder: 'page-order-placeholder' });

    $('.child-arrow').on('click', function (e) {
        e.preventDefault();
        var parent = $(this).parent('div.page');
        parent.next().slideToggle();
        parent.toggleClass('expanded');
    });

    $('#expand-all').on('click', function (e) {
        e.preventDefault();

        if ($(this).hasClass('expanded')) {
            $('ul.children').slideUp();
            $(this).removeClass('expanded').text('Visa alla undersidor');
            $('div.page').removeClass('expanded');
        } else {
            $(this).addClass('expanded');
            $('ul.children').slideDown();
            $(this).text('Dölj alla undersidor');
            $('div.page').addClass('expanded');
        }

    });

    $('.category-select').on('change', function () {
        window.location = $(this).data('base-url') + '&term_id=' + $(this).val();
    });

    if ($('.sb-page-order .page-order').children().length === 0) {
        $('.sort-helper').hide();
    }

    $('#sort-helper-sort').on('click', function (e) {
        e.preventDefault();

        var wrapper = $('.sb-page-order .page-order'),
            objects = wrapper.children(),
            options = $('#sort-helper-options'),
            value = options.val(),
            option = $('option[value="' + value + '"]', options),
            order = option.data('order'),
            method = option.data('method');

        var fn = sbSortable[method];

        if (typeof fn === 'function') {
            objects.sort(fn);
        } else {
            console.log('Error: cant find method');
            return false;
        }

        if ('ASC' !== order) {
            objects.reverse();
        }

        wrapper.append(objects);

    });

})(jQuery);
