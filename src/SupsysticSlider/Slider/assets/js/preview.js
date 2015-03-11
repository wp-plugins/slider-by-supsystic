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
                        if($('.jssor-slider').length) {
                            self.initJssorSlider();
                        }
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
        var $slider = $('.jssor-slider'),
            stringToBoolean = function (value) {
                if (value == 'true') {
                    return true;
                } else if (value == 'false') {
                    return false;
                } else {
                    return value;
                }
            },
            _CaptionTransitions = [],
            options = {
                $PlayOrientation: 2,
                $DragOrientation: 0,
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
                },
                $CaptionSliderOptions: {
                    $Class: $JssorCaptionSlider$,
                    $CaptionTransitions: _CaptionTransitions,
                    $PlayInMode: 1,
                    $PlayOutMode: 3
                }
            };

        var initResponsive = function(jssorSlider) {
            var $slider = $('#supsystic-jssor-slider');

            if($slider.hasClass('responsive')) {
                function ScaleSlider() {
                    var bodyWidth = parseInt($slider.parent().css('width'));
                    if (bodyWidth)
                        jssorSlider.$ScaleWidth(Math.min(bodyWidth, 1920));
                    else
                        window.setTimeout(ScaleSlider, 30);
                }
                ScaleSlider();
                $(window).bind("load", ScaleSlider);
                $(window).bind("resize", ScaleSlider);
                $(window).bind("orientationchange", ScaleSlider);
            }
        };

        var checkMode = function(config, options) {
            if(config.mode == 'vertical') {
                options.$DragOrientation = 0;
                options.$PlayOrientation = 2;
            } else {
                options.$DragOrientation = 0;
                options.$PlayOrientation = 1;
            }
        };

        var initSlideshow = function(config, options) {

            if(config.slideshow) {
                options.$AutoPlay = true;
            } else {
                options.$AutoPlay = false;
            }

            if(config.slideshowSpeed) {
                options.$AutoPlayInterval = parseInt(config.slideshowSpeed);
            }
        };

        if ($slider.length < 1) {
            return false;
        }

        var settings = $slider.find('.supsystic-slider-jssor').data('settings'),
            config   = {};

        $.each(settings, function (category, opts) {
            if(opts) {
                $.each(opts, function (key, value) {
                    config[key] = stringToBoolean(value);
                });
            }

            initSlideshow(config, options);
            checkMode(config, options);

            _CaptionTransitions["L"] = { $Duration: 800, x: 0.6, $Easing: { $Left: $JssorEasing$.$EaseInOutSine }, $Opacity: 2 };
            _CaptionTransitions["R"] = { $Duration: 800, x: -0.6, $Easing: { $Left: $JssorEasing$.$EaseInOutSine }, $Opacity: 2 };
            _CaptionTransitions["T"] = { $Duration: 800, y: 0.6, $Easing: { $Top: $JssorEasing$.$EaseInOutSine }, $Opacity: 2 };
            _CaptionTransitions["B"] = { $Duration: 800, y: -0.6, $Clip: 10, $Easing: { $Top: $JssorEasing$.$EaseInOutSine }, $Opacity: 2 };
            _CaptionTransitions["TL"] = { $Duration: 800, x: 0.6, y: 0.6, $Easing: { $Left: $JssorEasing$.$EaseInOutSine, $Top: $JssorEasing$.$EaseInOutSine }, $Opacity: 2 };
            _CaptionTransitions["TR"] = { $Duration: 800, x: -0.6, y: 0.6, $Easing: { $Left: $JssorEasing$.$EaseInOutSine, $Top: $JssorEasing$.$EaseInOutSine }, $Opacity: 2 };
            _CaptionTransitions["BL"] = { $Duration: 800, x: 0.6, y: -0.6, $Easing: { $Left: $JssorEasing$.$EaseInOutSine, $Top: $JssorEasing$.$EaseInOutSine }, $Opacity: 2 };
            _CaptionTransitions["BR"] = { $Duration: 800, x: -0.6, y: -0.6, $Easing: { $Left: $JssorEasing$.$EaseInOutSine, $Top: $JssorEasing$.$EaseInOutSine }, $Opacity: 2 };

            var jssorSlider = new $JssorSlider$($slider.attr('id'), options);

            initResponsive(jssorSlider);

            $JssorPlayer$.$FetchPlayers(document.body);
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