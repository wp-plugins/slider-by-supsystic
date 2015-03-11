/*global jQuery*/

Color.prototype.toString = function(remove_alpha) {
    if (remove_alpha == 'no-alpha') {
        return this.toCSS('rgba', '1').replace(/\s+/g, '');
    }
    if (this._alpha < 1) {
        return this.toCSS('rgba', this._alpha).replace(/\s+/g, '');
    }
    var hex = parseInt(this._color, 10).toString(16);
    if (this.error) return '';
    if (hex.length < 6) {
        for (var i = 6 - hex.length - 1; i >= 0; i--) {
            hex = '0' + hex;
        }
    }
    return '#' + hex;
};

(function ($, _wp, _app, _debug, undefined) {

    var dialogWidth = 900;

    var _colorPicker = (function (el, changeCallback) {
        el.each(function() {
            var $control = $(this),
            value = $control.val().replace(/\s+/g, '');
            // Manage Palettes
            // var palette_input = $control.attr('data-palette');
            // if (palette_input == 'false' || palette_input == false) {
            //     var palette = false;
            // } else if (palette_input == 'true' || palette_input == true) {
            //     var palette = true;
            // } else {
            //     // var palette = $control.attr('data-palette').split(",");
            // }
            var palette = false;
            $control.wpColorPicker({ // change some things with the color picker
                clear: function(event, ui) {
                    // TODO reset Alpha Slider to 100
                },
                change: function(event, ui) {
                    // send ajax request to wp.customizer to enable Save & Publish button
                    var _new_value = $control.val();
                    // var key = $control.attr('data-customize-setting-link');
                    // wp.customize(key, function(obj) {
                    //     obj.set(_new_value);
                    // });
                    // change the background color of our transparency container whenever a color is updated
                    var $transparency = $control.parents('.wp-picker-container:first').find('.transparency');
                    // we only want to show the color at 100% alpha
                    $transparency.css('backgroundColor', ui.color.toString('no-alpha'));

                    if ($.isFunction(changeCallback)) {
                        changeCallback.call($control, event);
                    }
                },
                palettes: palette // remove the color palettes
            });
            $('<div class="pluto-alpha-container"><div class="slider-alpha"></div><div class="transparency"></div></div>').appendTo($control.parents('.wp-picker-container'));
            var $alpha_slider = $control.parents('.wp-picker-container:first').find('.slider-alpha');
            // if in format RGBA - grab A channel value
            if (value.match(/rgba\(\d+\,\d+\,\d+\,([^\)]+)\)/)) {
                var alpha_val = parseFloat(value.match(/rgba\(\d+\,\d+\,\d+\,([^\)]+)\)/)[1]) * 100;
                var alpha_val = parseInt(alpha_val);
            } else {
                var alpha_val = 100;
            }
            $alpha_slider.slider({
                slide: function(event, ui) {
                    $(this).find('.ui-slider-handle').text(ui.value); // show value on slider handle
                    // send ajax request to wp.customizer to enable Save & Publish button
                    // var _new_value = $control.val();
                    // var key = $control.attr('data-customize-setting-link');
                    // wp.customize(key, function(obj) {
                    //     obj.set(_new_value);
                    // });
                },
                create: function(event, ui) {
                    var v = $(this).slider('value');
                    $(this).find('.ui-slider-handle').text(v);
                },
                value: alpha_val,
                range: "max",
                step: 1,
                min: 1,
                max: 100
            }); // slider
            $alpha_slider.slider().on('slidechange', function(event, ui) {
                var new_alpha_val = parseFloat(ui.value),
                iris = $control.data('a8cIris'),
                color_picker = $control.data('wpWpColorPicker');
                iris._color._alpha = new_alpha_val / 100.0;
                $control.val(iris._color.toString());
                color_picker.toggler.css({
                    backgroundColor: $control.val()
                });
                // fix relationship between alpha slider and the 'side slider not updating.
                var get_val = $control.val();
                $($control).wpColorPicker('color', get_val);
            });
        }); // each
    });

    var _log = (function (message, channel) {
        channel = channel || 'veditor';

        if (_debug) {
            console.log(channel + ': ' + message);
        }
    });

    var calculateOuterWidth = (function (baseWidth, width, includeMargins) {
        var result,
            horizontalMargin = 14.2 * 2,
            requiredWidth = parseInt(baseWidth, 10) - parseInt(width, 10);

        result = parseInt(baseWidth, 10) - requiredWidth;

        if (includeMargins === true) {
            result = result - horizontalMargin;
        }

        return Math.floor(result);
    });

    function Controller() {
        this.$dialog = $('#visual-editor-window');
        this.$loader = null;
    };

    Controller.prototype.createSpinner = (function () {
        var $spinner = $('<img/>', {
            src: _wp.ajax.settings.url.replace('admin-ajax.php', 'images/wpspin_light.gif')
        }).css({
            display:    'block',
            margin:     '10px auto'
        });

        return $spinner;
    });

    Controller.prototype.createContainer = (function () {
        var $container = $('<div/>', {
            id: 'veditor-container'
        }).css({
            float: 'left',
            width: calculateOuterWidth(dialogWidth, 600, true)
        });

        return $container;
    });

    Controller.prototype.createSidebar = (function () {
        var $sidebar = $('<div/>', {
            id: 'veditor-sidebar'
        }).css({
            float:  'left',
            width:  calculateOuterWidth(dialogWidth, 300, false),
            margin: '10px auto'
        });

        return $sidebar;
    });

    Controller.prototype.openWindow = (function () {
        this.$dialog.dialog('open');
    });

    Controller.prototype.enableTextillate = (function($selector, type, value) {
        if($.inArray(type, ['animation-effect', 'in-effect', 'out-effect']) > -1) {
            $($selector).find('span.caption').textillate({
                initialDelay: 1000,
                loop: true,
                in: {
                    delay: 100,
                    sync: true
                },
                out: {
                    shuffle: true
                }
            });
        }
        if(type == 'animation-effect' && value != 'enable') {
            $($selector).find('span.caption').textillate('in');
            $($selector).find('span.caption').textillate('stop');
            $($selector).find('span.caption').data('effect', 'disable');
        } else {
            if($($selector).find('span.caption').data('effect') == 'disable') {
                $($selector).find('span.caption').textillate('start');
                $($selector).find('span.caption').data('effect', 'enable');
            }
        }
    });

    Controller.prototype.updateVisual = (function($sidebar, $slider, event) {
        // Add loading spinner while we loading settings.
        this.createSpinner()
            .appendTo($sidebar.empty());

        $controller = this;

        // Send request to get settings page.
        $.post(_wp.ajax.settings.url, {
            action: 'supsystic-slider',
            route: {
                module: 'slider',
                action: 'getSidebar'
            },
            slider_id: this.$dialog.data('id'),
            selector:  event.currentTarget.className
        }).success(function (response) {
            var $template = $(response.template),
                onInputChange = (function (e) {
                    e.stopImmediatePropagation();

                    var $input = $( this );

                    if(!$input.data('type')) {
                        $slider.find($input.data('selector'))
                            .css(
                            $input.data('prop'),
                            $input.val()
                        );
                    }
                    else {
                        $slider.find($input.data('selector') + ' ' + $input.data('selector-helper')).each(function() {
                            $(this).attr($input.data('attr'), $input.val());
                        });
                        $controller.enableTextillate(event.currentTarget, $input.data('type'), $input.val());
                    }
                });

            // Configure colopicker and set change event handler.
            _colorPicker($('.rs-veditor-colorpicker', $template), onInputChange);

            // Set event handler for some events.
            $('.rs-veditor-input', $template).on(
                'change input keyup paste',
                onInputChange
            )
                // Apply presented settings.
                .trigger('change');

            $sidebar.empty().append($template);
        });
    });

    Controller.prototype.saveSettings = (function($sidebar, $slider, e) {
        // Save new settings when loading new sidebar.
        var data = {};

        $controller.createSpinner()
            .css({ float: 'left', marginRight: 20 })
            .attr('title', 'Saving slider settings...')
            .appendTo($('.ui-dialog-buttonset'));

        $('.rs-veditor-input', $sidebar).each(function () {
            var $el = $( this );

            if (!$.isPlainObject(data[$el.data('selector')])) {
                data[$el.data('selector')] = {};
            }

            data[$el.data('selector')][$el.data('prop')] = $el.val();
        });

        $.post(_wp.ajax.settings.url, {
            action: 'supsystic-slider',
            route:  {
                module: 'slider',
                action: 'updateVisual'
            },
            slider_id:  this.$dialog.data('id'),
            data:       data
        }).success(function (response) {
            var spinner = $('.ui-dialog-buttonset').find('img');

            $controller.updateVisual($sidebar, $slider, e);

            if (spinner != undefined) {
                spinner.remove();
            }
        });
    });

    Controller.prototype.init = (function () {
        var self = this;

        if (!this.$dialog.length) {
            return;
        }

        var $controller = this,
            $dialog = this.$dialog;

        $dialog.dialog({
            autoOpen: _debug,
            modal: true,
            width: dialogWidth,
            close: function() {
                // Ugly hack. Rewrite it.
                $('.bx-viewport, .bx-caption, .bx-prev, .bx-next', document).trigger('click');

                $dialog.empty();
            },
            buttons: {
                Close: function () {
                    $dialog.dialog('close');
                }
            },
            open: function () {
                if ($dialog.data('loaded')) {
                    if (_debug) {
                        _log('Slider already loaded.', 'dialog');
                    }

                    return;
                }

                $dialog.append($controller.createSpinner());

                $.post(_wp.ajax.settings.url, {
                    action: 'supsystic-slider',
                    route:  {
                        module: 'slider',
                        action: 'getPreview'
                    },
                    id:    $dialog.data('id'),
                    width: calculateOuterWidth(dialogWidth, 600)
                }).success(function (response) {

                    if (response.is_empty) {
                        var _paragraph = (function (text) {
                            return $('<p/>', {
                                class: 'description',
                            })
                            .text(text);
                        });

                        $dialog
                            .empty()
                            .append(_paragraph('Current slider is empty.'))
                            .append(_paragraph('Add one or more images to work with visual editor.'));

                        return false;
                    }

                    // Append slider.
                    var $slider = $(response.slider),
                        $sidebar = $controller.createSidebar(),
                        $container = $controller
                            .createContainer()
                            .append($slider);

                    // Create loading spinner on sidebar while settings loading.
                    $sidebar.append($controller.createSpinner());

                    // Add container and sidebar to the out modal window.
                    $dialog.empty()
                        .append($container)
                        .append($sidebar);

                    // Initialize Slider by Supsystic javascript plugins.
                    _app.init();
                    self.initCoinSlider();

                    $slider.append('<div class="bx-viewport-button button" style="display: inline-block; margin: 10px;">Viewport</div>' +
                                    '<div class="bx-caption-button button" style="display: inline-block; margin: 10px;">Caption</div>' +
                                    '<div class="bx-prev-button button" style="display: inline-block; margin: 10px;">Buttons</div>');

                    var $blocks = $('.bx-viewport, .bx-caption, .bx-prev, .bx-next, .bx-viewport-button, .bx-caption-button, .bx-prev-button, .thumbnails-button', $slider);

                    $blocks
                        .on('click veditorClose', function (e) {
                            e.stopImmediatePropagation();

                            // We don't need to save empty fields at first time.
                            // So check does the sidebar has loading spinner
                            // instead of form.
                            if ($sidebar.has('img').length == 0) {
                                $controller.saveSettings($sidebar, $slider, e);
                            } else {
                                $controller.updateVisual($sidebar, $slider, e);
                            }
                        });

                        // Trigger first match to load settings.
                    $blocks
                        .first()
                        .trigger('click')
                });
            }
        });
    });

    Controller.prototype.initCoinSlider = function() {
        var $sliders = $('.supsystic-slider.supsystic-slider-coin'),
            stringToBoolean = function (value) {
                if (value == 'true') {
                    return true;
                } else if (value == 'false') {
                    return false;
                } else {
                    return value;
                }
            };

        if ($sliders.length < 1) {
            return false;
        }

        $.each($sliders, function (index, slider) {
            var $slider  = $(slider),
                settings = $slider.data('settings'),
                config   = {};

            $.each(settings, function (category, opts) {
                if(opts) {
                    $.each(opts, function (key, value) {
                        config[key] = stringToBoolean(value);
                    });
                }
            });

            $slider.coinslider(config);
        });
    };

    $(document).ready(function () {
        var controller = new Controller();

        controller.init();

        $('#visual-editor').on('click', function () {
            controller.openWindow();
        });
    });

}(jQuery, wp, SupsysticSlider, document.location.hash == '#debug'));
