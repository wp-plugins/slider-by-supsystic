/*global jQuery*/
(function ($, app, debug, undefined) {

    app.enableTextAnimation = (function($part, properties) {
        if(properties['text-animation'] == 'enable' && !$('.ui-dialog').length) {
            $part.children('span').textillate({
                initialDelay: 1000,
                loop: true,
                in: {
                    effect: properties['text-effect-in'],
                    delay: 100,
                    sync: true
                },
                out: {
                    effect: properties['text-effect-out'],
                    shuffle: true
                }
            });
        }
    });

    app.isControls = (function($element) {
        return $element.hasClass('bx-controls-direction');
    });

    app.setArrows = (function($element, properties) {
        if(this.isControls($element)) {
            $element.find('a').each(function() {
                $(this).attr('id', properties['background']);
            });
        }
    });

    app.init = (function (selector) {
        var $container, defaultSelector = '.supsystic-slider';
        var self = this;

        $container = (selector == undefined) ? $(defaultSelector) : $(selector);

        if (!$container.length) {
            if (debug) {
                console.log('Selector "' + selector + '" does not exists.');
            }

            return false;
        }

        if ($.isEmptyObject(app.plugins)) {
            if (debug) {
                console.log('There are no registered plugins.');
            }

            return false;
        }

        $.each(app.plugins, function (plugin, callback) {
            if (debug) {
                console.log('Plugin initialization: ' + plugin);
            }

            if (!$.isFunction(callback)) {
                if (debug) {
                    console.log('The callback for the ' + plugin + ' is not a function.');
                }
            }

            callback($container);

            // Apply visual editor styles.
            if ($.isPlainObject($container.data('settings'))) {
                var settings = $container.data('settings');

                if ('__veditor__' in settings && settings['__veditor__'] != null) {
                    $.each(settings['__veditor__'], function (selector, properties) {
                        var $part = $(selector, $container);

                        self.setArrows($part, properties);
                        $.each(properties, function (key, value) {
                            $part.css(key, value);
                        });
                        self.enableTextAnimation($part, properties);
                    });
                }
            }
        });

        return true;
    });

    $(document).ready(function () {
        app.init();
    }).ajaxComplete(function() {
        //app.init();
    });

}(jQuery, window.SupsysticSlider = window.SupsysticSlider || {}, document.location.hash == '#debug'));
