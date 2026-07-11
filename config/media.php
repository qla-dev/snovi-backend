<?php

$localStorageUrl = rtrim(env('APP_URL', 'http://localhost'), '/').'/storage';
$storageUrl = env('MEDIA_STORAGE_URL');

return [
    /*
    | Public base URL returned by API resources for files under /storage.
    | Defaults to this application's local public storage. Set the env value
    | to switch every story/music response to another public storage host.
    */
    'storage_url' => rtrim($storageUrl ?: $localStorageUrl, '/'),
];
