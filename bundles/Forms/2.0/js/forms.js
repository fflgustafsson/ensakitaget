/*globals
    jQuery,
    wp,
    ajaxurl,
    colorFields,
    dateFields,
    dateDefaults,
    faders
*/
(function ($) {

    "use strict";
    var sbForms = window.sbForms || {};

    function handleMaxNumber(wrapper) {
        if (wrapper.data('max-number') === $('ul.sb-selected-display', wrapper).children().length) {
            return true;
        }
        return false;
    }

    // Media Chooser New
    function openMediaChooser(settings) {

        var title = (settings.multiple) ? 'Välj bilder' : 'Välj bild',
            file_frame;

        wp.media.frames.file_frame = wp.media({
            title: title,
            multiple: settings.multiple
        });

        file_frame = wp.media.frames.file_frame;

        var maxNumber = settings.parent.data('max-number'),
            currentNumber = $('ul.sb-selected-display', settings.parent).children().length,
            fetchMaxNumber = (maxNumber - currentNumber);

        file_frame.on('select', function () {

            var attachments = file_frame.state().get('selection').toJSON();

            var media_ids = [];
            $.each(attachments, function (i, val) {
                if ((i + 1) > fetchMaxNumber && maxNumber) {
                    return false;
                }
                media_ids.push(val.id);
            });

            $.post(ajaxurl, {
                'action'     : 'get_attachment_element',
                'media_id'   : media_ids,
                'media_size' : settings.size,
                'remove'     : settings.remove,
                'max-number' : settings.maxNumber,
                'name'       : settings.name
            }, function (data) {

                if (settings.multiple && maxNumber !== 'false') {
                    if (0 < fetchMaxNumber) {
                        data = data.slice(0, fetchMaxNumber);
                    }
                }

                attachments = data.join('');
                if (settings.multiple) {
                    var elements = $(attachments).hide();

                    // remove empty
                    settings.display.find('.hidden').remove();

                    settings.display.append(elements);
                    elements.fadeIn('fast'); // lägg till

                    if (handleMaxNumber(settings.parent)) {
                        settings.button.attr('disabled', 'true');
                    }

                } else {
                    settings.display.fadeOut('fast', function () {
                        $(this).html(attachments).fadeIn('fast'); // ersätt
                    });
                }

                // run trigger for multiform indexing
                $(document).trigger('form-updated');

            }, 'json');

        });

        file_frame.open();
    }

    // Image chooser
    $('#wpbody-content').on('click', 'input.sb-media-upload', function () {

        var parent = $(this).parents('div.sb-selected-wrapper'),
            imageSize = parent.data('image-size'),
            remove = parent.data('remove'),
            multiple = parent.data('multiple'),
            maxNumber = parent.data('max-number'),
            display = parent.find('ul.sb-selected-display'),
            name = parent.data('name'),
            settings = {'parent': parent, 'size': imageSize, 'remove': remove, 'multiple': multiple, 'display': display, 'button': $(this), 'maxNumber': maxNumber, 'name': name};

        openMediaChooser(settings);
        return false;

    });

    // Remove selected (image, post)
    $('#wpbody-content').on('click', 'a.remove-selected', function (e) {
        e.preventDefault();
        var element = $(this).parent('li'),
            wrapper = element.parents('div.sb-selected-wrapper');

        element.fadeOut(400, function () {
            $(this).parents('.sb-selected-display').trigger('remove-image');
            $(this).remove();
            wrapper.trigger('after-selected-remove');
        });

        if (handleMaxNumber(wrapper)) {
            $('input.sb-media-upload', wrapper).attr('disabled', false);
        }

    });

    // Remove has post
    $('#wpbody-content').on('after-selected-remove', 'div.sb-selected-wrapper', function () {
        if (0 === $('.selected-posts', this).children().length) {
            $(this).removeClass('has-posts');
        }
    });

    // Remove selection
    $('ul.sb-selected-wrapper').disableSelection();
    $('div.color-wrapper').disableSelection();

    // Color Picker
    if (colorFields !== undefined) {

        $.each(colorFields, function (field, value) {
            var elem = '.' + field + ' input',
                colorbox = $(elem).parents('.color-wrapper').find('.color-box');

            $(elem).iris({
                'default'   : value.default,
                'palettes'  : value.palettes,
                'hide'      : true,
                // event unused
                change: function (event, ui) {
                    colorbox.css('backgroundColor', ui.color.toString());
                }

            });

        });

        $('#wpbody-content').on('click', 'button.default-color', function (e) {
            e.preventDefault();

            var setColor = $(this).data('default').toLowerCase(),
                wrapper = $(this).parents('.color-wrapper');
            wrapper.find('input[type=text]').val(setColor);
            wrapper.find('.color-box').css({'backgroundColor' : setColor});

        });

        $('#wpbody-content').on('click', '.color-box', function () {
            $(this).next().iris('toggle');
        });

        $('#wpbody-content').on('focus', '.color-picker', function () {
            $(this).iris('toggle');
        });

        $('#wpbody-content').on('click', function (e) {

            if ($(e.target).hasClass('color-box')) {
                return;
            }

            if ($(e.target).hasClass('color-picker')) {
                return;
            }

            if ($(e.target).hasClass('iris-palette')) {
                return;
            }

            $('.iris-picker:visible').each(function () {
                $(this).prev('input').iris('hide');
            });

        });

    }

    // Date Picker
    if (dateFields !== undefined) {
        $.each(dateFields, function (field, value) {
            $('.' + field + ' input').datepicker($.extend({}, dateDefaults, value));
        });

        $('#wpbody-content').on('click', '.calendar-icon', function () {
            $(this).prev('input').datepicker('show');
        });

    }

    // Remove form element
    $('body').on('click', '.remove-form-element', function (e) {
        e.preventDefault();
        var wrapper = $(this).data('wrapper');
        $(this).parents(wrapper).css({'background-color' : 'red'}).fadeOut(400, function () {
            var parent = $(this).parent();
            $(this).remove();
            parent.trigger('check-no-elements');

            // run trigger for multiform indexing
            $(document).trigger('form-updated');

        });

    });

    // Add form element
    $('body').on('click', '.add-form-element', function (e) {
        e.preventDefault();
        var template = $(this).data('template'),
            wrapper = $(this).data('wrapper'),
            element = $.trim(($(template)).html());

        $(wrapper).append(element);
        $(wrapper).trigger('check-no-elements');

        // run trigger for multiform indexing
        $(document).trigger('form-updated');

    });

    // Sortable form elements
    if ($('.sb-forms-sortable').length > 0) {

        var formsSortable = $('.sb-forms-sortable');

        formsSortable.each(function () {
            var wrapper = $(this),
                sortWrapper = wrapper.data('sort-wrapper'),
                placeHolder = wrapper.data('sort-placeholder') || 'sb-forms-placeholder';
            $('body').on('mouseenter', sortWrapper, function () {
                $(this).parents('.sb-forms-sortable').sortable({
                    placeholder: placeHolder,
                    forcePlaceholderSize: true,
                    opacity: 0.5
                });
            });
        });

        if (formsSortable.data('sort-function')) {

            var fn = sbForms[formsSortable.data('sort-function')];
            if (typeof fn === 'function') {
                formsSortable.on('sortstop', fn);
            } else {
                console.log('Error: cant find method: ' + formsSortable.data('sort-function'));
                return false;
            }

        }

    }

    // Handle remove of images in multi forms
    $('.sb-multi-wrapper').on('after-selected-remove', '.sb-selected-wrapper', function () {

        var selectedDisplay = $('.sb-selected-display', this),
            templateID = $(this).parents('.sb-multi-wrapper').find('.add-form-element').data('template'),
            template = $.trim(($(templateID)).html());

        if (0 < selectedDisplay.children().length) {
            return;
        }

        $(template).find('.sb-selected-display li').appendTo(selectedDisplay);

    });

    // Chosen articles widget
    var postSelect = {

        init: function () {

            this.typeTimer = null;
            this.searchPosts();
            this.bindSearchList();
            this.bindSearchEvent();
            this.bindRemoveSelected();
            this.bindChooseFromList();
            this.filterPosts();
            this.bindEscape();

            $(document).on('click', function () {
                $('.search-results:visible').hide();
            });

            $('.sb-post-select-wrapper').on('after-selected-remove', function () {
                $(this).removeClass('has-post-list');
                $('.list-wrapper', $(this)).hide();
                $('.choose-post-from-list').show();
            });

        },

        decodeEntities: function (str) {

            var textArea = document.createElement('textarea');
            textArea.innerHTML = str;
            return textArea.value;

        },

        searchPosts: function () {

            var self = this,
                typeTimer = null;

            $(document).on('keyup', 'input.sb-post-search', function () {

                var scope = $(this),
                    currentValue = scope.val(),
                    wrapper = $(this).parents('.sb-post-select-wrapper'),
                    results = $('ul.search-results', wrapper),
                    postType = wrapper.data('posttype'),
                    metaKey = wrapper.data('metakey'),
                    limit = wrapper.data('search-limit'),
                    actionName = wrapper.data('action'),
                    loader = $('.spinner', wrapper);

                if (currentValue === '') {
                    return false;
                }

                if (actionName === undefined) {
                    actionName = 'get_post_search_result';
                }

                clearTimeout(typeTimer);
                typeTimer = setTimeout(function () {

                    if (wrapper.data('keep-string') === currentValue) {
                        return false;
                    }
                    wrapper.data('keep-string', currentValue);

                    loader.addClass('visible');
                    $.post(ajaxurl, {
                        'action'     : actionName,
                        'post_type'  : postType,
                        'meta_key'   : metaKey,
                        'search'     : currentValue,
                        'limit'      : limit,
                        'field'      : wrapper.data('name')
                    }, function (data) {

                        if (data.length > 0) {
                            self.showSearch(data, results, loader, wrapper);
                        } else {
                            results.hide();
                            loader.removeClass('visible');
                        }
                        return;

                    }, 'json');

                }, 300);

            });

        },

        showSearch: function (data, results, loader, wrapper) {

            var shortenTitle = wrapper.data('shorten-title');

            var elem = 'a';
            if (handleMaxNumber(wrapper)) {
                elem = 'span';
            }

            var li = $.map(data, function (post) {

                var post_title = post.post_title;
                if (shortenTitle === true) {
                    post_title = post.short_title;
                }

                var a = $('<' + elem + ' />', {
                    href: '',
                    'class': 'select-post',
                    text: postSelect.decodeEntities(post.post_title)
                }).attr('data-id', post.ID)
                    .attr('data-title', post_title)
                    .attr('data-type', post.post_type)
                    .attr('data-date', post.post_date)
                    .attr('data-meta', post.meta_value)
                    .attr('data-permalink', post.permalink);

                if (post.post_thumbnail !== undefined) {
                    a.attr('data-thumbnail-url', post.post_thumbnail);
                }

                var b = $('<span />', {
                    'class': 'post-type',
                    text: post.post_type
                });

                var c = $('<span />', {
                    'class': 'post-meta',
                    text: post.meta_value
                });

                return $('<li />').append(a).append(b).append(c);

            });

            results.hide().html(li).slideDown('slow');
            loader.removeClass('visible');

        },

        bindSearchList: function () {

            $(document).on('click', '.search-results a.select-post, .post-list a.select-post', function (e) {
                e.preventDefault();

                var wrapper = $(this).parents('.sb-post-select-wrapper'),
                    loader = $('.spinner', wrapper).addClass('visible'),
                    multiple = wrapper.data('multiple'),
                    ajax_fn = wrapper.data('function'),
                    selectedPosts = $('.selected-posts', wrapper),
                    ID = $(this).data('id'),
                    title = $(this).data('title'),
                    // type = $(this).data('type'),
                    // date = $(this).data('date'),
                    meta = wrapper.data('metakey'),
                    image = wrapper.data('image'),
                    permalink = $(this).data('permalink'),
                    remove = wrapper.data('remove'),
                    inputName = wrapper.data('name');
                    // thumbnailUrl = $(this).data('thumbnail-url'),
                    // idArray = [];

                $(this).parents('.search-results').fadeOut('fast');

                if (ajax_fn === 'set_post_id') {

                    $('.selected-post-id', wrapper).val(ID);
                    $('input.sb-post-search', wrapper).addClass('hidden');

                    var displayWrapper = $('.display-wrapper', wrapper),
                        displayPost = $('.selected-post', displayWrapper),
                        selected_tag = $('a.selected', displayPost),
                        text_input = $('.text-wrapper input', wrapper);

                    text_input.val(title);

                    selected_tag.attr('href', permalink).html(title);

                    displayWrapper.removeClass('hidden');

                    wrapper.data('keep-string', '');
                    $('.sb-post-search', wrapper).val('');

                    if (!wrapper.hasClass('has-posts')) {
                        wrapper.addClass('has-posts');
                    }

                    loader.removeClass('visible');
                    return true;
                }

                $.post(ajaxurl, {
                    'action'     : ajax_fn,
                    'post_id'    : ID,
                    'meta'       : meta,
                    'image'      : image,
                    'remove'     : remove,
                    'name'       : inputName
                }, function (data) {

                    wrapper.data('keep-string', '');
                    $('input[type=search]', wrapper).val('');

                    if (!wrapper.hasClass('has-posts')) {
                        wrapper.addClass('has-posts');
                    }

                    if (multiple) {
                        // append
                        selectedPosts.append(data);
                        loader.removeClass('visible');
                    } else {
                        // replace
                        selectedPosts.fadeOut('fast', function () {
                            selectedPosts.html(data).fadeIn('fast'); // ersätt
                            loader.removeClass('visible');
                        });
                    }

                    if (wrapper.hasClass('has-post-list')) {

                        var multi = wrapper.hasClass('multiple');
                        var maxNumber = wrapper.data('max-number');

                        var currentCount = $('.sb-selected-display', wrapper).children().length;

                        if (!multi || (multi && maxNumber === currentCount)) {
                            // reset
                            wrapper.find('.choose-post-from-list').show();
                            wrapper.removeClass('has-post-list');
                        }


                    }

                    // run trigger for multiform indexing
                    $(document).trigger('form-updated');

                }, 'json');

            });

        },

        bindSearchEvent: function () {

            $(document).on('input', 'input.sb-post-search', function () {

                var wrapper = $(this).parents('.sb-post-select-wrapper'),
                    searchResults = $('.search-results', wrapper);

                if (this.value !== '') {
                    return true;
                }

                wrapper.data('keep-string', '');
                searchResults.html('').hide();

            });

        },

        bindRemoveSelected: function () {

            $(document).on('click', '.sb-fancy-url a.remove', function (e) {
                e.preventDefault();
                var wrapper = $(this).parents('.sb-fancy-url');

                $('.selected-post-id', wrapper).val('');
                $('.text-wrapper input', wrapper).val('');
                $('.sb-post-search', wrapper).val('');
                $('a.selected', wrapper).attr('href', '').text('');
                $('.display-wrapper', wrapper).addClass('hidden');
                $('.search-wrapper input', wrapper).removeClass('hidden');

            });

        },

        bindChooseFromList: function () {

            var self = this;

            $('.choose-post-from-list').on('click', function (e) {

                e.preventDefault();

                var button = $(this),
                    wrapper = button.parents('.sb-post-select-wrapper'),
                    listWrapper = $('.list-wrapper', wrapper),
                    postList = $('.post-list', wrapper),
                    postType = wrapper.data('posttype'),
                    metaKey = wrapper.data('metakey'),
                    isList = wrapper.data('list'),
                    loader = $('.spinner', wrapper);

                if (button.attr('disabled') === 'disabled') {
                    return;
                }

                loader.addClass('visible');

                $.post(ajaxurl, {
                    'action'     : 'get_post_search_result',
                    'post_type'  : postType,
                    'meta_key'   : metaKey,
                    'search'     : '',
                    'list'       : isList
                }, function (data) {

                    if (data.length > 0) {

                        self.attachList(data, loader, wrapper, postList);
                        wrapper.addClass('has-post-list');
                        listWrapper.show();
                        button.hide();

                    } else {

                        loader.removeClass('visible');

                    }
                    return;

                }, 'json');

            });

        },

        attachList: function (data, loader, wrapper, postList) {

            var shortenTitle = wrapper.data('shorten-title');

            var elem = 'a';
            if (handleMaxNumber(wrapper)) {
                elem = 'span';
            }

            var li = $.map(data, function (post) {

                var post_title = post.post_title;
                if (shortenTitle === true) {
                    post_title = post.short_title;
                }

                var a = $('<' + elem + ' />', {
                    href: '',
                    'class': 'select-post post-title',
                    text: post.post_title
                }).attr('data-id', post.ID)
                    .attr('data-title', post_title)
                    .attr('data-type', post.post_type)
                    .attr('data-date', post.post_date)
                    .attr('data-meta', post.meta_value)
                    .attr('data-permalink', post.permalink);

                if (post.post_thumbnail !== undefined) {
                    a.attr('data-thumbnail-url', post.post_thumbnail);
                }

                var b = $('<span />', {
                    'class': 'post-type',
                    text: post.post_type
                });

                var c = $('<span />', {
                    'class': 'post-meta',
                    text: post.meta_value
                });

                return $('<li />').append(a).append(b).append(c);

            });

            postList.html(li).fadeIn('slow');
            loader.removeClass('visible');

        },

        filterPosts: function () {

            var typeTimer = null;

            $(document).on('keyup', 'input.sb-post-filter', function () {

                var scope = $(this),
                    currentValue = scope.val(),
                    wrapper = $(this).parents('.sb-post-select-wrapper'),
                    results = $('ul.post-list', wrapper);
                    // postType = wrapper.data('posttype'),
                    // metaKey = wrapper.data('metakey'),
                    // loader = $('.spinner', wrapper);

                if (currentValue === '') {

                    results.children().show();

                }

                clearTimeout(typeTimer);
                typeTimer = setTimeout(function () {

                    if (wrapper.data('keep-string') === currentValue) {
                        return false;
                    }

                    wrapper.data('keep-string', currentValue);

                    var children = results.children().removeClass('hide');

                    var r = new RegExp(currentValue, 'i');
                    var match = children.find('a, span.post-title').filter(function () { return $(this).text().match(r); });

                    children.addClass('hide');
                    match.parents('li').removeClass('hide');

                }, 300);

            });

        },

        resetList: function () {
            $('.list-wrapper').hide();
            $('.has-post-list').removeClass('has-post-list');
            $('.choose-post-from-list').show();
        },

        bindEscape: function () {

            var self = this;

            $(document).keyup(function (e) {

                if (e.keyCode === 27) {
                    self.resetList();
                }

            });

        }

    };

    postSelect.init();

    // multi form indexing
    var indexMultiForm = function () {

        $('.sb-field-wrapper').each(function (index) {

            var wrapper = $(this),
                dataName = wrapper.data('name');

            // unused i
            $('input[name^=' + dataName + ']', wrapper).each(function (i, input) {

                var name = $(input).attr('name');

                var newName = name.replace(/\[[a-zA-Z0-9_\-]+\](\[\d?\])/, function (a, b) {
                        var part = a.replace(b, '');
                        return part + '[' + index + ']';
                    });

                $(input).attr('name', newName);

            });

        });

    };

    indexMultiForm();
    $(document).on('form-updated', function () {
        indexMultiForm();
    });

    $('.sb-forms-sortable').on('sortstop', indexMultiForm);

    // Handle multi form unique
    $('.sb-multi-unique').on('change', 'input[type=checkbox]', function () {
        var status = $(this).prop('checked');
        $('.sb-multi-unique input').prop('checked', false);
        if (status === true) {
            $(this).prop('checked', true);
        }
    });

    // Text counter for text / textarea
    var textCounter = function (elem) {

        var input;

        if (elem.target) {
            input = $(this); // from keyup
        } else {
            input = elem;
        }

        if (input.length === 0) {
            return;
        }

        var wrapper = input.parents('.sb-forms'),
            counter = $('.sb-text-count', wrapper),
            maxLen = counter.data('max') || 0,
            currentLen = input.val().length,
            remaining = (maxLen - currentLen);

        if (0 < maxLen) {
            counter.text(remaining);
        } else {
            counter.text(currentLen);
        }

        if (0 < maxLen && 1 > remaining) {
            counter.addClass('too-long');
        } else {
            counter.removeClass('too-long');
        }

    };

    $('.sb-text-count').each(function () {
        var input = $(this).siblings('input, textarea');

        textCounter(input);
        input.on('keyup', textCounter);

    });

    // Bind toggle field
    $(document).on('click', '.toggle-wrapper', function () {

        var toggle = $(this),
            input = toggle.find('input');

        toggle.toggleClass('is-active');

        if (toggle.hasClass('is-active')) {
            input.val(1);
        } else {
            input.val(0);
        }

        toggle.trigger('has-toggled');

    });

    // Bind password toggle
    $(document).on('click', '.sb-password-toggle', function () {

        var button = $(this),
            input = button.prev('input'),
            type = input.attr('type'),
            show = button.data('show'),
            hide = button.data('hide');

        if ('password' === type) {
            input.attr('type', 'text');
            button.text(hide);
        } else {
            input.attr('type', 'password');
            button.text(show);
        }

    });

    // Faders

    var Faders = {

        init: function () {

            this.setCurrentValue();
            this.bindInteraction();

        },

        getObject: function (wrapper) {

            var object = {};

            object.wrapper = wrapper;
            object.min = wrapper.data('min');
            object.max = wrapper.data('max');
            object.knob = $('.fader-knob', wrapper);
            object.output = $('input', wrapper);
            object.knobWidth = object.knob.outerWidth();
            object.minPos = wrapper.offset().left;
            object.maxPos = (object.minPos + wrapper.outerWidth() - object.knobWidth);

            return object;

        },

        setCurrentValue: function () {

            var self = this;

            faders.each(function () {

                var faderWrapper = $(this).find('.wrapper'),
                    fader = self.getObject(faderWrapper),
                    currentValue = $(this).find('input').val(),
                    zeroMin = (fader.max - fader.min),
                    position = (currentValue / zeroMin) * fader.wrapper.outerWidth(),
                    calculatedPos = position - (fader.knobWidth / 2),

                    finalPos = Math.min(Math.max(parseInt(calculatedPos, 10), 0), fader.wrapper.outerWidth() - fader.knobWidth);

                fader.knob.animate({
                    'left': finalPos
                }, 150);

            });

        },

        bindInteraction: function () {

            var self = this;

            faders.on('mousedown touchstart', function (e) {

                e.preventDefault();

                var faderWrapper = $(this).find('.wrapper'),
                    fader = self.getObject(faderWrapper),
                    knob = fader.knob.addClass('fader-drag').css('z-index', 1000),
                    currentPos = knob.offset().left + fader.knobWidth - e.originalEvent.pageX;

                knob.parents('.wrapper').on('mousemove touchmove', function (e) {

                    e.preventDefault();

                    var toPos = e.originalEvent.pageX + currentPos - fader.knobWidth;
                    var moveTo = Math.min(Math.max(parseInt(toPos, 10), fader.minPos), fader.maxPos);

                    self.calcValue(fader);

                    $('.fader-drag').offset({ left: moveTo });

                    $(document).on('mouseup touchend', function (e) {

                        e.preventDefault();
                        $('.fader-drag').removeClass('fader-drag');

                        self.calcValue(fader);

                    });

                });

            });

        },

        calcValue: function (fader) {

            var pos = fader.knob.position().left / (fader.wrapper.outerWidth() - fader.knobWidth);

            var zeroMin = (fader.max - fader.min),
                result = Math.round(pos * zeroMin);

            fader.output.val(result);

        }

    };

    var faders = $('.sb-fader');
    if (0 < faders.length) {
        Faders.init();
    }


}(jQuery));