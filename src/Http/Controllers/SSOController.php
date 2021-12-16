<?php

namespace ZamanTech\Sso\Http\Controller;

use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Config;

class SSOController
{
    protected $engenesis_config, $content, $client, $body, $userDetails;

    public function __construct($token = null)
    {
        $this->engenesis_config = Config::get("services.engenesis");
        $this->client = new Client();
        $this->setBody($token);
    }

    public function getContent()
    {
        try {
            $response = $this->client->send($this->ContentRequest());
            $this->content = json_decode($response->getBody()->getContents());
            return $this->content;
        } catch (Exception $exception) {
            dd($exception->getMessage());
        }

    }

    public function getUserDetails()
    {
        try {
            $response = $this->client->send($this->UserDetailsRequest());
            $this->userDetails = json_decode($response->getBody()->getContents());
            return $this->userDetails;
        } catch (Exception $exception) {
            dd($exception->getMessage());
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
            'country_code' => $this->userDetails->country,
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
        new Request(
            'GET',
            $this->getLogoutUrl($user),
            $this->getHeaders()
        );
    }

    private function getLogoutUrl($user)
    {
        return $this->engenesis_config['app_url'] . "/api/sso/oauth/logout/" . $user->session_id . "/" . $user->provider_id;
    }

    public function updateRole()
    {

    }
}
