<?php


namespace App\Models\Base;


use App\Models\BaseModel;

class Menus extends BaseModel
{
    protected $table = 'menus';
    protected $guarded = [];


    /**
     * 权限菜单
     * @param int $isSuper
     * @param array $menu_ids
     * @return array
     */
    public function permissionMenusAndRoute($isSuper = 0, $menu_ids = array()){
        if($isSuper){
            $menuData = $this->newQuery()->where('is_show', 1)->orderBy('sort_field')->get();
            $routeData = $this->newQuery()->where('is_show', 1)->where('is_related_route', 1)->orderBy('sort_field')->get();
        }else{
            $menuData = $this->newQuery()->where('is_show', 1)->whereIn('id', $menu_ids)->orderBy('sort_field')->get();
            $routeData = $this->newQuery()->where('is_show', 1)->where('is_related_route', 1)->whereIn('id', $menu_ids)->orderBy('sort_field')->get();
        }
        return [
            'menus' => $this->vueMenuTree($menuData, 0, 1),
            'routes' => $routeData
        ];
    }

    /**
     * 构建vue菜单
     * @param $item
     * @param $parent_id
     * @param $level
     * @return array
     */
    public function vueMenuTree(&$item, $parent_id, $level)
    {
        $list = array();
        foreach ($item as $k => $v) {
            $v['index'] = trim($v['path'], '/');
            if ($v['parent_id'] == $parent_id) {
                $v['level'] = $level;
                $v['subs'] = $this->vueMenuTree($item, $v['id'], $level + 1);
                if (empty($v['subs'])) unset($v['subs']);
                $list[] = $v;
            }
        }
        return $list;
    }
}
