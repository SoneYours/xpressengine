<?php
namespace App\Http\Controllers;

use App\Http\Requests;
use XeStorage;
use Xpressengine\Http\Request;
use Xpressengine\Menu\Models\Menu;
use Xpressengine\Permission\Grant;
use Xpressengine\Permission\PermissionHandler;
use Xpressengine\Settings\SettingsHandler;
use Xpressengine\Site\SiteHandler;
use Xpressengine\Storage\File;
use Xpressengine\Theme\ThemeHandler;

class SettingsController extends Controller
{

    public function editSetting(SiteHandler $siteHandler, ThemeHandler $themeHandler)
    {
        $config = app('xe.site')->getSiteConfig();

        $siteKey = $siteHandler->getCurrentSiteKey();
        $indexInstance = $siteHandler->getHomeInstanceId();

        $menus = Menu::with('items')->where('siteKey', $siteKey)->get();
        $selectedTheme = $themeHandler->getSiteThemeId();

        return \XePresenter::make(
            'settings.setting',
            compact(
                'config',
                'selectedTheme',
                'menus',
                'indexInstance'
            )
        );
    }

    public function updateSetting(SiteHandler $siteHandler, ThemeHandler $themeHandler, Request $request)
    {
        $newConfig = $request->only(['site_title', 'favicon']);
        $config = $siteHandler->getSiteConfig();

        // resolve site_title
        $config['site_title'] = $newConfig['site_title'];

        // resolve favicon
        $uploadedFavicon = array_get($newConfig, 'favicon');
        if ($uploadedFavicon !== null) {
            // remove old favicon file
            if (isset($config['favicon'])) {
                $oldLogoFileId = $config['favicon'];
                $oldLogoFile = File::find($oldLogoFileId);
                XeStorage::remove($oldLogoFile);
            }
            $favicon = XeStorage::upload($uploadedFavicon, 'filebox');
            $config['favicon'] = $favicon->id;
        }
        $siteHandler->putSiteConfig($config);

        // resolve index instance
        $indexInstance = $request->get('indexInstance');
        $siteHandler->setHomeInstanceId($indexInstance);

        // resolve theme
        $theme = $request->only(['theme_desktop', 'theme_mobile']);
        $theme = ['desktop' => $theme['theme_desktop'], 'mobile' => $theme['theme_mobile']];
        $themeHandler->setSiteTheme($theme);


        return \Redirect::back()->with('alert', ['type' => 'success', 'message' => '저장되었습니다.']);
    }

    public function updateTheme(ThemeHandler $themeHandler, Request $request)
    {
        // resolve theme
        $theme = $request->only(['theme_desktop', 'theme_mobile']);
        $theme = ['desktop' => $theme['theme_desktop'], 'mobile' => $theme['theme_mobile']];
        $themeHandler->setSiteTheme($theme);

        return \Redirect::back()->with('alert', ['type' => 'success', 'message' => '저장되었습니다.']);
    }

    public function editPermissions(PermissionHandler $permissionHandler)
    {
        /** @var SettingsHandler $settingsHandler */
        $settingsHandler = app('xe.settings');
        $permissionGroups = $settingsHandler->getPermissionList();

        foreach ($permissionGroups as $tab => &$group) {
            foreach ($group as $key => &$item) {
                $permission = $permissionHandler->find('settings.'.$item['id']);
                if($permission === null) {
                    $permission = $permissionHandler->register('settings.'.$item['id'], new Grant());
                }
                $item['id'] = 'settings.'.$item['id'];
                $item['permission'] = $permission;
            }
        }

        return \XePresenter::make('settings.permissions', compact('permissionGroups'));
    }

    public function updatePermission(PermissionHandler $permissionHandler, Request $request, $permissionId)
    {
        $permissionHandler->register(
            $permissionId,
            $this->createAccessGrant(
                $request->only(
                    [
                        'accessRating',
                        'accessGroup',
                        'accessUser',
                        'accessExcept'
                    ]
                )
            )
        );

        return redirect()->back()->with('alert', ['type' => 'success', 'message' => '저장되었습니다.']);
    }

    /**
     * createAccessGrant
     *
     * @param array $inputs to create grant params array
     *
     * @return Grant
     *
     */
    protected function createAccessGrant(array $inputs)
    {
        $grant = new Grant;

        $rating = $inputs['accessRating'];
        $group = $this->innerParamParsing($inputs['accessGroup']);
        $user = $this->innerParamParsing($inputs['accessUser']);
        $except = $this->innerParamParsing($inputs['accessExcept']);

        $grant->add('access', 'rating', $rating);
        $grant->add('access', 'group', $group);
        $grant->add('access', 'user', $user);
        $grant->add('access', 'except', $except);

        return $grant;
    }

    protected function innerParamParsing($param)
    {
        if (empty($param)) {
            return [];
        }

        $ret = explode(',', $param);
        return array_filter($ret);
    }

}
