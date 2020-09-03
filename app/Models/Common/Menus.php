<?php


namespace App\Models\Common;

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


    /**
     * 获取权限表树形菜单
     * @param int $is_super
     * @return array
     */
    public function getElTree($is_super = 0){
        $data = $this->newQuery()->orderBy('sort_field')->get();
        return $this->elTree($data, 0, $is_super);
    }

    /**
     * el-tree树形控件
     * @param $item
     * @param $parent_id
     * @param $is_super
     * @return array
     */
    public function elTree(&$item, $parent_id, $is_super){
        $list = array();
        if(!empty($item)){
            foreach ($item as $k => $v) {
                if ($v['parent_id'] == $parent_id) {
                    $subs = $this->elTree($item, $v['id'], $is_super);
                    $list_item['id'] = $v['id'];
                    $list_item['label'] = $v['name'];
                    if($is_super){
                        $list_item['disabled'] = true;
                    }
                    if(!empty($subs)){
                        $list_item['children'] = $subs;
                    }
                    array_push($list, $list_item);
                }
            }
        }
        return $list;
    }
}
