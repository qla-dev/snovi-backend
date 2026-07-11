<?php

return [
    /*
    | Optional public base URL used for story sound and background music.
    | When empty, the current API request host's /storage path is used.
    */
    'story_audio_storage_url' => env('STORY_AUDIO_STORAGE_URL')
        ? rtrim(env('STORY_AUDIO_STORAGE_URL'), '/')
        : null,
];
