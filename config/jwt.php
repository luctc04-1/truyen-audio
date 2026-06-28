<?php

return [

    'secret' => env('JWT_SECRET', env('APP_KEY')),

    'ttl' => (int) env('JWT_TTL', 10080), // minutes (default 7 days)

    'algo' => env('JWT_ALGO', 'HS256'),

];
