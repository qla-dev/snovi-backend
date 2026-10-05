<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // Facebook page + Instagram business account for the daily sliders (MetaSocialPublisher).
    // Token permissions: pages_manage_posts, pages_read_engagement, instagram_basic, instagram_content_publish.
    'meta' => [
        'page_id' => env('META_PAGE_ID'),
        'page_token' => env('META_PAGE_TOKEN'),
        'ig_user_id' => env('META_IG_USER_ID'),
        'graph_version' => env('META_GRAPH_VERSION', 'v23.0'),
        // Public base of public/ that Meta downloads slides from (the same from web and cron).
        'media_url' => env('SOCIAL_MEDIA_URL', 'https://snovi.qla.dev'),
    ],

    'social' => [
        // bcrypt hash of the secret the agent sends to POST /api/social/publish.
        'secret_hash' => env('SOCIAL_PUBLISH_SECRET_HASH'),
    ],

];
