<?php


use Illuminate\Support\Facades\Route;

Route::post('update-user-info-webhook', 'Webhook\SsoWebhook@updateUserInfo');
