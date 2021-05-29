<?php
/**
 * Created by PhpStorm.
 * User: Geikiy
 * Date: 22/01/2021
 * Time: 16:09
 */

namespace App\Console\Commands;

use App\Constants\Account\AccountConstant;
use App\Http\Bll\Report\ReportBll;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Str;
use think\Hashlib;
use App\Librarys\HelperFunc;
use App\Http\Bll\Project\ProjectBll;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:test';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'demo:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '测试使用';

    public function handle()
    {

    }
}
