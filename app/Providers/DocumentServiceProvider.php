<?php
/**
 * Service provider
 *
 * PHP version 5
 *
 * @category    Providers
 * @package     App\Providers
 * @author      XE Team (developers) <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Xpressengine\Document\ConfigHandler;
use Xpressengine\Document\DocumentHandler;
use Xpressengine\Document\InstanceManager;
use Xpressengine\Document\Models\Document;

/**
 * laravel service provider
 *
 * @category    Providers
 * @package     App\Providers
 * @author      XE Team (developers) <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
class DocumentServiceProvider extends ServiceProvider
{
    /**
     * boot
     *
     * @return void
     */
    public function boot()
    {
        Document::creating(function (Document $model) {
            $model->setReply();
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $app = $this->app;

        // set reply code length config to Document model
        Document::setReplyCharLen($app['config']['xe.documentReplyCodeLen']);

        $app->singleton('xe.document.config', function ($app) {
            return new ConfigHandler($app['xe.config']);
        });
        $app->singleton('xe.document.instance', function ($app) {
            $instanceManagerClass = $app['xe.interception']->proxy(InstanceManager::class, 'DocumentInstanceManager');
            return new $instanceManagerClass(
                $app['xe.db']->connection('document'),
                $app['xe.document.config']
            );
        });
        $app->singleton('xe.document', function ($app) {
            $documentHandlerClass = $app['xe.interception']->proxy(DocumentHandler::class, 'Document');
            $document = new $documentHandlerClass(
                $app['xe.db']->connection('document'),
                $app['xe.document.config'],
                $app['xe.document.instance'],
                $app['request']
            );

            return $document;
        });

        $app->bind(
            DocumentHandler::class,
            'xe.document'
        );
    }
}
