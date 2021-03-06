<?php namespace Xpressengine\UIObjects\Permission;

use XeFrontend;
use Xpressengine\UIObject\AbstractUIObject;

/**
 * Class Permission
 *
 * @package Xpressengine\UIObjects\Permission
 */
class Permission extends AbstractUIObject
{
    /**
     * @var string
     */
    protected static $id = 'uiobject/xpressengine@permission';
    /**
     * @var
     */
    protected $maxShowItemDepth;

    /**
     * render
     *
     * @return string
     */
    public function render()
    {
        XeFrontend::js('/assets/vendor/lodash/lodash.min.js')->load();
        XeFrontend::js('/assets/core/permission/PermissionTag.js')->type('text/jsx')->load();
        XeFrontend::js('/assets/core/permission/PermissionTagSuggestion.js')->type('text/jsx')->load(
        );
        XeFrontend::js('/assets/core/permission/PermissionInclude.js')->type('text/jsx')->load();
        XeFrontend::js('/assets/core/permission/PermissionExclude.js')->type('text/jsx')->load();
        XeFrontend::js('/assets/core/permission/Permission.js')->type('text/jsx')->load();
        XeFrontend::css('/assets/core/permission/permission.css')->load();

        $permissioinScriptString = [];

        $permissioinScriptString[] = "<script type='text/jsx'>";
        $permissioinScriptString[] = "$('.__xe__uiobject_permission').each(function(i) {";
        $permissioinScriptString[] = "var el = $(this),";
        $permissioinScriptString[] = "data = el.data('data');";
        $permissioinScriptString[] = "key= el.data('key');";
        $permissioinScriptString[] = "type = el.data('type');";
        $permissioinScriptString[] = "memberUrl = el.data('memberUrl');";
        $permissioinScriptString[] = "groupUrl= el.data('groupUrl');";
        $permissioinScriptString[] = "vgroupAll= el.data('vgroupAll');";
        $permissioinScriptString[] = "React.render(<Permission ";
        $permissioinScriptString[] = "
                                    key={key}
                                    memberSearchUrl={memberUrl}
                                    groupSearchUrl={groupUrl}
                                    permission={data}
                                    type={type}
                                    vgroupAll={vgroupAll}
                                    />";

        $permissioinScriptString[] = ", this);\n";
        $permissioinScriptString[] = "});";
        $permissioinScriptString[] = "</script>";

        $permissioinScriptString = implode('', $permissioinScriptString);

        XeFrontend::html('permissionUiobject')->content($permissioinScriptString)->load();

        $htmlString = [];
        $args = $this->arguments;

        $inheritMode = null;

        $grant = $args['grant'];
         $title = $args['title'];
        if (isset($args['mode'])) {
            $inheritMode = $args['mode'];
        }

        $permissionJsonString = $this->getPermissionJsonString($grant, $inheritMode);
        $htmlString[] = $this->loadReactComponent($title.'xe_permission', $title, $permissionJsonString);

        $this->template = implode('', $htmlString);

        return parent::render();
    }

    /**
     * boot
     *
     * @return void
     */
    public static function boot()
    {
        // TODO: Implement boot() method.
    }

    /**
     * getSettingsURI
     *
     * @return void
     */
    public static function getSettingsURI()
    {
    }

    protected function getPermissionJsonString($grant, $inheritMode)
    {
        $permissionValueArray = [];

        if ($inheritMode !== null) {
            $permissionValueArray['mode'] = $inheritMode;
        }

        $groupRepo = app('xe.user.groups');
        $userRepo = app('xe.users');

        $groups = $groupRepo->findMany($grant['group']);
        $users = $userRepo->findMany($grant['user'], ['id','displayName']);
        $excepts = $userRepo->findMany($grant['except'], ['id','displayName']);

        $permissionValueArray['rating'] = $grant['rating'];
        $permissionValueArray['group'] = $groups;
        $permissionValueArray['user'] = $users;
        $permissionValueArray['except'] = $excepts;
        $permissionValueArray['vgroup'] = isset($grant['vgroup']) ? $grant['vgroup'] : [];

        return json_encode($permissionValueArray);
    }

    protected function permissionScript()
    {
    }

    protected function loadReactComponent($container, $title, $jsonRet)
    {

        $memberSearchUrl = route('settings.member.search');
        $groupSearchUrl = route('manage.group.search');
        $vgroupAll = app('xe.user.virtualGroups')->all();

        $uibojectKey = "__xe__permission_{$title}_uiobject_data";

        $container = '__xe__uiobject_permission';

        $vgroups = [];
        foreach ($vgroupAll as $vgroup) {
            $vgroups[] = $vgroup->toArray();
        }
        $jsonVGroups = json_enc($vgroups);
        $htmlString = [];
        $htmlString[] = "<div class='{$container}' data-data='{$jsonRet}' data-title='{$title}'
                    data-key='{$uibojectKey}' data-member-url='{$memberSearchUrl}' data-group-url='{$groupSearchUrl}'
                    data-type='{$title}' data-vgroup-all='{$jsonVGroups}'></div>";


        return implode('', $htmlString);
    }
}
