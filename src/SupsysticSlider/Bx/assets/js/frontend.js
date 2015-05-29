/*global jQuery*/
(function ($, app) {

    /**
     * Converts string to boolean values if it needed.
     * @type {Function}
     * @param {*} value
     * @return {*}
     */
    var stringToBoolean = (function (value) {
        if (value == 'true') {
            return true;
        } else if (value == 'false') {
            return false;
        } else {
            return value;
        }
    });

    var defaults = {
        adaptiveHeight: false,
        responsive:     true
    };

    var initThumbsTransition = function($slider, $thumbs) {
        $thumbs.find('li').on('click', function() {
            $slider.goToSlide(parseInt($(this).index()));
            //$thumbs.goToSlide(parseInt($(this).index()) - 4);
        });
    };

    var initThumbs = function($slider, $current, config) {
        var $thumbs = $('.' + $slider.data('thumbs')).bxSlider({
                slideWidth: 100,
                minSlides: 4,
                maxSlides: 10,
                slideMargin: 1,
                infiniteLoop: false,
                width: parseInt(config.width)
            }),
            $thumbsContainer = $('.thumbs'),
            maxWidth = parseInt(config.width);

        initThumbsTransition($current, $thumbs);

        if(parseInt($thumbsContainer.find('li').length * 100) < config.width) {
            maxWidth = $thumbsContainer.find('li').length * 100;
        }

        $thumbsContainer.closest('.bx-wrapper').css('max-width', maxWidth);
        $thumbsContainer.closest('.bx-wrapper').css('margin-top', '5px');
    };

    var initWrapper = function() {
        $('div#bx-clearfix').each(function() {
            var slider = $(this).prev();
            var wrapper = $(this).next('.bx-wrapper');
            if(wrapper.length) {
                wrapper.css('float', slider.css('float'));
            }
        });
    };

    var init = (function ($container) {
        var $bx;

        if (!$container.length) {
            return;
        }

        $bx = $container.filter('.supsystic-slider-bx');

        if (!$bx.length) {
            return;
        }

        $.each($bx, function (index, slider) {
            var $slider = $(slider),
                settings = $slider.data('settings'),
                config = {},
                $current;

            if(settings.properties.width > 100 && settings.properties.widthType == '%') {
                settings.properties.width = 100;
            }

            if(settings.properties.widthType == '%') {
                settings.properties.width = parseInt($container.parent().css('width'))*parseInt(settings.properties.width)/100.0;
                settings.properties.height = parseInt(settings.properties.height)*100/parseInt(settings.properties.width);
            }

            $.each(settings, function (category, opts) {
                if(category != '__veditor__') {
                    $.each(opts, function (key, value) {;
                        if (key !== 'enabled') {
                            config[key] = stringToBoolean(value);
                        }
                    });
                }
            });

            config = $.extend(defaults, config, {
                slideWidth: config.width,
                'sliderId': $slider.attr('id').split('-')[2],
            });

            $slider.find('ul').css({ visibility: 'hidden'}).each(function (index, container) {
                $current = $(container).bxSlider(
                    $.extend(config,
                        {
                            onSliderLoad: function () {
                                $(container).css({ visibility: 'visible' });
                            }
                        }
                    )
                );
            });

            if(parseInt(config.navigation)) {
                initThumbs($slider, $current, config);
            } else {
                $('.' + $slider.data('thumbs')).remove();
            }
        });

        initWrapper();
    });

    app.plugins = app.plugins || {};
    app.plugins.bx = init;

}(jQuery, window.SupsysticSlider = window.SupsysticSlider || {}));
