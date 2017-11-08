<?php

namespace PluggTo\SDK;

use Input;
use Session;
use Exception;

class PluggTo
{

	protected function __construct()
	{
	}

	public static function bootstrap()
	{
		$connect = Session::get('connect');
		$plugg_code = Input::get('pluggtocode');
		if(empty($connect) && empty($plugg_code))
			throw new PluggToException("Codigo de acesso invalido ou não informado", 1);
		if(!empty($connect))
			return self::auth();
		Session::put('connect', $plugg_code);
		return self::authByCode();
	}

	// Retira informações do Banco de dados, joga para seção
	public static function loadUser($userid)
	{
		try {
			$data = config('pluggTo.user_model')->firstOrNew(['plugg_id' => $userid]);
			Session::put('access_token', $data['access_token']);
			Session::put('refresh_token', $data['refresh_token']);
			Session::put('expire_access', $data['expires_in']);
			Session::put('plugg_id', $data['body']['data']['plugg_id']);
			Session::put('user_id', $data['id']);
			self::auth();
		} catch (Exception $e) {
			if(env('APP_DEBUG'))
				throw new PluggToException($e->getMessage(), 5);
			throw new PluggToException("Usuario não encontrado", 5);
		}
	}

	// salva informações na Sessão e Banco de Dados
	private static function saveData($data)
	{
		Session::put('access_token', $data['body']['access_token']);
		Session::put('refresh_token', $data['body']['refresh_token']);
		Session::put('expire_access', time() + $data['body']['expires_in'] - 60);
		if (empty(Session::get('plugg_id'))) {
			$me = self::request('users', 'GET', [], 'http');
			Session::put('plugg_id', $me['body']['data']['id']);
			$user = config('pluggTo.user_model')->firstOrNew(['plugg_id' => Session::get('plugg_id')]);
			$user->name = $me['body']['data']['name'];
			$user->email = $me['body']['data']['email'];
			$user->status = $me['body']['data']['status'];
		}
		if(!isset($user))
			$user = config('pluggTo.user_model')->firstOrNew(['plugg_id' => Session::get('plugg_id')]);
		$user->access_token  = Session::get('access_token');
		$user->refresh_token = Session::get('refresh_token');
		$user->expire_access = Session::get('expire_access');
		$user->push();
		Session::put('user_id', $user->id);
	}

	public static function userId()
	{
		return Session::get('user_id');
	}

	private static function auth()
	{
		// Verifica se tem o access_token
		if(empty(Session::get('access_token')))
			return self::authByCode();
		// Verifica se o acesso está expirado
		if(Session::get('expire_access') <= time())
			try {
				return self::authByRefresh();
			} catch (\Exception $e) {
				return self::authByCode();
			}
	}

	private static function authByCode()
	{
		$body = [
			'grant_type' => 'authorization_code',
			'code' => Session::get('connect'),
			'client_id' => config('pluggTo.credencials.client'),
			'client_secret' => config('pluggTo.credencials.password')
		];
		$result = self::request('Oauth/token', 'POST', $body, 'http');
		try {
			self::saveData($result);
		} catch (Exception $e) {
			if(env('APP_DEBUG'))
				throw new PluggToException($e->getMessage(), 4);
			throw new PluggToException("Não foi possivel armazenar o usuario", 4);
		}
	}

	private static function authByRefresh()
	{
		$body = [
			'grant_type' => 'refresh_token',
			'client_id' => config('pluggTo.credencials.client'),
			'client_secret' => config('pluggTo.credencials.password'),
			'refresh_token' => Session::get('refresh_token')
		];
		$result = self::request('Oauth/token', 'POST', $body, 'http');
		try {
			self::saveData($result);
		} catch (Exception $e) {
			if(env('APP_DEBUG'))
				throw new PluggToException($e->getMessage(), 4);
			throw new PluggToException("Não foi possivel armazenar o usuario", 4);
		}
	}

	public static function request($model, $method, $body = [], $btype = 'json')
	{
		$call = curl_init();
		// buld the post data follwing the api needs
		if ($btype == 'json')
			$posts = json_encode($body);
		if ($btype == 'none')
			$posts = $body;
		if ($btype != 'json' || $btype != 'none')
			$posts = urldecode(http_build_query($body));
		if ($model != 'Oauth/token') {
			$heads = ['Content-Type:application/json'];
			$model = $model . '?' . 'access_token=' . Session::get('access_token');
		} 
		if ($model == 'Oauth/token') {
			$heads = ['Content-Type:application/x-www-form-urlencoded'];
			$model = $model . '?' . $posts;
		}
		if ($btype == 'query')
			$url = env('API_URL') . $model . '&' . $posts;
		if ($btype != 'query')
			$url = env('API_URL') . $model;
		$url = str_replace(" ", "%20", $url);
		$options[CURLOPT_URL] = $url;
		$options[CURLOPT_RETURNTRANSFER] = 1;
		$options[CURLOPT_HTTPHEADER] = $heads;
		$options[CURLOPT_SSL_VERIFYPEER] = false;
		if($method != 'GET')
			$options[CURLOPT_POSTFIELDS] = $posts;
		$options[CURLOPT_CUSTOMREQUEST] = $method;
		$options[CURLOPT_CONNECTTIMEOUT] = 60;
		$options[CURLOPT_RETURNTRANSFER] = 1;
		$options[CURLOPT_SSL_VERIFYHOST] = 0;
		curl_setopt_array($call, $options);
		// execute the curl call
		$answer = curl_exec($call);
		// get the curl statys
		$status = curl_getinfo($call);
		if ($answer === false || !isset($status['http_code']) || empty($status['http_code']))
			throw new PluggToException('OUT', 5);
		if( $status['http_code'] != 200 && $status['http_code'] != 201 )
			throw new PluggToException(curl_error($call), 6);
		// close the call
		curl_close($call);
		$retorno['body'] = json_decode($answer,true);
		$retorno['status'] = $status;
		return $retorno;
	}
}
