<?php
/**
 * Created by PhpStorm.
 * User: tan wei
 * Date: 2018/8/1
 * Time: 17:10
 */

namespace App\Console\Commands\ConfigDB;

use Illuminate\Console\Command;

class ConfigFlush extends Command
{

    protected $signature = 'base_config:flush';

    protected $description = 'flush db_config cache';


    /**
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     *
     * @return void
     */
    public function handle()
    {
        config_db()->flush();
        $this->info('db_config cache has been flushed');
    }
}