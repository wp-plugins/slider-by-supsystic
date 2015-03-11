/*global jQuery*/
(function ($, WordPress) {

    /**
     * Page controller.
     * @constructor
     */
    function Controller() {
        this.$newSlider = $('#newSliderDialog');
        this.$form = $('#newSliderDialogForm');

        this.init();
    }

    /**
     * Opens "New Slider" dialog.
     *
     * @type {Function}
     */
    Controller.prototype.open = (function () {
        this.$newSlider.dialog('open');
    });

    /**
     * Submits a form.
     *
     * @type {Function}
     */
    Controller.prototype.submitForm = (function () {
        this.$form.submit();
    });

    /**
     * Deletes the slider.
     * Sends request to the SupsysticSlider_Slider_Controller::deleteAction().
     * If slider deleted successfully, then add animation and remove element from the page.
     * Otherwise show error message with $.jGrowl.
     *
     * @type {Function}
     */
    Controller.prototype.delete = (function (e) {
        e.preventDefault();

        if (!confirm('Are you sure?')) {
            return;
        }

        /**
         * Finds the parent of the event's target
         * retrieves element id and returns slider id.
         *
         * @returns {Number}
         */
        var getSliderId = function (target) {
            var elId = $(target)
                .parents('.gg-slider')
                .attr('id');

            return parseInt(elId.replace('slider-', ''), 10);
        };

        $.post(WordPress.ajax.settings.url, {
            action: 'supsystic-slider',
            id:     getSliderId(e.currentTarget),
            route:  {
                module: 'slider',
                action: 'delete'
            }
        }).success(function (response) {
            if (response.error) {
                $.jGrowl(response.message);

                return;
            }


            $(e.currentTarget)
                .parents('.gg-slider')
                .addClass('hinge animated')
                .fadeOut(2000, function () {
                    $(this).remove();
                });
        });
    });

    Controller.prototype.togglePresets = function() {
        var $presets = this.$newSlider.find('.preset'),
            $plugin = this.$newSlider.find('#slider-plugin'),
            $preset = this.$newSlider.find('#slider-preset');

        $presets.on('click', function() {
            $presets.removeClass('selected');
            $(this).addClass('selected');
            $plugin.attr('value', $(this).data('value'));
            $preset.attr('value', $(this).data('preset'));
        });
    };

    /**
     * Controller initialization.
     *
     * Here we are init "New Slider" dialog and handles form submission.
     *
     * @type {Function}
     */
    Controller.prototype.init = (function () {
        this.$newSlider.dialog({
            width:    this.$newSlider.data('width'),
            autoOpen: this.$newSlider.data('auto-open'),
            modal: true,
            buttons: {
                /*OK: $.proxy(function () {

                }, this),
                Cancel: $.proxy(function () {
                    this.$newSlider.dialog('close');
                }, this)*/
            }
        });

        this.$form.submit(function (e) {
            e.preventDefault();

            /**
             * Converts $.serializeArray() result to key-value pairs object.
             *
             * @param {Array} input $.serializeArray() result
             * @return {Object} Key-value pairs.
             */
            var toObject = (function (input) {
                var result = {};

                $.each(input, function (index, object) {
                    result[object.name] = object.value;
                });

                return result;
            });

            var data = toObject($(e.currentTarget).serializeArray());

            $.post(WordPress.ajax.settings.url, data)
                .success(function (response) {
                    if (response.success) {
                        window.location.href = response.url;
                    }

                    $.jGrowl(response.message);
                });

        });

        if (document.location.hash == '#addSliderWindow') {
            this.$newSlider.dialog('open');
            $(".ui-dialog-titlebar").hide();
        }
    });

    $(document).ready(function () {
        var controller = new Controller();

        //$('#add-slider, #btn-add-new').on('click', $.proxy(controller.open, controller));
        $('.delete-gallery').on('click', $.proxy(controller.delete, controller));
        $('#add-slider-button').on('click', function() {
            var sliderName = $.trim($('#sliderNameInput').val());

            if(!sliderName) {
                var notification = noty({
                    layout: 'topCenter',
                    type: 'error',
                    text : '<h3>Error</h3>Slider name can not be empty',
                    timeout: 2000,
                    animation: {
                        open: 'animated flipInX',
                        close: 'animated flipOutX',
                        easing: 'swing',
                        speed: '800'
                    }
                });
            } else {
                controller.submitForm();
                controller.$newSlider.dialog('close');
            }
        });

        controller.togglePresets();

        $('#cancel-slider-button').on('click', function () {
            controller.$newSlider.dialog('close');
        });

    });

}(jQuery, window.wp = window.wp || {}));