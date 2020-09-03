<?php

namespace App\Observers;

use App\Models\Base\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoleObserver
{
    public function deleted(Role $role){
        DB::table('role_menus')->where('role_id', $role->id)->delete();
    }
}
