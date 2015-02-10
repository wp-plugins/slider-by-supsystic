(function ($, WordPress) {

    /**
     * Page Controller.
     *
     * @constructor
     */
    function Controller() {
        this.$submit = $('button#save');
        this.$form = $('form#settings');
        this.$pluginBtn = $('button#change');
        this.$pluginWindow = $('#changePluginWindow');
        this.$deleteBtn = $('button#delete'),
        this.$randomCheckbox = $('#generalRandomStart');

        this.init();
    }

    /**
     *
     * toggle random slide start checkbox value
     *
     * @type {Function}
     */
    Controller.prototype.randomToggle = (function() {
        this.$randomCheckbox.on('click', function() {
            if(parseInt($(this).val(), 10)) {
                $(this).attr('value', '0');
            } else {
                $(this).attr('value', '1');
            }
        });
    });

    Controller.prototype.formNavigation = (function() {
        var $buttons = $('form .add-new-h2');

        $buttons.on('click', function() {
            var $container = $(this).closest('tr');
            $container.find('.add-new-h2').removeClass('active');
            $buttons.css('background-color', 'white');
            $(this).addClass('active');
        });
    });

    Controller.prototype.checkWidth = (function() {
        var $widthType = $('[name="properties[widthType]"]'),
            $height = $('[name="properties[height]"]');

        $widthType.on('change', function() {
            if($(this).val() == '%') {

                $height.attr('disabled', 'disabled');

                var notification = noty({
                    layout: 'topRight',
                    type: 'warning',
                    text : '<h3>Warning</h3>Max width in percents is equal to 100',
                    timeout: 2000,
                    animation: {
                        open: 'animated flipInX',
                        close: 'animated flipOutX',
                        easing: 'swing',
                        speed: '800'
                    }
                });
            } else {
                $height.attr('disabled', false);
            }
        });
    });

    /**
     * Init controller.
     *
     * @type {Function}
     */
    Controller.prototype.init = (function () {
        this.intiPluginSelectWindow();

        this.$submit.on('click', $.proxy(this.submit, this));
        this.$pluginBtn.on('click', $.proxy(this.openPluginSelectWindow, this));
        this.$deleteBtn.on('click', $.proxy(this.deleteSlider, this));
        this.$pluginWindow.find('form').submit(function (e) {
            e.preventDefault();
        });

        this.randomToggle();
        this.formNavigation();
        this.checkWidth();
    });

    /**
     * Removes the slider.
     * @type {Function}
     */
    Controller.prototype.deleteSlider = (function (e) {
        var id = this.$deleteBtn.data('id'),
            redirectUri = this.$deleteBtn.data('redirect-uri'),
            confirmMsg = this.$deleteBtn.data('confirm');

        if (!confirm(confirmMsg)) {
            e.preventDefault();

            return;
        }

        $.post(WordPress.ajax.settings.url, { id: id, action: 'supsystic-slider', route: { module: 'slider', action: 'delete' } })
            .success(function (response) {
                if (!response.error) {
                    window.location.href = redirectUri;
                }

                $.jGrowl(response.message);
        });
    });

    /**
     * Submit form.
     *
     * @type {Function}
     */
    Controller.prototype.submit = (function () {
        this.$form.submit();
    });

    Controller.prototype.intiPluginSelectWindow = (function () {
        this.$pluginWindow.dialog({
            modal:    true,
            width:    400,
            autoOpen: false,
            buttons:  {
                Change:   function () {
                    $.post(
                        WordPress.ajax.settings.url,
                        $('form#changePlugin').serialize()
                    ).success(
                            $.proxy(
                                function(response) {
                                    if (!response.error) {
                                        window.location.reload(true);
                                    } else {
                                        $.jGrowl(response.message);
                                    }
                                },
                                this
                            )
                        );
                },
                Cancel: function () {
                    $(this).dialog('close');
                }
            }
        });
    });

    Controller.prototype.openPluginSelectWindow = (function () {
        this.$pluginWindow.dialog('open');
    });

    $(document).ready(function () {
        return new Controller();
    });

}(jQuery, window.wp = window.wp || {}));