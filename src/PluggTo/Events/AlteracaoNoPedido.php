<?php
/**
 * Created by PhpStorm.
 * User: diego
 * Date: 18/08/16
 * Time: 23:31
 */

namespace PluggTo\Events;


use PluggTo\Model\Pedido;

class AlteracaoNoPedido
{
    /**
     * @var Pedido
     */
    private $pedido;
    /**
     * @var array
     */
    private $data;

    /**
     * AlteracaoNoProduto constructor.
     * @param Pedido $pedido
     * @param array $data
     */
    public function __construct(Pedido $pedido, $data)
    {
        $this->pedido = $pedido;
        $this->data = $data;
    }
}