<?php
/**
 * Created by PhpStorm.
 * User: diego
 * Date: 17/08/16
 * Time: 22:58
 */

namespace PluggTo\Model;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{

    protected $table = 'pedidos';
    protected $fillable = [
        'plugg_id',
        'user_id',
        'status'
    ];

}