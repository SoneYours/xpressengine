<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use XePresenter;
use XeStorage;
use XeMedia;
use XeFrontend;
use Symfony\Component\HttpFoundation\Response;
use Validator;
use Xpressengine\Support\Exceptions\HttpXpressengineException;

class SeoController extends Controller
{
    public function getSetting()
    {
        $ruleName = 'seoSetting';

        XeFrontend::rule($ruleName, $this->getRules());

        return XePresenter::make('seo.setting', [
            'setting' => app('xe.seo')->getSetting(),
            'ruleName' => $ruleName
        ]);
    }

    public function postSetting(Request $request)
    {
        $this->validate($request, $this->getRules());

        $inputs = $request->only([
            'mainTitle',
            'subTitle',
            'keywords',
            'description',
            'twitterUsername',
        ]);
        $setting = app('xe.seo')->getSetting();
        $setting->set($inputs);

        if ($request->file('siteImage') !== null) {
            $file = XeStorage::upload($request->file('siteImage'), 'seo');
            $image = XeMedia::make($file);
            $setting->setSiteImage($image);
        }

        return redirect()->route('manage.seo.edit');
    }

    private function getRules()
    {
        return [
            'mainTitle' => 'LangRequired',
            'keywords' => 'Required',
        ];
    }

    /**
     * Validate the given request with the given rules.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $rules
     * @param  array  $messages
     * @param  array  $customAttributes
     * @return void
     * @throws HttpXpressengineException
     */
    public function validate(Request $request, array $rules, array $messages = [], array $customAttributes = [])
    {
        $validator = Validator::make($request->all(), $rules, $messages, $customAttributes);

        if ($validator->fails()) {
            $request->flash();
            $e = new HttpXpressengineException(Response::HTTP_NOT_ACCEPTABLE);
            $e->setMessage($validator->errors()->first());
            throw $e;
        }
    }
}
