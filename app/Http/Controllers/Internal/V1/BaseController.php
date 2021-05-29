<?php
/**
 * Created by PhpStorm.
 * User: Geikiy
 * Date: 20/01/2021
 * Time: 18:58
 */

namespace App\Http\Controllers\Internal\V1;

use App\Http\Controllers\BaseController as PBaseController;
use App\Http\Bll\Account\AccountBll;
use App\Exception\BusinessException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Constants\Account\AccountConstant;

class BaseController extends PBaseController
{
    public $account_id;

    public $parent_account_id;

    public $account_info;
    public $admin_info;

    public function getAccountModel()
    {
        // return app('activeUser');
        if (!$this->account_info) {
            $this->account_info = Auth::guard('api')->user();
        }
        return $this->account_info;
    }

    public function getAdminModel()
    {
        if (!$this->admin_info) {
            $this->admin_info = Auth::guard('admin')->user();
        }
        return $this->admin_info;
    }


    public function getAccountID()
    {
        // return app('activeUser')->account_id;
        if (!$this->account_id) {
            $this->account_id = $this->getAccountModel()->account_id;
        }
        return $this->account_id;
    }

    public function getParentAccountID()
    {
        // return app('activeUser')->parent_account_id;
        if (!$this->parent_account_id) {
            $user = $this->getAccountModel();
            $parent_account_id = $user->account_id;
            if (in_array($user->account_type, [
                AccountConstant::ACCOUNT_TYPE_AGENCY_SUB,
                AccountConstant::ACCOUNT_TYPE_ADVERTISER_SUB,
            ])) {
                $parent_account_id = $user->pid;
            }
            $this->parent_account_id = $parent_account_id;
        }
        return $this->parent_account_id;
    }
}
