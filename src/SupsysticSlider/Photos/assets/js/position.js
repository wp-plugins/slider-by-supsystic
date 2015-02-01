//(function ($, WordPress) {
//
//    function Controller() {
//        this.scope = {
//            folder:  'folder',
//            gallery: 'gallery',
//            main:    'main'
//        };
//
//        return this;
//    }
//
//    Controller.prototype.getParameterByName = function (name) {
//        name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
//
//        var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
//            results = regex.exec(location.search);
//        return results == null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
//    };
//
//    Controller.prototype.getScope = function () {
//        var module = this.getParameterByName('module'),
//            action = this.getParameterByName('action');
//
//        if (module === 'photos' && action === 'view') {
//            return this.scope.folder;
//        }
//
//        if (module === 'slider') {
//            return this.scope.gallery;
//        }
//
//        return this.scope.main;
//    };
//
//    Controller.prototype.getScopeId = function () {
//        switch (this.getScope()) {
//            case this.scope.main:
//                return 0;
//                break;
//            case this.scope.folder:
//                return this.getParameterByName('folder_id');
//                break;
//            case this.scope.gallery:
//                return this.getParameterByName('id');
//                break;
//        }
//    };
//
//    Controller.prototype.updatePosition = function (event, ui) {
//        var $entities = $('[data-entity]'),
//            data = {
//                elements: [],
//                scope_id: this.getScopeId(),
//                scope:    this.getScope()
//            },
//            reqParams = {
//                action:   'supsystic-slider',
//                route:    {
//                    module: 'photos',
//                    action: 'updatePosition'
//                }
//            };
//
//        $.each($entities, function (index, entity) {
//            data.elements.push({
//                photo_id: parseInt($(entity).data('entity-info').id, 10),
//                position: parseInt(index, 10)
//            });
//        });
//
//        reqParams.data = data;
//
//        $.post(WordPress.ajax.settings.url, reqParams).success(function (response) {
//            $.jGrowl(response.message);
//        });
//    };
//
//    $(document).ready(function () {
//        var Ctrl = new Controller();
//
//        $('[data-sortable]').on('sortstop', $.proxy(Ctrl.updatePosition, Ctrl));
//    });
//}(jQuery, window.wp = window.wp || {}));