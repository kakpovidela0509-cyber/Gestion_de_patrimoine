<?php

return [
    // En Docker : http://auth-service:8000, http://patrimoine-service:8000, http://inventaire-service:8000
    'auth_url' => env('AUTH_SERVICE_URL', 'http://localhost:8001'),
    'patrimoine_url' => env('PATRIMOINE_SERVICE_URL', 'http://localhost:8002'),
    'inventaire_url' => env('INVENTAIRE_SERVICE_URL', 'http://localhost:8003'),
];