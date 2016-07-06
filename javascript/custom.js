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
    $('#region-main .notifications_table tr > td').on('click', 'a', function(e) {

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

                    var affectelement = $('#add_notification_wrapper_id').find('#add_notification_' + index);

                    affectelement.val(value);
                    // This is assuming only checkboxes will/may have a value of one, however it shouldn't affect other input types anyway
                    if (value == 1)
                    {
                        affectelement.prop('checked', true);
                    }
                    else
                    {
                        affectelement.prop('checked', false);
                    }
                });
            }
        });
    });

    //Clear form
    $('#add_notification_wrapper_id #add_notification_cancel').on('click', function(e) {
        e.preventDefault();
        $('#add_notification_form')[0].reset();
    });

    // Managing more notifications
    $('#add_notification_form').on('submit', function(e) {
        e.preventDefault();
        var status = $('#add_notification_status');
        var savebutton = $('#add_notification_save');

        status.show();

        var senddata = $(this).serialize();
        senddata.call = 'ajax';
        senddata.purpose = 'add';

        // TODO If save button has 'update' class, we need to update instead of adding
        //if (savebutton.hasClass('update'))
        //{
        //    senddata.purpose = 'update';
        //}

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
                    $('#add_notification_form')[0].reset();

                    // Change save button back to normal
                    savebutton.removeClass('update');
                    savebutton.val('Save');
                });
            }, 2500);
        });
    });
});