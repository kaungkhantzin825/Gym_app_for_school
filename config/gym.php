<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin access
    |--------------------------------------------------------------------------
    |
    | The admin panel is gated on users.role === 'admin'. Emails listed here
    | are always allowed in, which is the escape hatch if the only admin
    | account is lost.
    |
    */

    'admin_emails' => array_filter(array_map(
        'trim',
        explode(',', (string) env('ADMIN_EMAILS', ''))
    )),

];
