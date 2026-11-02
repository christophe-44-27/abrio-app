<?php

return [
    'greeting' => 'Hi :name,',

    'verify_code' => [
        'subject' => 'Your Abrio code: :code',
        'intro' => 'Use this code to verify your email address:',
        'expires' => "It expires in :minutes minutes. If you didn't create an Abrio account, you can ignore this email.",
    ],

    'two_factor' => [
        'enabled' => 'enabled',
        'disabled' => 'disabled',
        'subject' => 'Two-factor authentication was :state on your Abrio account',
        'intro' => 'Two-factor authentication was :state on your Abrio account on :when:where.',
        'if_you_enabled' => 'If it was you, there is nothing to do: your account is now better protected.',
        'if_you_disabled' => 'If it was you, there is nothing to do. You can turn it back on anytime in your account settings.',
        'if_not_you' => "If it wasn't you, someone else may have access to your account. Change your password right away, then contact us at :support so we can help.",
        'action' => 'Change my password',
    ],

    'invitation' => [
        'hello' => 'Hello,',
        'subject' => ':inviter invited you to Abrio',
        'intro' => ':inviter invited you to Abrio to compare places together.',
        'details' => 'Create your account in a minute: you will then both see and edit the same budget.',
        'action' => 'Accept the invitation',
        'expires' => 'This link expires in :days days. Don’t know :inviter? Just ignore this email.',
    ],

    'contact' => [
        'subject' => 'Abrio contact: :name',
        'from' => '**From:** :name (:email)',
    ],
];
