<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Socialite;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;
use Http;
//use GuzzleHttp\Psr7;
//use GuzzleHttp\Exception\ClientException;

class LoginController extends Controller {

    protected $_client_name = "okta-laravel_auth";
    protected $_client_id = "0oab16sk7wCpiQy935d7";
    protected $_api_token_id = "00T1a3poh0cgbftfA5d7";
    protected $_api_token_secret = "00-1lVTib85z5F7t8B1RGV92i0E6dSM71hoa9a0A-O";
    protected $_url_get_current_user = "https://dev-87493811.okta.com/api/v1/users/me";
    protected $_url_add_user_activate = "https://dev-87493811.okta.com/api/v1/users";
    protected $_url_get_user = "https://dev-87493811.okta.com/api/v1/users";
    protected $_url_assigned_user = "https://dev-87493811.okta.com/api/v1/apps";

    /**
     * A felhasználó átirányítása az Okta hitelesítési oldalra.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider() {
        return Socialite::driver('okta')->redirect();
    }

    /**
     * Szerezze be a felhasználói információkat az Oktától.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback(\Illuminate\Http\Request $request) {
        $user = Socialite::driver('okta')->user();

        $localUser = User::where('email', $user->email)->first();

        // create a local user with the email and token from Okta
        if (!$localUser) {
            $localUser = User::create([
                        'email' => $user->email,
                        'name' => $user->name,
                        'token' => $user->token,
                        'id_token' => $user->id_token,
            ]);
        } else {
            // if the user already exists, just update the token:
            $localUser->token = $user->token;
            $localUser->id_token = $user->id_token;
            $localUser->save();
        }

        try {
            Auth::login($localUser);
        } catch (\Throwable $e) {
            return redirect('/login-okta');
        }

        return redirect('/home');
    }

    /**
     * Kiléptetés a helyi rendszerből
     * @return type
     */
    public function logout() {
        Auth::logout();
        return redirect('/');
    }

    /**
     * Kiléptetés az Okta rendszerből
     * @return type
     */
    public function sso_logout() {
        $idToken = Auth::user()->id_token;

        Auth::guard('web')->logout();

        $logoutUrl = Socialite::driver('okta')->getLogoutUrl($idToken, URL::to('/'));

        return redirect($logoutUrl);
    }

    /**
     * Új felhasználó regisztrálása.
     * @param Request $request
     * @return type
     */
    public function add_user(Request $request) {
        // Új felhasználó adatai
        $new_user = $request->all();
        
        // adatok küldése az Okta rendszerbe
        try {
            $response = Http::withHeaders([
                "Accept" => "application/json",
                "Content-Type" => "application/json",
                "Authorization" => "SSWS " . $this->_api_token_secret
            ])
                ->withUrlParameters(["activate" => true])
                ->withBody(json_encode(
                    [
                        "profile" => [
                            "firstName" => $new_user['first_name'],
                            "lastName" => $new_user['last_name'],
                            "email" => $new_user['email'],
                            "login" => $new_user['email']
                        ],
                        "credentials" => [
                            "password" => ["value" => $new_user['password']]
                        ]
                    ]
                ))->post($this->_url_add_user_activate);
        } catch (\Exception $e) {
            \Log::info($e);
            return response()->json(['success' => 0]);
        }


        // felhasználói azonosító beszerzése az alkalmazáshoz való hozzárendeléshez  
        try {
            $response = Http::withHeaders(
                [
                    "Accept" => "application/json",
                    "Content-Type" => "application/json",
                    "Authorization" => "SSWS " . $this->_api_token_secret
                ])->get($this->_url_get_user . '/' . $new_user['email']);

            $response_body = json_decode($response->getBody()->getContents());
            \Log::info(print_r($response_body, true));
            $response_data['id'] = $response_body->id;
        } catch (\Exception $e) {
            \Log::info($e);
            return response()->json(['success' => 1]);
        }

        // Felhasználó hozzárendelése az alkalmazáshoz
        try {
            $response = Http::withHeaders(
                [
                    "Accept" => "application/json",
                    "Content-Type" => "application/json",
                    "Authorization" => "SSWS " . $this->_api_token_secret
                ])->withBody(json_encode(
                    [
                        "id" => $response_data['id'],
                        "scope" => "USER",
                        "credentials" => ["userName" => $new_user['email']]
                    ]
                ))->post($this->_url_assigned_user . '/' . $this->_client_id . '/users');

            $response_body2 = json_decode($response->getBody()->getContents());
            \Log::info(print_r($response_body2, true));
        } catch (\Exception $e) {
            \Log::info($e);
            return response()->json(['success' => 2]);
        }

        return response()->json(['success' => 3]);
    }

    /**
     * Aktuális felhasználó lekérése
     * @return type
     */
    public function current_user() {

        try{
            // get current user
             $response = Http::withHeaders(
                [
                    "Accept" => "application/json",
                    "Content-Type" => "application/json",
                    "Authorization" => "SSWS " . $this->_api_token_secret
                ])->get($this->_url_get_user . '/' . auth()->user()->email);

            $response_body = json_decode($response->getBody()->getContents());
            \Log::info(print_r($response_body, true));
            $response_data['id'] = $response_body->id;
            
            $response_data['name'] = $response_body->profile->firstName . " " . $response_body->profile->lastName;
            $response_data['email'] = $response_body->profile->email;
            $response_data['success'] = true;
        } catch (\Exception $e) {
            \Log::info($e);
            return response()->json(['success' => false]);
        }

        return response()->json($response_data);
    }
}
