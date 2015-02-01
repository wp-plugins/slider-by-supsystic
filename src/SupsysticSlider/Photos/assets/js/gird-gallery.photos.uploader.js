(function ($) {

    var defaults = {
        wp:         null,
        url:        null,
        title:      'Choose image',
        buttonText: 'Choose image',
        debug:      false,
        multiple:   true,

        route: {
            module: 'photos',
            action: 'add'
        }
    };

    var uploader;

    $.fn.ggPhotoUploader = function (parameters) {
        parameters = $.extend({}, defaults, parameters);


        if (typeof(parameters.wp) === 'undefined') {
            $.jGrowl('The WordPress Media API is not available.');
            return;
        }

        parameters.url = parameters.wp.ajax.settings.url;

        uploader = parameters.wp.media.frames.file_frame = parameters.wp.media({
            title:    parameters.title,
            button:   {
                text: parameters.buttonText
            },
            multiple: parameters.multiple
        });

        uploader.on('select', function () {
            SupsysticGallery.Loader.show('Please, wait until images will be imported.');

            var attachments = uploader.state().get('selection').toJSON(),
                statusMessage = null;

            if (attachments.length > 1) {
                statusMessage = 'There are %number% photos selected';
            } else {
                statusMessage = 'There is %number% photo selected';
            }

            $.jGrowl(statusMessage.replace('%number%', attachments.length.toString()));

            var $container = $('[data-container]'),
                imagesNumber = attachments.length;

            $.each(attachments, function (index, attachment) {
                $.post(parameters.url, {
                    action: 'supsystic-slider',
                    route: parameters.route,
                    attachment_id: attachment.id,
                    folder_id: $('[data-upload]').data('folder-id'),
                    id: $('[data-upload]').data('slider-id'),
                    view_type: $container.data('container')
                }, function (response) {
                    if (!response.error) {
                        $container.parents('#containerWrapper').show(function () {
                            $('#gg-alrt').remove();
                        });

                        $('.ready-lazy').lazyload();
                        if(!(--imagesNumber)) {
                            SupsysticGallery.Loader.hide();
                            location.reload(true);
                        }
                    }

                    $.jGrowl(response.message);
                });
            });

            /*$(document).ajaxStop(function() {
                location.reload(true);
            });*/
        });

        this.on('click', function (e) {
            e.preventDefault();
            uploader.open();
        });
    };

})(jQuery);
