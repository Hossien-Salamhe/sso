<?php

namespace ZamanTech\Sso\Http\Controllers;

use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Config;

class SsoController
{
    protected $engenesis_config, $content, $client, $body, $userDetails;

    public function __construct($token = null)
    {
        $this->engenesis_config = Config::get("zaman-tech.engenesis");
        $this->client = new Client();
        $this->setBody($token);
    }

    public function getContent()
    {
        try {
            $response = $this->client->send($this->ContentRequest());
            $this->content = json_decode($response->getBody()->getContents());
            if ($this->content->status == 'error') {
                throw new \RuntimeException($this->content->msg);
            }
            return $this->content;
        } catch (Exception $exception) {
            return response()->json([
                'status' => 'error',
                'msg' => $exception->getMessage()
            ]);
        }

    }

    public function getUserDetails()
    {
        try {
            $response = $this->client->send($this->UserDetailsRequest());
            $this->userDetails = json_decode($response->getBody()->getContents());
            if(isset($this->userDetails->meta) && $this->userDetails->meta->error){
                throw new \RuntimeException($this->userDetails->meta->msg);
            }
            return $this->userDetails;
        } catch (Exception $exception) {
            return response()->json([
                'status' => 'error',
                'msg' => $exception->getMessage()
            ]);
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
            'country_code' => $this->userDetails->country->code,
            'country' => $this->userDetails->country->code,
            'engenesis_id_access_token' => $this->content->accessToken,
            'engenesis_id_refresh_token' => $this->content->refreshToken,
            'engenesis_id_access_token_expires_at' => Carbon::parse($this->content->expiresAt),
        ];
    }

    public function logout($user)
    {
        $response = $this->client->send($this->LogoutRequest($user));
        $userDetails = json_decode($response->getBody()->getContents());
        return $userDetails;
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
            $this->getHeaders($this->content->accessToken),
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
            return response()->json([
                'status' => 'ok',
                'msg' => $response
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'status' => 'error',
                'msg' => $exception->getMessage()
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
            return response()->json([
                'status' => 'ok',
                'msg' => $response
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'status' => 'error',
                'msg' => $exception->getMessage()
            ]);
        }
    }

    private function addNewPaymentRequest($user, $address, $last_4_digits, $holder_name, $expiration_date)
    {
        return new Request(
            'POST',
            $this->getAddNewPaymentUrl(),
            $this->getHeaders($user->engenesis_id_access_token),
            json_encode([
                "sso_id" => $user->provider_id,
                "app_id" => $this->engenesis_config['app_id'],
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


    public function addNewTransaction($user, $descriptions, $amount)
    {
        try {
            $response = $this->client->send($this->addNewTransactionRequest($user, $descriptions, $amount));
            return response()->json([
                'status' => 'ok',
                'msg' => $response
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'status' => 'error',
                'msg' => $exception->getMessage()
            ]);
        }
    }

    private function addNewTransactionRequest($user, $descriptions, $amount)
    {
        return new Request(
            'POST',
            $this->getAddNewTransactionUrl(),
            $this->getHeaders($user->engenesis_id_access_token),
            json_encode([
                "sso_id" => $user->provider_id,
                "app_id" => $this->engenesis_config['app_id'],
                "descriptions" => $descriptions,
                "amount" => $amount,
            ])
        );
    }

    private function getAddNewTransactionUrl()
    {
        return $this->engenesis_config['app_url'] . "/api/transaction/add-new-transaction";
    }
}
