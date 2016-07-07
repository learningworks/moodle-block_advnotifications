<?php
    $plugin->component = 'block_advanced_notifications';  // Recommended since 2.0.2 (MDL-26035). Required since 3.0 (MDL-48494)
    $plugin->version = 201607071321;  // YYYYMMDDHH (year, month, day, 24-hr format hour)
    $plugin->requires = 2015051104; // YYYYMMDDHH (This is the stable version for Moodle 2.9 as at 04/07/2016)
    $plugin->cron      = 24*3600;           // Cron interval 1 day.