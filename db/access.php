<?php
    $capabilities = array(

        'block/advanced_notifications:myaddinstance' => array(
            'captype' => 'write',
            'contextlevel' => CONTEXT_SYSTEM,
            'archetypes' => array(
                'user' => CAP_ALLOW
            ),

            'clonepermissionsfrom' => 'moodle/my:manageblocks'
        ),

        'block/advanced_notifications:addinstance' => array(
            'riskbitmask' => RISK_SPAM | RISK_XSS,

            'captype' => 'write',
            'contextlevel' => CONTEXT_BLOCK,
            'archetypes' => array(
                'editingteacher' => CAP_ALLOW,
                'manager' => CAP_ALLOW
            ),

            'clonepermissionsfrom' => 'moodle/site:manageblocks'
        ),

        'block/advanced_notifications:managenotifications' => array(

            'riskbitmask' => RISK_SPAM,

            'captype' => 'write',
            'contextlevel' => CONTEXT_SYSTEM,
            'archetypes' => array(
                'frontpage' => CAP_PREVENT,
                'guest' => CAP_PREVENT,
                'user' => CAP_PREVENT,
                'student' => CAP_PREVENT,
                'teacher' => CAP_PREVENT,
                'editingteacher' => CAP_PREVENT,
                'coursecreator' => CAP_PREVENT,
                'manager' => CAP_ALLOW,
            ),
        ),
    );