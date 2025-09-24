<?php

return [
    'paths' => ['api/*'],
    
    'allowed_methods' => ['*'],
    
    'allowed_origins' => [
        '*',
    ],
    
    'allowed_origins_patterns' => [],
    
    'allowed_headers' => [
        'Accept',
        'Authorization',
        'Content-Type',
        'X-Requested-With',
        'Origin',
    ],
    
    'exposed_headers' => [],
    
    'max_age' => 0,
    
    'supports_credentials' => false, // FALSE para Bearer tokens
];
