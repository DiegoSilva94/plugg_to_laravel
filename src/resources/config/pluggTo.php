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
    'pedido_model' => \PluggTo\Model\Pedido::class,
    'produto_model' => \PluggTo\Model\Produto::class,
	// Credenciais para acesso ao plugg To
	'credencials' => [
		'client' => env('PLUGG_CLIENT', ''),
		'password' => env('PLUGG_PASSWORD', '')
	]
];