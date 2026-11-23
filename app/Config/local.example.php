<?php

/**
 * Live-server settings WITHOUT a .env file.
 *
 * InfinityFree (and some other free hosts) disable PHP's putenv(), which CodeIgniter's .env loader needs,
 * so a .env file crashes the site there. Instead:
 *   1. Copy this file to app/Config/local.php (on the server, or locally before uploading).
 *   2. Fill in the real values below.
 * local.php is git-ignored, so your password never reaches GitHub.
 */

return [
    // Your site address, WITH a trailing slash. Use https:// only if the https address opens for you.
    'baseURL' => 'https://your-subdomain.example/',

    // InfinityFree > Control Panel > MySQL Databases shows these four values.
    'database' => [
        'hostname' => 'sqlXXX.infinityfree.com',   // NOT "localhost"
        'username' => 'if0_XXXXXXXX',
        'password' => 'your-hosting-account-password',
        'database' => 'if0_XXXXXXXX_pos',
        'DBDriver' => 'MySQLi',
        'port'     => 3306,
    ],
];
