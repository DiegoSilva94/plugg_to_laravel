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
        $users = $this->createModel(config('pluggTo.user_model'))->all();
        foreach ($users as $user) {
            dispatch((new BaixaProdutos($user->plugg_id))->onQueue('pluggToProdutos'));
        }
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
