(function($, app) {

    function initControlsSlection() {
        // Init Controls type dialog
        $('#select-controls').on('click', function () {
            $('#selectControlsDialog').dialog({
                autoOpen: false,
                modal:    true,
                height:   600,
                width:    450,
                buttons:  {
                    Cancel: function () {
                        $(this).dialog('close');
                    }
                }
            });
            var selectedControls = $('input#bx-controls').val();
            if(selectedControls) {
                var controlsPreview = $('img#controlsType[data-value="'+selectedControls+'"]');
                if(controlsPreview) {
                    controlsPreview.parent().removeClass().addClass('selectedPreset');
                }
            }
            $('#selectControlsDialog').dialog('open');
        });
        // Init presets highlighting in Controls type dialog (on mouseenter)
        $('div#controlsType').each(function() {
            $(this).on('mouseenter', function() {
                if($(this).is('.unselectedPreset')) {
                    $(this).css('border-bottom', '1px solid aqua');
                }
            });
            $(this).on('mouseleave', function() {
                if($(this).is('.unselectedPreset')) {
                    $(this).css('border-bottom', '1px solid gray');
                }
            });
        });
        // Select Text in effect
        $('img#controlsType').on('click', function() {
            $('#bx-controls').attr('value', $(this).data('value'));
            $('div.selectedPreset').removeClass().addClass('unselectedPreset');
            $(this).parent().addClass('selectedPreset');
            $('#selectControlsDialog').dialog('close');
        });
    }

    app.initBuilderControls = initControlsSlection;

}(jQuery, window.SupsysticSlider = window.SupsysticSlider || {}));