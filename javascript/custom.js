$(document).ready(function() {
    // BLOCK INSTANCE ID LOGIC MANAGEMENT.
    $('#advanced_notifications_manage').on('click', 'a', function () {
        if ($(this).hasClass('instance'))
        {
            var binstance = getUrlParameter('bui_editid');
            if (binstance == undefined)
            {
                binstance = getUrlParameter('blockid');
            }

            if (binstance != undefined) {
                var link = $(this).attr('href');

                // Determine if '?' or '&' is needed for parameter.
                var divider = '?';
                if (link.indexOf("?") > -1) {
                    divider = '&';
                }

                $(this).attr('href', link + divider + 'blockid=' + binstance);
            }
        }
    });

    // USER DISMISSING/CLICKING ON A NOTIFICATION.
    $('.block_advanced_notifications').on('click', '.dismissible', function() {

        var dismiss = $(this).attr('data-dismiss');

        $(this).slideUp('150', function(){
            $(this).remove();
        });

        var senddata = {};          // Data Object.
        senddata.call = 'ajax';
        senddata.dismiss = dismiss;

        var callpath = M.cfg.wwwroot + "/blocks/advanced_notifications/pages/process.php?sesskey=" + M.cfg.sesskey;

        // Update user preferences.
        $.post(callpath, senddata, function (data) {
        }).fail(function (data) {
            try {
                alert(data.responseJSON.Message);
            } catch (e) {
            }
        }).done(function (data) {
            // User dismissed notification.

        });
    });

    // MANAGING NOTIFICATIONS.
    $('#region-main').on('click', '.notifications_table tr > td a', function(e) {

        e.preventDefault();
        var senddata = {};          // Data Object.
        senddata.call = 'ajax';
        senddata.purpose = '';
        senddata.tableaction = '';

        // Check if user wants to edit/delete.
        var eattr = $(this).attr('data-edit');
        var dattr = $(this).attr('data-delete');

        // Check if anchor element has attribute, retrieved from above.
        if (typeof eattr !== typeof undefined && eattr !== false) {
            senddata.purpose = 'edit';
            senddata.tableaction = $(this).attr('data-edit');

            var savebutton = $('#add_notification_save');
            savebutton.addClass('update');
            savebutton.val('Update');
        }
        else if (typeof dattr !== typeof undefined && dattr !== false)
        {
            senddata.purpose = 'delete';
            senddata.tableaction = $(this).attr('data-delete');
        }

        var callpath = M.cfg.wwwroot + "/blocks/advanced_notifications/pages/process.php?sesskey=" + M.cfg.sesskey;

        // Perform tableaction.
        $.post(callpath, senddata, function (data) {
        }).fail(function (data) {
            try {
                alert(data.responseJSON.Message);
            } catch (e) {
            }
        }).done(function (data) {
            // User deleted/edited notification.
            if (parseInt(data.done) > 0){
                $('#tr' + data.done).closest("tr").remove();
            }
            else if(senddata.purpose == 'edit')
            {
                $.each(data.edit, function(index, value) {

                    // Quickfix for uses of 'enabled' and 'enable'.
                    if (index == "enabled")
                    {
                        index = "enable";
                    }

                    // Need this for updating.
                    if (index == "id") {
                        var form = $('#add_notification_form');

                        // Because we're doing a standard submit, we need extra inputs to pass params.
                        // But first, remove old hidden inputs.
                        $('#add_notification_id').remove();
                        $('#add_notification_call').remove();
                        $('#add_notification_purpose').remove();
                        form.prepend('<input type="hidden" id="add_notification_id" name="id" value="' + value + '"/>');
                        form.prepend('<input type="hidden" id="add_notification_call" name="call" value="ajax"/>');
                        form.prepend('<input type="hidden" id="add_notification_purpose" name="purpose" value="update"/>');
                    }

                    var affectelement = $('#add_notification_wrapper_id').find('#add_notification_' + index);

                    // Check whether checkboxes should be checked or not.
                    // We also don't assign a value to checkbox input fields.
                    if ((index == 'enable' || index == 'dismissible' || index == 'icon') && value == 1)
                    {
                        affectelement.prop('checked', true);
                    }
                    else if ((index == 'enable' || index == 'dismissible' || index == 'icon') && value == 0)
                    {
                        affectelement.prop('checked', false);
                    }
                    else
                    {
                        affectelement.val(value);
                    }
                });
            }
        });
    });

    // Restore & Permanently delete notifications.
    $('#region-main').on('click', '.notifications_restore_table tr > td a', function(e) {

        e.preventDefault();
        var senddata = {};          // Data Object.
        senddata.call = 'ajax';
        senddata.purpose = '';
        senddata.tableaction = '';

        // Check if user wants to restore/delete.
        var rattr = $(this).attr('data-restore');
        var pdattr = $(this).attr('data-permdelete');

        // Check if anchor element has attribute, retrieved from above.
        if (typeof rattr !== typeof undefined && rattr !== false) {
            senddata.purpose = 'restore';
            senddata.tableaction = $(this).attr('data-restore');
        }
        else if (typeof pdattr !== typeof undefined && pdattr !== false)
        {
            senddata.purpose = 'permdelete';
            senddata.tableaction = $(this).attr('data-permdelete');
        }

        var callpath = M.cfg.wwwroot + "/blocks/advanced_notifications/pages/process.php?sesskey=" + M.cfg.sesskey;

        // Perform tableaction.
        $.post(callpath, senddata, function (data) {
        }).fail(function (data) {
            try {
                alert(data.responseJSON.Message);
            } catch (e) {
            }
        }).done(function (data) {
            // User deleted/restored notification.
            // Object 'done' is returned for both restore & delete.
            if (parseInt(data.done) > 0){
                $('#tr' + data.done).closest("tr").remove();
            }
        });
    });

    // Clear form.
    $('#add_notification_wrapper_id').on('click', '#add_notification_cancel', function(e) {
        e.preventDefault();
        $('#add_notification_form')[0].reset();

        // Change save button back to normal.
        var savebutton = $('#add_notification_save');
        savebutton.removeClass('update');
        $('#add_notification_id').remove();
        $('#add_notification_call').remove();
        $('#add_notification_purpose').remove();
        savebutton.val('Save');
    });

    // Managing more notifications.
    $('#region-main').on('submit', '#add_notification_form', function(e) {
        e.preventDefault();
        var status = $('#add_notification_status');
        var savebutton = $('#add_notification_save');
        var form = $('#add_notification_form');

        status.show();

        var senddata = $(this).serialize();  // Data Object.
        senddata.call = 'ajax';
        senddata.purpose = 'add';

        var callpath = M.cfg.wwwroot + "/blocks/advanced_notifications/pages/process.php?sesskey=" + M.cfg.sesskey;

        // Perform tableaction.
        $.post(callpath, senddata, function (data) {
        }).fail(function (data) {
            try {
                alert(data.responseJSON.Message);
            } catch (e) {
            }
        }).done(function (data) {
            // User saved notification.
            status.find('.saving').hide();
            status.find('.done').show();

            setTimeout(function () {
                status.fadeOut( function () {
                    status.hide();
                    status.find('.saving').show();
                    status.slideUp();
                    form[0].reset();

                    // Change save button back to normal.
                    savebutton.removeClass('update');
                    $('#add_notification_id').remove();
                    $('#add_notification_call').remove();
                    $('#add_notification_purpose').remove();
                    savebutton.val('Save');
                });
            }, 2500);

            $('#advanced_notifications_table_wrapper').load('# #advanced_notifications_table_wrapper > *');
        });
    });

    // LIVE PREVIEW.
    // Prepend live preview alert.
    $('#add_notification_wrapper_id').prepend('<div><strong>Preview:</strong><br></div><div class="alert preview-alert"><div class="preview-icon" style="display: none;"><img src="" /></div><strong class="preview-title">Title</strong> <div class="preview-message">Message</div> <div class="preview-alert-dismissible" style="display: none;"><strong>&times;</strong></div></div>');

    // Dynamically update preview alert as user changes textbox content.
    $('#add_notification_wrapper_id').on('input propertychange paste', '#add_notification_title, #add_notification_message', function () {
        $('#add_notification_wrapper_id').find('.preview-' + $(this).attr('name')).text($(this).val());
    });

    // Dynamically update preview alert type.
    $('#add_notification_type').on('change', function() {
        var alerttype = $(this).val();
        var previewalert = $('#add_notification_wrapper_id .preview-alert');

        if (alerttype != 'info' && alerttype != 'success' && alerttype != 'warning' && alerttype != 'danger')
        {
            alerttype = 'info';
        }

        previewalert.removeClass('alert-info');
        previewalert.removeClass('alert-success');
        previewalert.removeClass('alert-danger');
        previewalert.removeClass('alert-warning');
        previewalert.addClass('alert-' + alerttype);

        $('.preview-icon').find('> img').attr('src', M.cfg.wwwroot + '/blocks/advanced_notifications/pix/' + alerttype + '.png')
    });

    $('#add_notification_dismissible').on('change', function() {
        if (!this.checked) {
            $('.preview-alert-dismissible').hide();
        }
        else
        {
            $('.preview-alert-dismissible').show();
        }
    });

    $('#add_notification_icon').on('change', function() {
        if (!this.checked) {
            $('.preview-icon').hide();
        }
        else
        {
            $('.preview-icon').show();
        }
    });
});

// FUNCTIONS.
var getUrlParameter = function getUrlParameter(sParam) {
    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : sParameterName[1];
        }
    }
};