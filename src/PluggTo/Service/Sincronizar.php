<?php
/**
 * Created by PhpStorm.
 * User: diego
 * Date: 18/08/16
 * Time: 00:23
 */

namespace PluggTo\Service;


use PluggTo\Events\AlteracaoNoPedido;
use PluggTo\Events\AlteracaoNoProduto;
use PluggTo\SDK\PluggTo;

class Sincronizar
{
    /**
     * @var \PluggTo\Model\Pedido
     */
    private $pedido;
    /**
     * @var \PluggTo\Model\Produto
     */
    private $produto;

    public function __construct()
    {
        $this->pedido = config('pluggTo.pedido_model');
        $this->produto = config('pluggTo.produto_model');
    }

    /**
     *
     */
    public function downPedido()
    {
        $pagina = 1;
        do {
            $retorno = PluggTo::request('orders', 'GET', [ 'page'=>$pagina ], 'query');
            $pedidos = $retorno['body'];
            foreach ($pedidos['result'] as $resultado)
            {
                $pedido = $this->getPedidoPorPluggId($resultado['Order']['id']);
                event(new AlteracaoNoPedido($pedido, $resultado['Order']));
                $pedido->status = $resultado['Order']['status'];
                $pedido->save();
            }
            $pagina++;
        } while($pedidos['showing'] == $pedidos['limit']);
    }
    public function downProduto()
    {
        $pagina = 1;
        do {
            $retorno = PluggTo::request('products','GET', [ 'page'=>$pagina ], 'query');
            $produtos = $retorno['body'];
            foreach ($produtos['result'] as $resultado)
            {
                $produto = $this->getProdutoPorPluggId($resultado['Product']['id']);
                event(new AlteracaoNoProduto($produto, $resultado['Product']));
                $produto->status = $resultado['Product']['status'];
                $produto->save();
            }
            $pagina++;
        } while($produtos['showing'] == $produtos['limit']);
    }

    /**
     * @param $plugg_id
     * @return \PluggTo\Model\Pedido
     */
    protected function getPedidoPorPluggId($plugg_id)
    {
        return $this->createModel($this->pedido)->firstOrCreate(['plugg_id'=> $plugg_id, 'user_id'=> PluggTo::userId()]);
    }

    /**
     * @param $plugg_id
     * @return \PluggTo\Model\Produto
     */
    protected function getProdutoPorPluggId($plugg_id)
    {
        return $this->createModel($this->produto)->firstOrCreate(['plugg_id'=> $plugg_id, 'user_id'=> PluggTo::userId()]);
    }

    /**
     * Create a new instance of the model.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createModel($model)
    {
        $class = '\\'.ltrim($model, '\\');

        return new $class;
    }

}