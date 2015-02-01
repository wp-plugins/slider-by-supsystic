/*global jQuery*/
(function ($, WordPress, undefined) {
    $(document).ready(function () {

        var $entities = $('[data-entity]'),
            $container = $('[data-sortable]');

        function Controller() {
            $container.on('sortstop', $.proxy(this.updatePosition, this));
        }

        Controller.prototype.getParameterByName = function (name) {
            name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");

            var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
                results = regex.exec(location.search);
            return results == null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
        };

        Controller.prototype.updatePosition = (function () {
            var data = [],
                sliderId = this.getParameterByName('id');

            $entities.each(function () {
                var $entity = $(this),
                    index = $entity.data('entity-info').index;

                data[index] = $entity.index($entities.selector);
            });

            $.post(
                WordPress.ajax.settings.url,
                {
                    action:    'supsystic-slider',
                    route:     {
                        module: 'slider',
                        action: 'updatePosition'
                    },
                    positions: data,
                    slider_id: sliderId
                }
            ).success(function (response) {
                if (response.message) {
                    $.jGrowl(response.message);
                }
            });
        });

        return new Controller();
    });
}(jQuery, window.wp = window.wp || {}));