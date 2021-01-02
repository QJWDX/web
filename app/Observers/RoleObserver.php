<?php

namespace App\Observers;
use App\Models\Common\Role;
use Illuminate\Support\Facades\DB;

class RoleObserver
{
    public function deleted(Role $role){
        DB::table('role_menus')->where('role_id', $role->id)->delete();
    }
}
