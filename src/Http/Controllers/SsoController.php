<?php

namespace ZamanTech\Sso\Http\Controllers;

use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class SsoController
{
    protected $engenesis_config, $content, $client, $body, $userDetails;

    public function __construct($config_name = 'engenesis', $token = null)
    {
        $this->engenesis_config = Config::get("zaman-tech." . $config_name);
        $this->client = new Client();
        $this->setBody($token);
    }

    public function getContent()
    {
        try {
            $response = $this->client->send($this->ContentRequest());
            $this->content = json_decode($response->getBody()->getContents());
            if (isset($this->content->meta) && $this->content->meta->error) {
                return redirect()->away($this->content->redirect_to);
            }
            if (isset($this->content) && $this->content->status == 'error') {
                return redirect()->away($this->content->redirect_to);
            }
            return $this->content;
        } catch (Exception $exception) {
            return response()->json([
                'status' => 'error',
                'msg' => $exception->getMessage() . " === 41 === "
            ]);
        }

    }

    public function getUserDetails()
    {
        try {
            $response = $this->client->send($this->UserDetailsRequest());
            $this->userDetails = json_decode($response->getBody()->getContents());
            if ((isset($this->userDetails->meta) && $this->userDetails->meta->error)) {
                return redirect()->away($this->content->redirect_to);
            }
            return $this->userDetails;
        } catch (Exception $exception) {
            if (isset($this->content) && $this->content->status == 'error') {
                return redirect()->away($this->content->redirect_to);
            }
        }
    }

    public function getUserInfo()
    {
        return [
            'first_name' => $this->userDetails->first_name,
            'last_name' => $this->userDetails->last_name,
            'phone_number' => $this->userDetails->phone_number,
            'email' => $this->userDetails->email,
            'gender' => $this->userDetails->gender,
            'provider_id' => $this->userDetails->provider_id,
            'session_id' => $this->content->sessionId, // $session_id,
            'country_code' => $this->userDetails->country_code,
            'engenesis_id_access_token' => $this->content->accessToken,
            'engenesis_id_refresh_token' => $this->content->refreshToken,
            'engenesis_id_access_token_expires_at' => Carbon::parse($this->content->expiresAt),
        ];
    }

    public function logout($user)
    {
        if ($user) {
            $response = $this->client->send($this->LogoutRequest($user));
            $userDetails = json_decode($response->getBody()->getContents());
            return $userDetails;
        }
        throw new \RuntimeException("User not found!" . " === 92 === ");
    }

    private function getVerifyUrl()
    {
        return $this->engenesis_config['app_url'] . '/api/sso/oauth/verify';
    }

    private function getHeaders($auth = false)
    {
        $headers = [
            "content-type" => 'application/json',
            "accept" => "application/json"
        ];
        if ($auth) {
            $headers['Authorization'] = "Bearer " . $auth;
        }
        return $headers;
    }

    private function setBody($token)
    {
        $this->body = [
            "token" => $token,
            "username" => $this->engenesis_config['app_id'],
            "password" => $this->engenesis_config['app_secret']
        ];
    }

    private function ContentRequest()
    {
        return new Request(
            'POST',
            $this->getVerifyUrl(),
            $this->getHeaders(),
            json_encode($this->body)
        );
    }

    private function getUserDetailsUrl()
    {
        return $this->engenesis_config['app_url'] . '/api/user';
    }

    private function UserDetailsRequest()
    {
        return new Request(
            'GET',
            $this->getUserDetailsUrl(),
            $this->getHeaders($this->content->accessToken ?? "123"),
            json_encode($this->body)
        );

    }

    private function LogoutRequest($user)
    {
        return new Request(
            'GET',
            $this->getLogoutUrl($user),
            $this->getHeaders($user->engenesis_id_access_token)
        );
    }

    private function getLogoutUrl($user)
    {
        return $this->engenesis_config['app_url'] . "/api/sso/oauth/logout/" . $user->session_id . "/" . $user->provider_id;
    }

    public function updateRole($user, string $user_type = null, string $role = null, string $type = null)
    {
        try {
            $response = $this->client->send($this->updateRoleRequest($user, $user_type, $role, $type));
            $content = json_decode($response->getBody()->getContents());
            return response()->json([
                'status' => 'ok',
                'msg' => $content
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'status' => 'error',
                'msg' => $exception->getMessage() . " === 172 === "
            ]);
        }
    }

    private function updateRoleRequest($user, $user_type, $role, $type)
    {
        return new Request(
            'POST',
            $this->getUpdateRoleUrl(),
            $this->getHeaders($user->engenesis_id_access_token),
            json_encode([
                "sso_id" => $user->provider_id,
                "app_id" => $this->engenesis_config['app_id'],
                "user_type" => $user_type,
                "role" => $role,
                "type" => $type,
            ])
        );
    }

    private function getUpdateRoleUrl()
    {
        return $this->engenesis_config['app_url'] . "/api/user-update-role";
    }


    public function addNewPayment($user, $address, $last_4_digits, $holder_name, $expiration_date)
    {
        try {
            $response = $this->client->send($this->addNewPaymentRequest($user, $address, $last_4_digits, $holder_name, $expiration_date));
            $content = json_decode($response->getBody()->getContents());
            return response()->json([
                'status' => 'ok',
                'msg' => $content
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'status' => 'error',
                'msg' => $exception->getMessage() . " === 211 === "
            ]);
        }
    }

    private function addNewPaymentRequest($user, $address, $last_4_digits, $holder_name, $expiration_date)
    {
        return new Request(
            'POST',
            $this->getAddNewPaymentUrl(),
            $this->getHeaders(),
            json_encode([
                "sso_id" => $user->provider_id,
                "app_id" => $this->engenesis_config['app_id'],
                "app_secret" => $this->engenesis_config['app_secret'],
                "address" => $address,
                "last_4_digits" => $last_4_digits,
                "holder_name" => $holder_name,
                "expiration_date" => $expiration_date,
            ])
        );
    }

    private function getAddNewPaymentUrl()
    {
        return $this->engenesis_config['app_url'] . "/api/payment/add-new-payment";
    }


    public function addNewTransaction($user, $descriptions, $amount, $paid_at)
    {
        try {
            // TODO::: process response and get stream to return string msg not request msd
            $response = $this->client->send($this->addNewTransactionRequest($user, $descriptions, $amount, $paid_at));
            $content = json_decode($response->getBody()->getContents());
            return response()->json([
                'status' => 'ok',
                'msg' => $content
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'status' => 'error',
                'msg' => $exception->getMessage() . " === 253 === "
            ]);
        }
    }

    private function addNewTransactionRequest($user, $descriptions, $amount, $paid_at)
    {
        return new Request(
            'POST',
            $this->getAddNewTransactionUrl(),
            $this->getHeaders(),
            json_encode([
                "sso_id" => $user->provider_id,
                "app_id" => $this->engenesis_config['app_id'],
                "app_secret" => $this->engenesis_config['app_secret'],
                "descriptions" => $descriptions,
                "amount" => $amount,
                "paid_at" => $paid_at,
            ])
        );
    }

    private function getAddNewTransactionUrl()
    {
        return $this->engenesis_config['app_url'] . "/api/transaction/add-new-transaction";
    }
}
