<?php
/*
|--------------------------------------------------------------------------
| Plugg To SDK Config
|--------------------------------------------------------------------------
|
|
*/
return [
	// Modulo de acesso ao usuario
	'user_model' => \App\User::class,
	// Credenciais para acesso ao plugg To
	'credencials' => [
		'client' => env('PLUGG_CLIENT', ''),
		'password' => env('PLUGG_PASSWORD', '')
	]
];