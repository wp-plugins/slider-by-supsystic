/*global jQuery*/
(function ($, WordPress, undefined) {


    $(document).ready(function () {

        var $error = $('div.error'),
            $entities = $('[data-entity]'),
            sliderId = $('#sliderID').val();

        function hasSmallImages() {
            return ($error != undefined);
        }

        function getSliderWidth() {
            if (!hasSmallImages()) {
                return 0;
            }

            return parseInt($error.find('.sliderWidth').text(), 10);
        }

        function generateEntities(images, $entity) {
            var id = $entity.closest('tr').find('input[name="image[]"]').val();
            images.push({
                'id': id,
                'type': 'image',
                'folder_id': '0'
            });
        }

        function removeDialog(sliderId, images) {
            var notification = noty({
                layout: 'topCenter',
                type: 'error',
                text : '<h3>Error</h3>There are images that smaller then slider width, do you want to remove them?',
                timeout: false,
                animation: {
                    open: 'animated flipInX',
                    close: 'animated flipOutX',
                    easing: 'swing',
                    speed: '800'
                },
                buttons : [
                    {
                        addClass: 'btn btn-primary', text: 'Ok', onClick: function($noty) {
                        $noty.close();
                        $.post(WordPress.ajax.settings.url, {
                            action:    'supsystic-slider',
                            route:     {
                                module: 'slider',
                                action: 'deleteResource'
                            },
                            resources: images,
                            id:        parseInt(sliderId, 10)
                        }).success(function (response) {
                            if (!response.error) {
                                //$entities.parents('tr').remove();
                                window.location.reload(true);
                            } else {
                                $.jGrowl(response.message);
                            }
                        });
                    }
                    },
                    {
                        addClass: 'btn btn-danger', text: 'Cancel', onClick: function($noty) {
                        $noty.close();
                        noty({
                            layout: 'topCenter',
                            text: '<h3>Warning</h3>Small images causes to bad slider output',
                            type: 'warning',
                            timeout: 2000
                        });
                    }
                    }
                ]
            });
        }

        function fireSmallImages() {
            var width = getSliderWidth(),
                images = [];

            $entities.each(function () {
                var $entity = $(this),
                    isPhoto = $entity.data('entity-type') == 'photo',
                    entityWidth = $entity.data('entity-info')
                        .attachment
                        .sizes
                        .full
                        .width;

                if ((entityWidth < width || !entityWidth) && isPhoto) {
                    //$entity.addClass('active update');
                    generateEntities(images, $entity);
                }
            });
            if(images.length) {
                removeDialog(sliderId, images);
            }
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