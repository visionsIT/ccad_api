<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel CORS
    |--------------------------------------------------------------------------
    |
    | allowedOrigins, allowedHeaders and allowedMethods can be set to array('*')
    | to accept any value.
    |
    */

    'supportsCredentials'    => FALSE,
    'allowedOrigins'         => [ '*', 'http://localhost:3002', 'https://ccadapi.takreem.ae', 'https://ccaddashboard.takreem.ae', 'https://ccad.takreem.ae','https://ccaddashboard.meritincentives.com', 'https://ccad.meritincentives.com', 'https://adfs.clevelandclinicabudhabi.ae' ],
    'allowedOriginsPatterns' => [],
    'allowedHeaders'         => [ '*' ],
    'allowedMethods'         => [ '*' ],
    'exposedHeaders'         => [],
    'maxAge'                 => 0,

];
