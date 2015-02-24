/*global jQuery*/
(function ($, WordPress, app) {

    var windowWidth = 500,
        sliderWidth = Math.floor(windowWidth - 14.3 * 2);

    function Controller() {
        this.$window = null;
        this.$trigger = null;
    }

    Controller.prototype.setWindow = (function (windowId) {
        var $window = $(windowId), onDialogOpen,
            self = this;

        if ($window.length) {
            $window.dialog({
                autoOpen: false,
                /*buttons:  {
                    Close: function () {
                        $(this).dialog('close');
                    }
                },*/
                modal:    true,
                open:     function (e, ui) {
                    $.post(WordPress.ajax.settings.url, {
                        action: 'supsystic-slider',
                        route:  {
                            module: 'slider',
                            action: 'getPreview'
                        },
                        id:     $window.data('id'),
                        width:  sliderWidth
                    }).success(function (response) {
                        $window.html(response.slider);
                        self.initCoinSlider();
                        self.initJssorSlider();
                        app.init();
                    });
                },
                width:    windowWidth
            });

            this.$window = $window;
        }
    });

    Controller.prototype.setTrigger = (function (triggerId) {
        var $trigger = $(triggerId);

        if ($trigger.length) {
            this.$trigger = $trigger;
        }
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

    Controller.prototype.initJssorSlider = function() {
        var $sliders = $('.supsystic-slider-jssor'),
            stringToBoolean = function (value) {
                if (value == 'true') {
                    return true;
                } else if (value == 'false') {
                    return false;
                } else {
                    return value;
                }
            },
            options = {
                $PlayOrientation: 2,
                $DragOrientation: 2,
                $AutoPlay: true,
                $AutoPlayInterval: 1500,
                $BulletNavigatorOptions: {
                    $Class: $JssorBulletNavigator$,
                    $ChanceToShow: 2
                },
                $ArrowNavigatorOptions: {
                    $Class: $JssorArrowNavigator$,
                    $ChanceToShow: 2,
                    $AutoCenter: 0,
                    $Steps: 1
                },
                $ThumbnailNavigatorOptions: {
                    $Class: $JssorThumbnailNavigator$,
                    $ChanceToShow: 2,
                    $Loop: 2,
                    $SpacingX: 3,
                    $SpacingY: 3,
                    $DisplayPieces: 6,
                    $ParkingPosition: 204
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

            var jssorSlider = new $JssorSlider$("supsystic-jssor-slider", options);
        });
    };

    Controller.prototype.init = (function () {
        if (!this.$window || !this.$trigger) {
            return false;
        }

        if (document.location.hash == '#previewAction') {
            this.$window.dialog('open');
        }

        this.$trigger.on('click', $.proxy(function (e) {
            e.preventDefault();

            this.$window.dialog('open');
        }, this));

        return true;
    });

    $(document).ready(function () {
        var preview = new Controller();

        preview.setWindow('#preview-window');
        preview.setTrigger('#preview-trigger');

        preview.init();
    });

}(jQuery, window.wp = window.wp || {}, window.SupsysticSlider));