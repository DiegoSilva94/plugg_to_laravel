<?php

namespace PluggTo\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;
use PluggTo\Jobs\BaixaProdutos;

class SincronizarProduto extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pluggTo:sincronizarProduto';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza os produtos com a pluggTo';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $users = config('pluggTo.user_model')::all();
        foreach ($users as $user) {
            dispatch((new BaixaProdutos($user->plugg_id))->onQueue('pluggToProdutos'));
        }
    }
}
