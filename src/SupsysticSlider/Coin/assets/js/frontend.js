/*global jQuery*/

/**
 * Slider by Supsystic Wordpress Plugin
 * Coin-Slider module.
 */
(function ($) {

    /**
     * Converts string true or false to the real boolean values.
     * If value isn't equals to true or false then returns raw value.
     * @param value
     * @returns {*}
     */
    var stringToBoolean = function (value) {
        if (value == 'true') {
            return true;
        } else if (value == 'false') {
            return false;
        } else {
            return value;
        }
    };

    $(document).ready(function () {
        var $sliders = $('.supsystic-slider.supsystic-slider-coin');

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

            $.each(settings, function (category, opts) {
                if(category != '__veditor__') {
                    $.each(opts, function (key, value) {;
                        if (key !== 'enabled') {
                            config[key] = stringToBoolean(value);
                        }
                    });
                }
            });

            $slider.coinslider(config);
        });

        return true;
    });
}(jQuery));