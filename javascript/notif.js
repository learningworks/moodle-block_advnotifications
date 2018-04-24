/* eslint no-console: ["error", { allow: ["error"] }] */
require(['jquery'], function($) {
    // JQuery is available via $.
    $(document).ready(function() {
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
    });
});