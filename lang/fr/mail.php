<?php

return [
    'greeting' => 'Bonjour :name,',

    'verify_code' => [
        'subject' => 'Ton code Abrio : :code',
        'intro' => 'Utilise ce code pour valider ton adresse courriel :',
        'expires' => "Il expire dans :minutes minutes. Si tu n'as pas créé de compte Abrio, ignore ce courriel.",
    ],

    'two_factor' => [
        'enabled' => 'activée',
        'disabled' => 'désactivée',
        'subject' => 'La double authentification a été :state sur ton compte Abrio',
        'intro' => 'La double authentification a été :state sur ton compte Abrio le :when:where.',
        'if_you_enabled' => 'Si c’est toi, tu n’as rien à faire : ton compte est simplement mieux protégé.',
        'if_you_disabled' => 'Si c’est toi, tu n’as rien à faire. Tu peux la réactiver quand tu veux dans les paramètres du compte.',
        'if_not_you' => 'Si ce n’est pas toi, quelqu’un d’autre a peut-être accès à ton compte. Change ton mot de passe tout de suite, puis écris-nous à :support pour qu’on t’aide.',
        'action' => 'Changer mon mot de passe',
    ],

    'invitation' => [
        'hello' => 'Bonjour,',
        'subject' => ':inviter t’invite sur Abrio',
        'intro' => ':inviter t’invite sur Abrio pour comparer des logements avec toi.',
        'details' => 'Crée ton compte en une minute : vous verrez et modifierez ensuite le même budget.',
        'action' => 'Accepter l’invitation',
        'expires' => 'Ce lien expire dans :days jours. Tu ne connais pas :inviter? Ignore ce courriel.',
    ],

    'contact' => [
        'subject' => 'Contact Abrio : :name',
        'from' => '**De :** :name (:email)',
    ],
];
