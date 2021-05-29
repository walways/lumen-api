<?php
/**
 * Created by PhpStorm.
 * User: tan wei
 * Date: 2018/8/1
 * Time: 17:10
 */

namespace App\Console\Commands\ConfigDB;

use Illuminate\Console\Command;

class ConfigForget extends Command
{

    protected $signature = 'base_config:forget {key}';

    protected $description = 'clear db_config cache by key';


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
        $key = $this->argument('key');
        config_db()->forget($key);
        $this->info($key . ' has been forgot');
    }
}