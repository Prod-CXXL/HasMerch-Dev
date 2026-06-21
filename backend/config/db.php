<?php

/**
 * Database configuration
 *
 * Reads credentials from environment variables.
 * Never hardcode credentials here — use a .env file locally
 * and Cloudflare Pages environment variables in production.
 *
 * See .env.example and SECURITY.md for setup instructions.
 */

return [
    'host'   => getenv('DB_HOST')   ?: 'localhost',
    'dbname' => getenv('DB_NAME')   ?: 'hasmerch',
    'user'   => getenv('DB_USER')   ?: 'root',
    'pass'   => getenv('DB_PASS')   ?: 'HasMerch@22',
];
