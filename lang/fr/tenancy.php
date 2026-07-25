<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Lignes de langue de la résolution de tenant
    |--------------------------------------------------------------------------
    |
    | Les clés unknown / suspended / expired / provisioning servent à la fois de
    | message pour les réponses JSON de ResolveTenant et de source de texte pour
    | les pages HTML autonomes de resources/views/tenancy/.
    |
    */

    'unknown'      => "Cet espace n'existe pas.",
    'suspended'    => 'Cet espace est temporairement suspendu.',
    'expired'      => 'Votre abonnement a expiré.',
    'provisioning' => "Votre espace est en cours de préparation.",

    // Page « espace introuvable » (404)
    'unknown_title' => 'Espace introuvable',
    'unknown_body'  => "L'adresse que vous avez saisie ne correspond à aucun espace client. Vérifiez l'orthographe du sous-domaine et réessayez.",

    // Page « suspendu » (503)
    'suspended_title' => 'Espace suspendu',
    'suspended_body'  => "L'accès à cet espace a été temporairement suspendu. Si vous pensez qu'il s'agit d'une erreur, contactez le support.",

    // Page « expiré » (503)
    'expired_title' => 'Abonnement expiré',
    'expired_body'  => "L'abonnement associé à cet espace a expiré. Renouvelez votre abonnement pour rétablir l'accès.",

    // Page « en préparation » (503)
    'provisioning_title' => 'Espace en préparation',
    'provisioning_body'  => "Nous finalisons la mise en place de votre espace. Cette opération ne prend habituellement que quelques instants. Merci de réessayer dans un moment.",

    // Éléments communs
    'contact_support' => 'Contacter le support',
    'retry'           => 'Réessayer',
    'need_help'       => "Besoin d'aide ?",

];
