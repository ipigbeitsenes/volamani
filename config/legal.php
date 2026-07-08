<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Current Terms & Conditions version
    |--------------------------------------------------------------------------
    | Bump this whenever the Terms materially change. Every buyer/seller is then
    | required to re-accept before they can keep using the platform. A user's
    | accepted version is stored on users.terms_version. Admin-overridable via
    | the `terms_version` setting.
    */
    'terms_version' => env('TERMS_VERSION', '2026-07-08'),
];
