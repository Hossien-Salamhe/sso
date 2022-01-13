<?php

namespace Modules\User\Http\Controllers\Webhook;

use ZamanTech\Sso\Http\Requests\SsoWebhookRequest;

abstract class SsoWebhook
{
    public function updateUserInfo(SsoWebhookRequest $request)
    {
        $validated = $request->validated();
        $this->updateUserInfo($validated);
        return "OK";
    }

    abstract public function updateInfo($user);
}
