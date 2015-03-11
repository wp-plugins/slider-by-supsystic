(function ($) {

    function Controller() {
        this.$previewWindow = $('#previewWindow');
        this.$triggers = $('.changeEffect');

        this.init();
    }

    Controller.prototype.init = (function () {
        this.initPreviewWindow();
        this.initSlider();
        this.initTriggers();
    });

    Controller.prototype.initPreviewWindow = (function () {
        this.$previewWindow.dialog({
            modal:    true,
            width:    428,
            autoOpen: false,
            buttons:  {
                Select: function () {
                    $('#effectName').text(function () {
                        var text = $('.changeEffect')
                            .filter(':checked')
                            .val(),
                            f = text.charAt(0).toUpperCase();

                        return f + text.substr(1, text.length - 1);
                    });

                    $('[name="effects[effect]"]').val($('.changeEffect').filter(':checked').val());

                    $(this).dialog('close');
                },
                Cancel: function () {
                    $(this).dialog('close');
                }
            }
        });

        $('#showEffectsPreview').on('click', $.proxy(function (e) {
            e.preventDefault();
            this.$previewWindow.dialog('open');
        }, this));
    });

    Controller.prototype.initTriggers = (function () {
        this.$triggers
            .on('click', $.proxy(function (e) {
                    this.$previewWindow
                        .find('.effectPreview')
                        .hide();

                    this.$previewWindow
                        .find('[data-effect="' + e.currentTarget.value + '"]')
                        .show();
                }, this)
            )
            .filter(':checked')
            .trigger('click');
    });

    Controller.prototype.initSlider = (function () {
        this.$previewWindow
            .find('.effectPreview')
            .each(function () {
                var $container = $(this);

                $container.coinslider({
                    width: 400,
                    height: 150,
                    effect: $container.data('effect'),
                    navigation: false,
                    links: false
                });
            });
    });

    $(document).ready(function () {
        return new Controller();
    });

}(jQuery));