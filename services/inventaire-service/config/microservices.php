<?php

return [
    // En Docker : http://auth-service:8000 et http://patrimoine-service:8000
    'auth_url' => env('AUTH_SERVICE_URL', 'http://localhost:8001'),
    'patrimoine_url' => env('PATRIMOINE_SERVICE_URL', 'http://localhost:8002'),
];