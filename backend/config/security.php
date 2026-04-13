<?php

return [
    'auth' => [
        'max_login_attempts' => (int) env('AUTH_MAX_LOGIN_ATTEMPTS', 5),
        'lockout_seconds' => (int) env('AUTH_LOCKOUT_SECONDS', 300),
        'password_history_limit' => (int) env('AUTH_PASSWORD_HISTORY_LIMIT', 5),
    ],
];
