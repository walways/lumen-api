<?php
 
return [
    // Mail Driver
    'driver' => env('MAIL_DRIVER', 'smtp'),
 
    //SMTP Host Address
    'host' => env('MAIL_HOST', 'smtp.dmcgrowth.cn'),
 
    // SMTP Host Port
    'port' => env('MAIL_PORT', 465),
 
    // Global "From" Address
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'noreply@dmcgrowth.cn'),
        'name' => env('MAIL_FROM_NAME', 'By Growth Cloud Service'),
    ],
 
    // E-Mail Encryption Protocol
    'encryption' => env('MAIL_ENCRYPTION', 'ssl'),
 
    // SMTP Server Username
    'username' => env('MAIL_USERNAME'),
 
    'password' => env('MAIL_PASSWORD'),
 
    // Sendmail System Path
    'sendmail' => '/usr/sbin/sendmail -bs',
 
    // Markdown Mail Settings
    'markdown' => [
        'theme' => 'default',
        'paths' => [
            resource_path('views/vendor/mail'),
        ],
    ],
];
