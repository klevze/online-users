<?php

return [
    // How many minutes we consider a user "active"
    'threshold' => env('ONLINE_USERS_THRESHOLD', 5),

    // Tracking strategy: supported values - 'ip', 'session', 'user_id'.
    // When set to 'user_id', the package will attempt to use auth()->id().
    'tracking' => env('ONLINE_USERS_TRACKING', 'ip'),

    // Table used for tracking user activity
    'table' => env('ONLINE_USERS_TABLE', 'user_activities'),
    // Whether to anonymize (hash) IP addresses before storing them.
    'anonymize_ip' => env('ONLINE_USERS_ANONYMIZE_IP', false),

    // Salt used when hashing IPs; keep secret in production.
    'ip_salt' => env('ONLINE_USERS_IP_SALT', null),
    // Algorithm used to hash IPs. Supported algorithms depend on hash() (e.g., sha1, sha256).
    'hash_algorithm' => env('ONLINE_USERS_HASH_ALGORITHM', 'sha256'),

    // When anonymization is enabled, whether to still store raw IPs in `user_ip`.
    'store_raw_ip' => env('ONLINE_USERS_STORE_RAW_IP', false),
];
