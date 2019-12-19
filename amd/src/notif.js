/* eslint no-console: ["error", { allow: ["error"] }] */
/**
 * @package    block_advnotifications
 * @copyright  2019 onwards LearningWorks Ltd {@link https://learningworks.co.nz/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Zander Potgieter <zander.potgieter@learningworks.co.nz>
 */

/**
 * @module block_advnotifications/notif
 */
define(['jquery'], function($) {
    // JQuery is available via $.

    return {
        initialise: function() {
            // Module initialised.
            $(document).ready(function() {
                // USER DISMISSING/CLICKING ON A NOTIFICATION.
                $('.block_advnotifications').on('click', '.dismissible', function() {

                    var dismiss = $(this).attr('data-dismiss');

                    $(this).slideUp('150', function() {
                        $(this).remove();
                    });

                    // TODO - Move ajax call to Moodle's ajax/webservice call.
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
        }
    };
});