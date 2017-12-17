/* eslint no-console: ["error", { allow: ["error"] }] */
require(['jquery'], function($) {
    // JQuery is available via $.
    $(document).ready(function() {
        // Commonly (multiple times) used elements.
        var mainregion = $('#region-main');
        var addregion = $('#add_notification_wrapper_id');
        var strings = {save: 'Save',
            update: 'Update',
            req: 'Required field...',
            preview: 'Preview',
            title: 'Title',
            message: 'Message'};

        // USER DISMISSING/CLICKING ON A NOTIFICATION.
        $('.block_advnotifications').on('click', '.dismissible', function() {

            var dismiss = $(this).attr('data-dismiss');

            $(this).slideUp('150', function() {
                $(this).remove();
            });

            var senddata = {}; // Data Object.
            senddata.call = 'ajax';
            senddata.dismiss = dismiss;

            var callpath = M.cfg.wwwroot + "/blocks/advnotifications/pages/process.php?sesskey=" + M.cfg.sesskey;

            // Update user preferences.
            $.post(callpath, senddata).fail(function() {
                console.error("No 'dismiss' response received.");
            }).done(function() {
                // User dismissed notification. Do something maybe...
            });
        });

        // MANAGING NOTIFICATIONS.
        mainregion.on('click', '.notifications_table tr > td > form > input[type=submit]', function(e) {
            e.preventDefault();
            var senddata = {}; // Data Object.
            senddata.call = 'ajax';
            senddata.purpose = '';
            senddata.tableaction = '';

            // Check if user wants to edit/delete.
            var eattr = $(this).closest('form').attr('data-edit');
            var dattr = $(this).closest('form').attr('data-delete');
            refreshRequired();

            // Check if anchor element has attribute, retrieved from above.
            if (typeof eattr !== typeof undefined && eattr !== false) {
                senddata.purpose = 'edit';
                senddata.tableaction = eattr;

                var savebutton = $('#add_notification_save');
                savebutton.addClass('update');
                savebutton.val(strings.update);
            } else if (typeof dattr !== typeof undefined && dattr !== false) {
                senddata.purpose = 'delete';
                senddata.tableaction = dattr;
            }

            var callpath = M.cfg.wwwroot + "/blocks/advnotifications/pages/process.php?sesskey=" + M.cfg.sesskey;

            // Perform tableaction.
            $.post(callpath, senddata).fail(function() {
                console.error("No 'manage' response received.");
            }).done(function(data) {
                data = JSON.parse(data);

                // User deleted/edited notification.
                if (parseInt(data.done, 10) > 0) {
                    $('#tr' + data.done).closest("tr").fadeOut(250, function() {
                        $(this).remove();
                    });
                } else if (senddata.purpose === "edit") {
                    for (var i in data) {
                        if (data.hasOwnProperty(i)) {

                            // Need this for updating.
                            if (i === "id") {
                                var form = $('#add_notification_form');

                                // Because we're doing a standard submit, we need extra inputs to pass params.
                                // But first, remove old hidden inputs.
                                $('#add_notification_id').remove();
                                form.prepend('<input type="hidden" id="add_notification_id" name="id" value="' + data[i] + '"/>');

                                $('#add_notification_purpose').val('update');
                            }

                            var affectelement = $('#add_notification_wrapper_id').find('#add_notification_' + i);

                            // Check whether checkboxes should be checked or not.
                            // We also don't assign a value to checkbox input fields.
                            if ((i === 'enabled' || i === 'global' || i === 'dismissible' || i === 'aicon') && data[i] == 1) {
                                affectelement.prop('checked', true);
                            } else if ((i === 'enabled' || i === 'global' || i === 'dismissible' || i === 'aicon') && data[i] == 0) {
                                affectelement.prop('checked', false);
                            } else {
                                affectelement.val(data[i]);
                            }
                        }
                    }
                }
            });
        });

        // Restore & Permanently delete notifications.
        mainregion.on('click', '.notifications_restore_table tr > td > form > input[type=submit]', function(e) {

            e.preventDefault();
            var senddata = {}; // Data Object.
            senddata.call = 'ajax';
            senddata.purpose = '';
            senddata.tableaction = '';

            // Check if user wants to restore/delete.
            var rattr = $(this).closest('form').attr('data-restore');
            var pdattr = $(this).closest('form').attr('data-permdelete');

            // Check if anchor element has attribute, retrieved from above.
            if (typeof rattr !== typeof undefined && rattr !== false) {
                senddata.purpose = 'restore';
                senddata.tableaction = rattr;
            } else if (typeof pdattr !== typeof undefined && pdattr !== false) {
                senddata.purpose = 'permdelete';
                senddata.tableaction = pdattr;
            }

            var callpath = M.cfg.wwwroot + "/blocks/advnotifications/pages/process.php?sesskey=" + M.cfg.sesskey;

            // Perform tableaction.
            $.post(callpath, senddata).fail(function() {
                console.error("No 'restore/permdelete' response received.");
            }).done(function(data) {
                data = JSON.parse(data);

                // User deleted/restored notification.
                // Object 'done' is returned for both restore & delete.
                if (parseInt(data.done, 10) > 0) {
                    $('#tr' + data.done).closest("tr").fadeOut(250, function() {
                        $(this).remove();
                    });
                }
            });
        });

        // Clear form.
        addregion.on('click', '#add_notification_cancel', function(e) {
            e.preventDefault();
            $('#add_notification_form')[0].reset();
            refreshRequired();

            // Change save button back to normal.
            var savebutton = $('#add_notification_save');
            savebutton.removeClass('update');
            $('#add_notification_id').remove();
            $('#add_notification_purpose').val('add');

            savebutton.val(strings.save);
        });

        // Managing more notifications.
        mainregion.on('submit', '#add_notification_form', function(e) {
            e.preventDefault();
            var status = $('#add_notification_status');
            var savebutton = $('#add_notification_save');
            var form = $('#add_notification_form');

            status.show();
            refreshRequired();
            if (!checkRequired()) {
                status.hide();
                return;
            }

            var senddata = $(this).serialize(); // Data Object.

            var callpath = M.cfg.wwwroot + "/blocks/advnotifications/pages/process.php";

            // Perform tableaction.
            $.post(callpath, senddata).fail(function(data) {
                console.error("No 'add' response received.");

                var error = data.responseJSON.error;

                for (var i in error) {
                    if (error.hasOwnProperty(i)) {
                        var sfield = form.find('select[name=' + error[i] + ']');
                        sfield.addClass('requiredfield');
                        $('<strong class="requiredfield"><em>' + strings.req + '</em><strong>').insertAfter(sfield[0].nextSibling);
                    }
                }

                status.hide();
            }).done(function() {
                // User saved notification.
                status.find('.saving').hide();
                status.find('.done').show();

                setTimeout(function() {
                    status.fadeOut(function() {
                        status.hide();
                        status.find('.saving').show();
                        status.slideUp();
                        form[0].reset();

                        // Change save button back to normal.
                        savebutton.removeClass('update');
                        $('#add_notification_id').remove();
                        $('#add_notification_purpose').val('add');
                        savebutton.val(strings.save);
                    });
                }, 1500);

                $('#advnotifications_table_wrapper').load('# #advnotifications_table_wrapper > *');
            });
        });

        // LIVE PREVIEW.
        // Dynamically update preview alert as user changes textbox content.
        addregion.on('input propertychange paste', '#add_notification_title, #add_notification_message', function() {
            $('#add_notification_wrapper_id').find('.preview-' + $(this).attr('name')).text($(this).val());
        });

        // Dynamically update preview alert type.
        $('#add_notification_type').on('change', function() {
            var alerttype = $(this).val();
            var previewalert = $('#add_notification_wrapper_id .preview-alert');

            if (alerttype !== 'info' && alerttype !== 'success' && alerttype !== 'warning' && alerttype !== 'danger') {
                alerttype = 'info';
            }

            previewalert.removeClass('alert-info alert-success alert-danger alert-warning');
            previewalert.addClass('alert-' + alerttype);

            $('.preview-aicon').find('> img').attr('src', M.cfg.wwwroot + '/blocks/advnotifications/pix/' + alerttype + '.png');
        });

        $('#add_notification_dismissible').on('change', function() {
            // Checking specifically whether ticked/checked or not to ensure it's displayed correctly (not toggling).
            if (!this.checked) {
                $('.preview-alert-dismissible').hide();
            } else {
                $('.preview-alert-dismissible').show();
            }
        });

        $('#add_notification_aicon').on('change', function() {
            // Checking specifically whether ticked/checked or not to ensure it's displayed correctly (not toggling).
            if (!this.checked) {
                $('.preview-aicon').hide();
            } else {
                $('.preview-aicon').show();
            }
        });

        var init = function() {
            // Get strings.
            var senddata = {}; // Data Object.
            senddata.call = 'ajax';
            senddata.purpose = 'strings';

            var callpath = M.cfg.wwwroot + "/blocks/advnotifications/pages/process.php?sesskey=" + M.cfg.sesskey;

            $.post(callpath, senddata).fail(function() {
                console.error("No 'strings' response received.");
            }).done(function(data) {
                // Store strings and update preview (TODO: ONLY DO THIS IF AJAX SUCCESSFUL - don't render with English first?).
                strings = data;
            }).always(function() {
                // Always prepend live preview. Will use langstrings if AJAX successful, otherwise the strings declared at top.
                refreshPreview();
            });

            // JS is enabled, so we can use AJAX in the new notification form.
            $('#add_notification_form').append('<input type="hidden" id="add_notification_call" name="call" value="ajax"/>');
        };

        var refreshPreview = function() {
            var previewelem = $('#notification_preview_wrapper');

            if (previewelem.length > 0) {
                previewelem.remove();
            }
            $('<div id="notification_preview_wrapper" style="display: none"><strong>' + strings.preview + '</strong><br><div class="alert alert-info preview-alert"><div class="preview-aicon" style="display: none;"><img src="' + M.cfg.wwwroot + '/blocks/advnotifications/pix/info.png' + '" /></div><strong class="preview-title">' + strings.title + '</strong> <div class="preview-message">' + strings.message + '</div> <div class="preview-alert-dismissible" style="display: none;"><strong>&times;</strong></div></div></div>').prependTo($(addregion)).slideDown();
        };

        var checkRequired = function() {
            var disselopt = $('#add_notification_form select option:selected:disabled');

            for (var opt in disselopt) {
                if (disselopt.hasOwnProperty(opt)) {
                    if ($(disselopt[opt]).prop('disabled')) {
                        $(disselopt[opt]).closest('select').addClass('requiredfield');
                        $('<strong class="requiredfield"><em>' + strings.req + '</em><strong>')
                            .insertAfter($(disselopt[opt]).closest('select')[0].nextSibling);

                        return false;
                    }
                }
            }
            return true;
        };

        var refreshRequired = function() {
            $('select.requiredfield').removeClass('requiredfield');
            $('strong.requiredfield').remove();
        };

        init();
    });
});