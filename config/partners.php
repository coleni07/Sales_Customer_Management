<?php

/**
 * API keys issued to the other ERP module groups so they can call
 * our outbound integration endpoints (routes/api.php).
 *
 * Each group gets its own named key so we know (and can log/rotate)
 * who is calling us. Set the real values in .env — never commit real
 * keys to source control.
 */
return [
    'api_keys' => [
        'finance' => env('PARTNER_KEY_FINANCE'),
        'inventory' => env('PARTNER_KEY_INVENTORY'),
    ],
];
