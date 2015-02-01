/*global jQuery*/
(function ($, WordPress) {

    /**
     * Page Controller.
     *
     * @constructor
     */
    function Controller() {

        // Toolbar buttons.
        this.toolbar = {
            $import:   $('#import'),
            $checkAll: $('#select-all')
        };

        // Photos and folders checkboxes.
        this.$checkboxes = $('[data-observable]');

        // Init controller.
        this.init();
    }

    /**
     * Checks all photos and folders.
     *
     * @type {Function}
     */
    Controller.prototype.checkAll = (function () {
        this.$checkboxes.attr('checked', 'checked');
    });

    /**
     * Unchecks all photos and folders.
     *
     * @type {Function}
     */
    Controller.prototype.uncheckAll = (function () {
        this.$checkboxes.removeAttr('checked');
    });

    /**
     * Returns a hash with the selected images and folders.
     *
     * @type {Function}
     */
    Controller.prototype.getSelectedItems = (function () {
        var $checked = this.$checkboxes.filter(':checked'),
            result   = {
                image: [],
                folder: [],
                video: []
            };

        if ($checked.length < 1) {
            return result;
        }

        $.each($checked.serializeArray(), function (index, object) {
            object.name = object.name.replace('[]', '');

            result[object.name].push(parseInt(object.value, 10));
        });

        return result;
    });

    Controller.prototype.getSliderId = (function () {
        var regexS = '[\\?&]id=([^&#]*)',
            regex = new RegExp( regexS ),
            results = regex.exec( window.location.href );
        if (results == null) {
            return "";
        } else {
            return decodeURIComponent(results[1].replace(/\+/g, ' '));
        }
    });

    /**
     * Toggle checkboxes.
     *
     * @type {Function}
     */
    Controller.prototype.toggleChecks = (function () {
        if (this.$checkboxes.filter(':checked').length == 0) {
            this.checkAll();
        } else {
            this.uncheckAll();
        }
    });

    /**
     * Sends the request to the controller to add selected items to the slider.
     *
     * @type {Function}
     */
    Controller.prototype.importSelected = (function () {
        $.post(WordPress.ajax.settings.url, {
            action: 'supsystic-slider',
            route:  {
                module: 'slider',
                action: 'import'
            },
            items:  this.getSelectedItems(),
            id:     this.getSliderId()
        }).success(function (response) {
            if (response.error) {
                $.jGrowl(response.message);

                return;
            }

            window.location.href = response.redirect_uri;
        });
    });

    /**
     * Controller initialization.
     * @type {Function}
     */
    Controller.prototype.init = (function () {
        this.toolbar.$checkAll.on('click', $.proxy(this.toggleChecks, this));
        this.toolbar.$import.on('click', $.proxy(this.importSelected, this));
    });

    $(document).ready(function () {
        return new Controller();
    });

}(jQuery, window.wp = window.wp || {}));