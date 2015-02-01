(function ($, WordPress) {

    var $btnDelete = $('#button-delete');

    function Controller() {

    }

    Controller.prototype = {

        // Returns slider id.
        getSliderId: function () {
            var parameter = function (name) {
                name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");

                var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
                    results = regex.exec(location.search);
                return results == null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
            };

            return parameter('id');
        },

        // Returns checked items
        getChecked:  function () {
            var $entities = $('[data-observable]:checked'), checked = [];

            if (!$entities.length) {
                return checked;
            }

            $entities.each(function () {
                var $entity = $(this).parents('[data-entity]');

                checked.push({
                    id:        $entity.data('entity-id'),
                    // Grid Gallery legacy
                    type:      $entity.data('entity-type') == 'photo' ? 'image' : $entity.data('entity-type'),
                    folder_id: $entity.data('entity-info').folder_id
                });
            });

            return checked;
        },

        // Removes checked items
        remove:      function () {
            var checked = this.getChecked(),
                id = this.getSliderId();

            if (!checked.length) {
                return;
            }

            $.post(WordPress.ajax.settings.url, {
                action:    'supsystic-slider',
                route:     {
                    module: 'slider',
                    action: 'deleteResource'
                },
                resources: checked,
                id:        parseInt(id, 10)
            }).success(function (response) {
                if (!response.error) {
                    $('[data-observable]:checked').parents('tr').remove();
                } else {
                    $.jGrowl(response.message);
                }
            });
        }
    };

    $(document).ready(function () {
        var Ctrl = new Controller();

        $('#delete-element').on('click', $.proxy(Ctrl.remove, Ctrl));
    });

}(jQuery, window.wp = window.wp || {}));