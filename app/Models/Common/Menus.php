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
        $select = array(
            'id',
            'parent_id',
            'name',
            'icon',
            'path',
            'component'
        );
        $builder = $this->newQuery();
        $routeBuilder = clone $builder;
        if(!$isSuper){
            $builder = $builder->whereIn('id', $menu_ids);
            $routeBuilder = $routeBuilder->whereIn('id', $menu_ids);
        }
        $menus = $builder->where('is_show', 1)->latest('sort_field')->get($select);
        $routes = $routeBuilder->where('is_related_route', 1)->get($select);
        return [
            'menus' => $this->vueMenuTree($menus, 0, 1),
            'routes' => $routes
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
        $data = $this->newQuery()->where('is_show', 1)->orderBy('sort_field')->get();
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
                    if(!empty($subs)){
                        $list_item['children'] = $subs;
                    }
                    array_push($list, $list_item);
                }
            }
        }
        return $list;
    }


    /**
     * 菜单列表
     * @return array
     */
    public function getList(){
        $builder = $this->builderQuery()->orderBy('parent_id')->orderByDesc('sort_field');
        return $this->modifyPaginateForApi($builder);
    }


    public function builderQuery(){
        $name = request('name', false);
        $builder = $this->newQuery();
        $builder = $builder->when($name, function ($query) use($name){
            $query->where('name', 'like', '%'. $name. '%');
        });
        return $builder;
    }


    /**
     * 是否含有子菜单
     * @param $id
     * @return bool
     */
    public function hasSubMenu($id){
        return $this->newQuery()->where('parent_id', $id)->exists();
    }
}
