[![Build Status](https://travis-ci.org/learningworks/moodle-block_advnotifications.svg?branch=master)](https://travis-ci.org/learningworks/moodle-block_advnotifications)

## Allows for notifications to be easily managed and set to be displayed to users.

### Features:

* Customisable title
* Customisable message
* Multiple types of notifications (Bootstrap-based)
* Type-based icons (optional setting)
* Dismissible/Non-Dismissible
* Customisable date range to show notification from and to
* Display a notification to the user a set amount of times
* Instance-based or global/site-wide notifications
* Enable/Disable a/all notifications (Site-wide and instance-based)
* Edit/Delete/Restore notifications
* Option to auto-delete notification after end date
* Option to permanently delete notifications that's had the deleted flag for more than 30 days
* Option to automatically remove user (dismissed/seen) records that relates to notifications that don't exist anymore
* AJAX used to improve user-experience and simplify processes
* Live-preview when making/editing a notification

#### Backwards Compatibility/Progressive Enhancement

Although the plugin works and is usable without JavaScript, it is highly recommended to use the plugin with JavaScript enabled.
Using the plugin with JavaScript disabled does not allow for some features to be used to their full potential ranging from
dismissing a notification to dynamically editing existing notifications and the live-preview feature - all of which relies on
JavaScript in some form to make the user's experience more enjoyable.

#### *Work-in-Progress!*

#### Work still being done:

* Allow user that created notification to delete the notification. 
* Nothing more.....