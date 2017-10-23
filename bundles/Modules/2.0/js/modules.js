(function ($) {

    "use strict";
    var modulesSelect = {

        init: function () {

            var elem = $('.module-type'),
                select = elem.find('select'),
                value = select.val(),
                description = elem.find('.description'),
                slug = value.replace('module-metabox-', '');

            $('#' + value).show();

            for (var type in types) {
                if (slug === types[type].slug) {
                    description.text(types[type].description);
                }
            }

            this.bindSelect(select);

        },

        bindSelect: function(select){

            select.on('change', function(){

                elem = $(this),
                description = elem.siblings('.description').text(''),
                value = elem.val(),
                slug = value.replace('module-metabox-', '');

                $('[id^=module-metabox]').hide();
                $('#' + value).fadeIn();

                for (var type in types) {
                    if (slug === types[type].slug) {
                        description.text(types[type].description);
                    }
                }

            });

        }

    };

    if ($('body').hasClass('post-type-module')) {
        if (0 < $('.module-type').length) {
            modulesSelect.init();
        }
    }

    var modulesSort = $('form.sb-modules ul.sort-modules').sortable({
        placeholder: 'sort-module-placeholder',
        forcePlaceholderSize: true,
        opacity: 0.5
    });

    var hasChanged = function(){

        $('#modules-order').removeClass('button-secondary').addClass('button-primary').prop('disabled', false);

    };

    modulesSort.on('sortstop', hasChanged);
    $('form.sb-modules .module-visibility').on('has-toggled', hasChanged);

    var moduleSets = {

        init: function(){

            this.bindSave();
            this.bindDelete();
            this.bindLoad();

        },

        toggleModule: function(elem, visible){

            var toggle = $('.toggle-wrapper', elem),
                toggleVal = toggle.find('input');

            if (visible === toggleVal.val()) return;

            if (1 === parseInt(visible)) {
                toggle.addClass('is-active');
                toggleVal.val(1);
            }

            if (0 === parseInt(visible)) {
                toggle.removeClass('is-active');
                toggleVal.val(0);
            }

        },

        bindLoad: function(){

            var self = this;

            $('.module-set-list').on('click', '.load-module-set', function(e){

                e.preventDefault();

                var listWrapper = $('.sort-modules'),
                    currentList = listWrapper.children(),
                    savedSet = $(this).parents('li').data('set'),
                    order = [];

                    for (var key in savedSet) {

                        var obj = savedSet[key];
                        for (var id in obj) {
                            if (obj.hasOwnProperty(id)) {

                                var visible = obj[id];
                                var elem = $('li[data-id="' + id + '"]', listWrapper);

                                self.toggleModule(elem, visible);
                                order.push(elem);

                                hasChanged();

                            }
                        }

                    }

                    listWrapper.prepend(order);

            });

        },

        bindSave: function(){

            $('#save-module-set').on('click', function(e){

                var button = $(this),
                    input = $('#module-set-name'),
                    name = input.val(),
                    spinner = $('#new-module-save'),
                    list = $('.module-set-list'),
                    modules = [];

                if ('' === name) return;

                button.prop('disabled', true);
                spinner.show();

                $('.sort-modules').children().each(function(i, val){

                    var module = $(this),
                        id = module.data('id'),
                        visible = module.find('input').val();

                    modules.push({'id' : id, 'visible' : visible });

                });

                $.post(ajaxurl, {

                    'action'  : 'save_new_module_set',
                    'name'    : name,
                    'set'     : modules

                }, function(data) {

                    if ('OK' === data.status) {

                        element = $.trim(($('#new-module-set')).html());
                        element = element.replace('{{id}}', name);
                        element = element.replace('{{name}}', name);
                        element = element.replace('{{url}}', encodeURIComponent(name));

                        input.val('');
                        $(element).appendTo(list).data('set', JSON.parse(data.set));
                        list.removeClass('hidden');

                    }

                    spinner.hide();

                });

            });

        },

        bindDelete: function(){

            $('.module-set-list').on('click', '.delete-module-set', function(e){

                e.preventDefault();

                var spinner = $('#new-module-save').show(),
                    element = $(this).parents('li');

                $.post(ajaxurl, {
                    'action'  : 'delete_module_set',
                    'name'    : element.data('id'),

                }, function(data) {

                    if ('OK' === data.status) {

                        element.fadeOut('fast', function(){
                            $(this).remove();

                            var list = $('.module-set-list');

                            if (0 === list.children().length) {
                                list.addClass('hidden');
                            }

                        });

                    }

                    spinner.hide();

                });

            });

        }

    };

    moduleSets.init();

}(jQuery));