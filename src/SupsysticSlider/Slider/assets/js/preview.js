/*global jQuery*/
(function ($, WordPress, app) {

    var windowWidth = 500,
        sliderWidth = Math.floor(windowWidth - 14.3 * 2);

    function Controller() {
        this.$window = null;
        this.$trigger = null;
    }

    Controller.prototype.setWindow = (function (windowId) {
        var $window = $(windowId), onDialogOpen;

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
