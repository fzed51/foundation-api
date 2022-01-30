<?php
declare(strict_types=1);

return [
    'settings' => [
        'outputBuffering' => false,
        'determineRouteBeforeAppMiddleware' => false,
        'displayErrorDetails' => false,
        'addContentLengthHeader' => true,
        'routerCacheFile' => false,
        /* settings supplémentaires */
        /* -+---------------------- */
        /*  |                       */
        /*  v                       */
        'logger' => [
            'name' => 'ProjetApi',
            'path' => __DIR__ . '/../logs/day.log',
        ],
        'apiParams' => [
        ],
        "mailer"=>[
            "Host"=> "smtp.mail.com",
            "Port"=> 25,
            "Username"=> "mon@mail.fr",
            "Password"=> "m0tdepAssE",
            "From"=> "mon@mail.fr",//adresse d’envoi correspondant au login entré précédemment
            "FromName"=> "moi", // nom qui sera affiché
            "replyToName"=> "No-Reply", // nom qui sera affiché pour le retour
            "replyToEmail"=> "No-Reply@mail.fr", // email de retour qui est donnée

        ]
    ]
];
