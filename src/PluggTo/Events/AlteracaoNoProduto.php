<?php
/**
 * Created by PhpStorm.
 * User: diego
 * Date: 18/08/16
 * Time: 23:29
 */

namespace PluggTo\Events;


use PluggTo\Model\Produto;

class AlteracaoNoProduto
{
    /**
     * @var Produto
     */
    public $produto;
    /**
     * @var array
     */
    public $data;

    /**
     * AlteracaoNoProduto constructor.
     * @param Produto $produto
     * @param array $data
     */
    public function __construct(Produto $produto, $data)
    {
        $this->produto = $produto;
        $this->data = $data;
    }

}