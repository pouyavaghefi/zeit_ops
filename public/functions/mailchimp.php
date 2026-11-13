<?php

use Illuminate\Support\Facades\Http;

$email = filter_input(INPUT_POST, 'lp-subscribe-email', FILTER_VALIDATE_EMAIL);

if (!$email) {
    http_response_code(422);
    echo 'Please enter a valid email address.';
    exit;
}

$apiKey = getenv('MAILCHIMP_API_KEY');
$listId = getenv('MAILCHIMP_LIST_ID');
$datacenter = getenv('MAILCHIMP_DC');

if (!$apiKey || !$listId || !$datacenter) {
    http_response_code(500);
    echo 'Mailchimp configuration is missing.';
    exit;
}

$subscriberHash = md5(strtolower($email));

$url = "https://{$datacenter}.api.mailchimp.com/3.0/lists/{$listId}/members/{$subscriberHash}";

$response = Http::withToken($apiKey)
    ->acceptJson()
    ->put($url, [
        'email_address' => $email,
        'status_if_new' => 'pending',
    ]);

if ($response->successful()) {
    echo '<div class="alert alert-success">
        Please check your email to confirm your subscription.
    </div>';
} else {
    http_response_code(400);
    echo '<div class="alert alert-error">
        Unable to process your subscription.
    </div>';
}
