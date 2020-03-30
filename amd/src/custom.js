/* eslint no-console: ["error", { allow: ["error"] }] */
/**
 * @package    block_advnotifications
 * @copyright  2019 onwards LearningWorks Ltd {@link https://learningworks.co.nz/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Zander Potgieter <zander.potgieter@learningworks.co.nz>
 */

/**
 * @module block_advnotifications/custom
 */
define(['jquery'], function($) {
    // JQuery is available via $.

    return {
        initialise: function() {
            // Module initialised.
            $(document).ready(function() {
                // Commonly (multiple times) used elements.
                var mainregion = $('#region-main');
                var addregion = $('#add_notification_wrapper_id');
                var strings = {
                    save: 'Save',
                    update: 'Update',
                    req: 'Required field...',
                    preview: 'Preview',
                    title: 'Title',
                    message: 'Message'
                };

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
                                clearForm();
                                refreshPreview();
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
                            reloadPreview();
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
                    clearForm();
                });

                // Managing more notifications.
                mainregion.on('submit', '#add_notification_form', function(e) {
                    e.preventDefault();
                    var status = $('#add_notification_status');
                    var form = $('#add_notification_form');

                    refreshRequired();
                    if (!checkRequired()) {
                        // Stop if required fields are not supplied.
                        return;
                    }

                    status.show();

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

                        // Clear Form.
                        clearForm();

                        setTimeout(function() {
                            status.fadeOut(function() {
                                status.find('.done').hide();
                                status.find('.saving').show();
                            });
                        }, 1500);

                        $('#advnotifications_table_wrapper').load('# #advnotifications_table_wrapper > *');
                    });
                });

                // LIVE PREVIEW.
                // Dynamically update preview alert as user changes textbox content.
                addregion.on('input propertychange paste', '#add_notification_title, #add_notification_message', function() {
                    reloadPreview();
                });

                // Dynamically update preview alert type.
                $('#add_notification_type').on('change', function() {
                    reloadPreview();
                });

                $('#add_notification_dismissible').on('change', function() {
                    // Checking specifically whether ticked/checked or not to ensure it's displayed correctly (not toggling).
                    reloadPreview();
                });

                $('#add_notification_aicon').on('change', function() {
                    // Checking specifically whether ticked/checked or not to ensure it's displayed correctly (not toggling).
                    reloadPreview();
                });

                // Check if preview is displaying correct (Update it).
                var reloadPreview = function() {
                    // Update title.
                    var title = addregion.find('#add_notification_title');
                    if (title.val().length > 0) {
                        addregion.find('.preview-title')[0].innerHTML = title.val();
                    } else {
                        addregion.find('.preview-title')[0].innerHTML = strings.title;
                    }

                    // Update message.
                    var message = addregion.find('#add_notification_message');
                    if (message.val().length > 0) {
                        addregion.find('.preview-message')[0].innerHTML = message.val();
                    } else {
                        addregion.find('.preview-message')[0].innerHTML = strings.message;
                    }

                    // Check notification type.
                    var alerttype = $('#add_notification_type').val();
                    var previewalert = $('#add_notification_wrapper_id .preview-alert');

                    // Clear existing classes.
                    previewalert.removeClass('alert-info alert-success alert-danger alert-warning announcement');

                    // Special check for announcement type.
                    if (alerttype === 'announcement') {
                        previewalert.addClass(alerttype);
                        alerttype = 'info';
                    }

                    // If anything unexpected, set to info type.
                    if (alerttype !== 'info' && alerttype !== 'success' && alerttype !== 'warning' && alerttype !== 'danger') {
                        alerttype = 'info';
                    }

                    // Add type of alert class.
                    previewalert.addClass('alert-' + alerttype);

                    $('.preview-aicon').find('> img').attr('src', M.util.image_url(alerttype, 'block_advnotifications'));

                    // Check if dismissable.
                    if (!$('#add_notification_dismissible')[0].checked) {
                        $('.preview-alert-dismissible').hide();
                    } else {
                        $('.preview-alert-dismissible').show();
                    }

                    // Check if icon should be shown.
                    if (!$('#add_notification_aicon')[0].checked) {
                        $('.preview-aicon').hide();
                    } else {
                        $('.preview-aicon').show();
                    }
                };

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

                // Shiny new and fresh preview.
                var refreshPreview = function() {
                    var previewelem = $('#notification_preview_wrapper');
                    var previewdom = '<div id="notification_preview_wrapper"><strong>' + strings.preview + '</strong><br><div class="alert alert-info preview-alert"><div class="preview-aicon" style="display: none;"><img src="' + M.util.image_url('info', 'block_advnotifications') + '" /></div><strong class="preview-title">' + strings.title + '</strong> <div class="preview-message">' + strings.message + '</div> <div class="preview-alert-dismissible" style="display: none;"><strong>&times;</strong></div></div></div>';

                    // If it exists already, remove before adding again.
                    if (previewelem.length > 0) {
                        previewelem.remove();
                        // Don't slide in.
                        $(previewdom).prependTo($(addregion));
                    } else {
                        // Slide in.
                        $(previewdom).prependTo($(addregion)).hide().slideDown();
                    }
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

                var clearForm = function() {
                    $('#add_notification_form')[0].reset();
                    refreshRequired();
                    refreshPreview();

                    // Change save button back to normal.
                    var savebutton = $('#add_notification_save');
                    savebutton.removeClass('update');
                    $('#add_notification_id').remove();
                    $('#add_notification_purpose').val('add');

                    savebutton.val(strings.save);
                };

                init();
            });
        }
    };
});