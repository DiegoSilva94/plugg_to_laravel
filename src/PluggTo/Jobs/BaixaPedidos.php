<?php

namespace PluggTo\Jobs;


use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use PluggTo\SDK\PluggTo;
use PluggTo\Service\Sincronizar;

class BaixaPedidos extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    /**
     * @var
     */
    private $user_id;

    /**
     * Create a new job instance.
     * @param $user_id
     */
    public function __construct($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        PluggTo::loadUser($this->user_id);
        $sincroniza = new Sincronizar();
        $sincroniza->downPedido();
    }
}