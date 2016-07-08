$(document).ready(function(){
    // User dismissing/clicking on a notification
    $('.block_advanced_notifications').on('click', '.dismissible', function() {

        var dismiss = $(this).attr('data-dismiss');

        $(this).slideUp('150', function(){
            $(this).remove();
        });

        var senddata = {};          //Data Object
        senddata.call = 'ajax';
        senddata.dismiss = dismiss;

        var callpath = M.cfg.wwwroot + "/blocks/advanced_notifications/pages/process.php?sesskey=" + M.cfg.sesskey;

        //Update user preferences
        $.post(callpath, senddata, function (data) {
        }).fail(function (data) {
            try {
                alert(data.responseJSON.Message);
            } catch (e) {
            }
        }).done(function (data) {
            //user dismissed notification

        });
    });

    // Managing notifications
    $('#region-main').on('click', '.notifications_table tr > td a', function(e) {

        e.preventDefault();
        var senddata = {};          //Data Object
        senddata.call = 'ajax';
        senddata.purpose = '';
        senddata.tableaction = '';

        //Chcek if user wants to edit/delete
        var eattr = $(this).attr('data-edit');
        var dattr = $(this).attr('data-delete');

        //Check if anchor element has attribute, retrieved from above
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

        // Perform tableaction
        $.post(callpath, senddata, function (data) {
        }).fail(function (data) {
            try {
                alert(data.responseJSON.Message);
            } catch (e) {
            }
        }).done(function (data) {
            //user deleted/edited notification
            if (parseInt(data.done) > 0){
                $('#tr' + data.done).closest("tr").remove();
            }
            else if(senddata.purpose == 'edit')
            {
                $.each(data.edit, function(index, value) {

                    //Quickfix for uses of 'enabled' and 'enable'
                    if (index == "enabled")
                    {
                        index = "enable";
                    }

                    // Need this for updating
                    if (index == "id") {
                        var form = $('#add_notification_form');

                        // Because we're doing a standard submit, we need extra inputs to pass params
                        form.prepend('<input type="hidden" id="add_notification_id" name="id" value="' + value + '"/>');
                        form.prepend('<input type="hidden" id="add_notification_call" name="call" value="ajax"/>');
                        form.prepend('<input type="hidden" id="add_notification_purpose" name="purpose" value="update"/>');
                    }

                    var affectelement = $('#add_notification_wrapper_id').find('#add_notification_' + index);

                    // Check whether checkboxes should be checked or not
                    // We also don't assign a value to checkbox input fields
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

    // Restore & Permanently delete notifications
    $('#region-main').on('click', '.notifications_restore_table tr > td a', function(e) {

        e.preventDefault();
        var senddata = {};          //Data Object
        senddata.call = 'ajax';
        senddata.purpose = '';
        senddata.tableaction = '';

        //Chcek if user wants to edit/delete
        var rattr = $(this).attr('data-restore');
        var pdattr = $(this).attr('data-permdelete');

        //Check if anchor element has attribute, retrieved from above
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

        // Perform tableaction
        $.post(callpath, senddata, function (data) {
        }).fail(function (data) {
            try {
                alert(data.responseJSON.Message);
            } catch (e) {
            }
        }).done(function (data) {
            // user deleted/restored notification
            // done is returned for both restore & delete
            if (parseInt(data.done) > 0){
                $('#tr' + data.done).closest("tr").remove();
            }
        });
    });

    //Clear form
    $('#add_notification_wrapper_id #add_notification_cancel').on('click', function(e) {
        e.preventDefault();
        $('#add_notification_form')[0].reset();

        // Change save button back to normal
        var savebutton = $('#add_notification_save');
        savebutton.removeClass('update');
        savebutton.val('Save');
    });

    // Managing more notifications
    $('#add_notification_form').on('submit', function(e) {
        e.preventDefault();
        var status = $('#add_notification_status');
        var savebutton = $('#add_notification_save');
        var form = $('#add_notification_form');

        status.show();

        var senddata = $(this).serialize();
        senddata.call = 'ajax';
        senddata.purpose = 'add';

        var callpath = M.cfg.wwwroot + "/blocks/advanced_notifications/pages/process.php?sesskey=" + M.cfg.sesskey;

        // Perform tableaction
        $.post(callpath, senddata, function (data) {
        }).fail(function (data) {
            try {
                alert(data.responseJSON.Message);
            } catch (e) {
            }
        }).done(function (data) {
            //user managed notification
            status.find('.saving').hide();
            status.find('.done').show();

            setTimeout(function () {
                status.fadeOut( function () {
                    status.hide();
                    status.find('.saving').show();
                    status.slideUp();
                    form[0].reset();

                    // Change save button back to normal
                    savebutton.removeClass('update');
                    form.remove('#add_notification_call');
                    form.remove('#add_notification_purpose');
                    savebutton.val('Save');
                });
            }, 2500);

            $('#advanced_notifications_table_wrapper').load('# #advanced_notifications_table_wrapper > *');
        });
    });
});