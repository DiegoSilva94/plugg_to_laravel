<?php

namespace PluggTo\SDK;

use Input;
use Session;
use Exception;

class PluggTo
{

	public $name;
	public $email;
	public $access_token;
	public $refresh_token;
	public $expire_access;
	public $debug = true;
	public $heads = array('Content-Type:application/json');
	public $tries = 1;
	public $dev;
	public $peso;
	public $dimensions;
	public $validate_ean;
	public $origin_sku;


	public function __construct($offline = false)
	{
		if (!$offline)
			$this->bootstrap();
	}

	private function bootstrap()
	{
		$connect = Session::get('connect');
		$plugg_code = Input::get('pluggtocode');
		if(empty($connect) && empty($plugg_code))
			throw new PluggToException("Codigo de acesso invalido ou não informado", 1);
		if(!empty($connect))
			return $this->auth();
		Session::put('connect', $plugg_code);
		return $this->authByCode();
	}

	private function authByCode()
	{

		$body = array(
			'grant_type' => 'authorization_code',
			'code' => Session::get('connect'),
			'client_id' => config('pluggTo.credencials.client'),
			'client_secret' => config('pluggTo.credencials.password')
		);

		try {
			$result = $this->request('Oauth/token', 'POST', $body, 'http');
		catch (Exception $e) {
			throw new PluggToException("Problema na conexão", 2);
		}

		if($result['status']['http_code'] != 200)
			throw new PluggToException("Acesso negado", 3);

		try {
			$this->saveData($result);
		} catch (Exception $e) {
			throw new PluggToException("Não foi possivel armazenar o usuario", 4);
		}

	}

	// Retira informações do Banco de dados, joga para seção
	public function loadUser($userid)
	{

		try {
			$data = config('pluggTo.user_model')::firstOrNew(array('plugg_id' => $userid));
			$this->access_token = $data['access_token'];
			$this->refresh_token = $data['refresh_token'];
			$this->expire_access = $data['expire_access'];
			$this->id = $data['plugg_id'];
			$this->name = $data['name'];
			$this->email = $data['email'];
			$this->dev = $data['dev'];


			if (empty($this->id)) {
				$me = $this->request('users', 'GET', null, 'http');
				$this->user['info'] = $me['body'];
			}

			Session::put('Plugg', serialize($this));
			Session::put('Plugg_User', $this->id);

		} catch (\Exception $e) {


			return null;
		}

		return $this;
	}

	// salva informações na Sessão e Banco de Dados
	public function saveData($data)
	{

		$this->access_token = $data['body']['access_token'];
		$this->refresh_token = $data['body']['refresh_token'];
		$this->expire_access = time() + $data['body']['expires_in'] - 60;

		if (!empty($this->id)) {


			$this->db = config('pluggTo.user_model')::firstOrNew(array('plugg_id' => $this->id));
			$this->db->access_token = $this->access_token;
			$this->db->refresh_token = $this->refresh_token;
			$this->db->expire_access = $this->expire_access;
			$this->db->push();

		} else {


			$me = $this->request('users', 'GET', array(), 'http');

			$this->id = $me['body']['data']['id'];
			$this->nome = $me['body']['data']['name'];
			$this->email = $me['body']['data']['email'];
			$db = User::firstOrNew(array('plugg_id' => $this->id));


			$db->access_token = $this->access_token;
			$db->refresh_token = $this->refresh_token;
			$db->expire_access = $this->expire_access;
			$db->email = $this->email;
			$db->name = $this->nome;
			$db->plugg_id = $this->id;
			$db->save();

		}
		$this->vtex = $db;
		$this->dev = $db->dev;
		$this->peso = $db->peso;
		$this->dimensions = $db->dimensions;
		$this->validate_ean = $db->validate_ean;
		$this->origin_sku = $db->origin_sku;

		Session::put('Plugg', serialize($this));
		Session::put('Plugg_User', $this->id);
	}


	public function authByRefresh()
	{


		$body = array(
			'grant_type' => 'refresh_token',
			'client_id' => $this->credencials->plugg_client,
			'client_secret' => $this->credencials->plugg_password,
			'refresh_token' => $this->refresh_token
			);

		try {
			$result = $this->request('Oauth/token', 'POST', $body, 'http');
			$this->saveData($result);
		} CATCH (\Exception $e) {
			throw new \Exception("Not possible to get authentication by refresh token", 1);
		}

	}


	public function auth()
	{

		// caso possua e não esteja expira, não faça nada
		if (!empty($this->access_token) && $this->expire_access > time()) {

		// depois refresh token

		} elseif (!empty($this->refresh_token)) {


			try {

				$this->authByRefresh();

			} catch (\Exception $e) {

				$this->authByCode();

			}

		// auth by code
		} else {


			try {

				$this->authByCode();

			} catch (\Exception $e) {


				header("http://dev.plugg.to/services/dash/extradev");
			}

		}

	}


	public function request($model, $method, $body = array(), $btype = 'json')
	{

		// get information from database
		if (!empty($this->id)) {
			$this->loadUser($this->id, false);
		}

		$call = curl_init();
		// buld the post data follwing the api needs
		if ($btype == 'json') {
			$posts = json_encode($body);
		} else if ($btype == 'none') {
			$posts = $body;
		} else {
			$posts = urldecode(http_build_query($body));
		}

		if ($model != 'Oauth/token') {
			$this->auth();
			$this->heads = array('Content-Type:application/json');
			$model = $model . '?' . 'access_token=' . $this->access_token;
		} else {
			$this->heads = array('Content-Type:application/x-www-form-urlencoded');
			$model = $model . '?' . $posts;
		}


		if ($btype == 'query') {
			$url = env('API_URL') . $model . '&' . $posts;
		} else {
			$url = env('API_URL') . $model;
		}

		$url = str_replace(" ", "%20", $url);


		$options[CURLOPT_URL] = $url;
		$options[CURLOPT_RETURNTRANSFER] = 1;
		$options[CURLOPT_HTTPHEADER] = $this->heads;
		$options[CURLOPT_SSL_VERIFYPEER] = false;

		if($method != 'GET'){
			$options[CURLOPT_POSTFIELDS] = $posts;
		}

		$options[CURLOPT_CUSTOMREQUEST] = $method;
		$options[CURLOPT_CONNECTTIMEOUT] = 60;
		$options[CURLOPT_RETURNTRANSFER] = 1;
		$options[CURLOPT_SSL_VERIFYHOST] = 0;

		curl_setopt_array($call, $options);

		// execute the curl call
		$answer = curl_exec($call);
		// get the curl statys
		$status = curl_getinfo($call);



		if (!isset($status['http_code']) || empty($status['http_code'])){
			throw new \Exception('OUT');
		}  elseif( $answer === false && ($status['http_code'] != 200 || $status['http_code'] != 201)) {

			if(!empty($answer)){
				throw new \Exception($answer);
			}else{
				throw new \Exception(curl_error($call));
			}
		}

		// close the call
		curl_close($call);

		$retorno['body'] = json_decode($answer,true);
		$retorno['status'] = $status;
		return $retorno;

	}


}
