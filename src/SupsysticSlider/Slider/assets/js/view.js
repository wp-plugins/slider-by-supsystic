/*global jQuery*/
(function ($, WordPress, undefined) {


    $(document).ready(function () {

        var $error = $('div.error'),
            $entities = $('[data-entity]');

        function hasSmallImages() {
            return ($error != undefined);
        }

        function getSliderWidth() {
            if (!hasSmallImages()) {
                return 0;
            }

            return parseInt($error.find('.sliderWidth').text(), 10);
        }

        function fireSmallImages() {
            var width = getSliderWidth();

            $entities.each(function () {
                var $entity = $(this),
                    isPhoto = $entity.data('entity-type') == 'photo',
                    entityWidth = $entity.data('entity-info')
                        .attachment
                        .sizes
                        .full
                        .width;

                if ((entityWidth < width || !entityWidth) && isPhoto) {
                    $entity.addClass('active update');
                }
            });
        }

        /**
         * Page controller.
         *
         * @constructor
         */
        function Controller() {
            this.$elements = $('[data-entity]');

            this.init();
        }

        /**
         * Controller initialization.
         * Will be called after constructor.
         *
         * @type {Function}
         */
        Controller.prototype.init = (function () {
            if (hasSmallImages()) {
                fireSmallImages();
            }
        });

        return new Controller();
    });

}(jQuery, window.wp = window.wp || {}));