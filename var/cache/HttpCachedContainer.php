<?php
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use App\Core\DependencyInjection\Container\Container;
use App\Core\DependencyInjection\Container\ParamContainer;

class HttpCachedContainer extends Container
{
    public function __construct()
    {
        $this->parameterContainer = new ParamContainer(array (
  'core' => 
  array (
    'cache_dir' => '/srv/src/app/var/cache',
    'cached_routes_file' => '/srv/src/app/var/cache/routes.php',
  ),
  'shortener' => 
  array (
    'code_length' => 4,
    'storage_path' => '/srv/src/app/var/cache/url_code_pair.txt',
  ),
  'db' => 
  array (
    'driver' => 'mysql',
    'host' => 'mysql',
    'database' => 'url-shortener',
    'username' => 'user',
    'password' => '1111',
    'charset' => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix' => '',
  ),
));

        $this->aliases = [
                    ];

        $this->methodMap = [
                        'App\Core\EventDispatcher\ListenerProvider' => 'AppCoreEventDispatcherListenerProvider',
                        'App\Core\EventDispatcher\EventDispatcher' => 'AppCoreEventDispatcherEventDispatcher',
                        'App\Core\Framework\Router' => 'AppCoreFrameworkRouter',
                        'App\Core\Database\DatabaseAR' => 'AppCoreDatabaseDatabaseAR',
                        'App\Controllers\HomeController' => 'AppControllersHomeController',
                        'App\Controllers\UrlController' => 'AppControllersUrlController',
                        'App\Handlers\MyCustomEventHandler' => 'AppHandlersMyCustomEventHandler',
                        'App\Shortener\Shortener' => 'AppShortenerShortener',
                        'App\Shortener\Services\Parsers\UrlParser' => 'AppShortenerServicesParsersUrlParser',
                        'App\Shortener\Services\Validators\UrlValidator' => 'AppShortenerServicesValidatorsUrlValidator',
                        'App\Shortener\Services\CodeGenerator' => 'AppShortenerServicesCodeGenerator',
                        'App\Shortener\Repositories\DBUrlCodePairRepository' => 'AppShortenerRepositoriesDBUrlCodePairRepository',
                        'App\Shortener\Repositories\FileUrlCodePairRepository' => 'AppShortenerRepositoriesFileUrlCodePairRepository',
                        'App\Shortener\Interfaces\IUrlCodePairRepository' => 'AppShortenerInterfacesIUrlCodePairRepository',
                        'App\Shared\FileSystem\File\Interfaces\IFileWriter' => 'AppSharedFileSystemFileInterfacesIFileWriter',
                        'App\Shared\FileSystem\File\Interfaces\IFileReader' => 'AppSharedFileSystemFileInterfacesIFileReader',
                        'App\Core\EventDispatcher\Interfaces\IListenerProvider' => 'AppCoreEventDispatcherInterfacesIListenerProvider',
                        'Psr\EventDispatcher\ListenerProviderInterface' => 'PsrEventDispatcherListenerProviderInterface',
                        'Psr\EventDispatcher\EventDispatcherInterface' => 'PsrEventDispatcherEventDispatcherInterface',
                        'App\Core\EventDispatcher\Interfaces\IEventListener' => 'AppCoreEventDispatcherInterfacesIEventListener',
                        'App\Shortener\Interfaces\IUrlEncoder' => 'AppShortenerInterfacesIUrlEncoder',
                        'App\Shortener\Interfaces\IUrlDecoder' => 'AppShortenerInterfacesIUrlDecoder',
                        'App\Shortener\Interfaces\IUrlParser' => 'AppShortenerInterfacesIUrlParser',
                        'App\Shortener\Interfaces\IUrlValidator' => 'AppShortenerInterfacesIUrlValidator',
                        'App\Shortener\Interfaces\ICodeGenerator' => 'AppShortenerInterfacesICodeGenerator',
                    ];
    }

    protected function AppCoreEventDispatcherListenerProvider(): \App\Core\EventDispatcher\ListenerProvider    {
            $service = new \App\Core\EventDispatcher\ListenerProvider(...[]);
            $this->setService('App\Core\EventDispatcher\ListenerProvider', $service);        return $service;
    }

    protected function AppCoreEventDispatcherEventDispatcher(): \App\Core\EventDispatcher\EventDispatcher    {
            $service = new \App\Core\EventDispatcher\EventDispatcher(...['listenerProvider' => $this->get('App\Core\EventDispatcher\Interfaces\IListenerProvider') ,]);
            $this->setService('App\Core\EventDispatcher\EventDispatcher', $service);        return $service;
    }

    protected function AppCoreFrameworkRouter(): \App\Core\Framework\Router    {
            $service = new \App\Core\Framework\Router(...['path' => '/srv/src/app/var/cache/routes.php',]);
            $this->setService('App\Core\Framework\Router', $service);        return $service;
    }

    protected function AppCoreDatabaseDatabaseAR(): \App\Core\Database\DatabaseAR    {
            $service = new \App\Core\Database\DatabaseAR(...['database' => 'url-shortener','username' => 'user','password' => '1111','host' => 'mysql','dbDriver' => 'mysql','prefix' => '','charset' => 'utf8','collation' => 'utf8_unicode_ci',]);
            $this->setService('App\Core\Database\DatabaseAR', $service);        return $service;
    }

    protected function AppControllersHomeController(): \App\Controllers\HomeController    {
            $service = new \App\Controllers\HomeController(...['eventDispatcher' => $this->get('Psr\EventDispatcher\EventDispatcherInterface') ,]);
            $this->setService('App\Controllers\HomeController', $service);        return $service;
    }

    protected function AppControllersUrlController(): \App\Controllers\UrlController    {
            $service = new \App\Controllers\UrlController(...[]);
            $this->setService('App\Controllers\UrlController', $service);        return $service;
    }

    protected function AppHandlersMyCustomEventHandler(): \App\Handlers\MyCustomEventHandler    {
            $service = new \App\Handlers\MyCustomEventHandler(...[]);
            $this->setService('App\Handlers\MyCustomEventHandler', $service);        return $service;
    }

    protected function AppShortenerShortener(): \App\Shortener\Shortener    {
            $service = new \App\Shortener\Shortener(...['repository' => $this->get('App\Shortener\Interfaces\IUrlCodePairRepository') ,'urlValidator' => $this->get('App\Shortener\Interfaces\IUrlValidator') ,'urlParser' => $this->get('App\Shortener\Interfaces\IUrlParser') ,'codeGenerator' => $this->get('App\Shortener\Interfaces\ICodeGenerator') ,]);
            $this->setService('App\Shortener\Shortener', $service);        return $service;
    }

    protected function AppShortenerServicesParsersUrlParser(): \App\Shortener\Services\Parsers\UrlParser    {
            $service = new \App\Shortener\Services\Parsers\UrlParser(...[]);
            $this->setService('App\Shortener\Services\Parsers\UrlParser', $service);        return $service;
    }

    protected function AppShortenerServicesValidatorsUrlValidator(): \App\Shortener\Services\Validators\UrlValidator    {
            $service = new \App\Shortener\Services\Validators\UrlValidator(...[]);
            $this->setService('App\Shortener\Services\Validators\UrlValidator', $service);        return $service;
    }

    protected function AppShortenerServicesCodeGenerator(): \App\Shortener\Services\CodeGenerator    {
            $service = new \App\Shortener\Services\CodeGenerator(...[]);
            $this->setService('App\Shortener\Services\CodeGenerator', $service);        return $service;
    }

    protected function AppShortenerRepositoriesDBUrlCodePairRepository(): \App\Shortener\Repositories\DBUrlCodePairRepository    {
            $service = new \App\Shortener\Repositories\DBUrlCodePairRepository(...[]);
            $this->setService('App\Shortener\Repositories\DBUrlCodePairRepository', $service);        return $service;
    }

    protected function AppShortenerRepositoriesFileUrlCodePairRepository(): \App\Shortener\Repositories\FileUrlCodePairRepository    {
            $service = new \App\Shortener\Repositories\FileUrlCodePairRepository(...['fileReader' => $this->get('App\Shared\FileSystem\File\Interfaces\IFileReader') ,'fileWriter' => $this->get('App\Shared\FileSystem\File\Interfaces\IFileWriter') ,]);
            $this->setService('App\Shortener\Repositories\FileUrlCodePairRepository', $service);        return $service;
    }

    protected function AppShortenerInterfacesIUrlCodePairRepository(): \App\Shortener\Repositories\DBUrlCodePairRepository    {
            $service = new \App\Shortener\Repositories\DBUrlCodePairRepository(...[]);
            $this->setService('App\Shortener\Interfaces\IUrlCodePairRepository', $service);        return $service;
    }

    protected function AppSharedFileSystemFileInterfacesIFileWriter(): \App\Shared\FileSystem\File\FileWriter    {
            $service = new \App\Shared\FileSystem\File\FileWriter(...['path' => '/srv/src/app/var/cache/url_code_pair.txt',]);
            $this->setService('App\Shared\FileSystem\File\Interfaces\IFileWriter', $service);        return $service;
    }

    protected function AppSharedFileSystemFileInterfacesIFileReader(): \App\Shared\FileSystem\File\FileReader    {
            $service = new \App\Shared\FileSystem\File\FileReader(...['path' => '/srv/src/app/var/cache/url_code_pair.txt',]);
            $this->setService('App\Shared\FileSystem\File\Interfaces\IFileReader', $service);        return $service;
    }

    protected function AppCoreEventDispatcherInterfacesIListenerProvider(): \App\Core\EventDispatcher\ListenerProvider    {
            $service = new \App\Core\EventDispatcher\ListenerProvider(...[]);
                $service->{'setListener'}(...['eventName' => 'App\Events\MyCustomEvent','listener' => [0 => $this->get('App\Handlers\MyCustomEventHandler') ,1 => 'onMyCustomEvent',],'priority' => 0,]);
            $service->{'setListener'}(...['eventName' => 'App\Events\MyCustomEvent','listener' => [0 => $this->get('App\Handlers\MyCustomEventHandler') ,1 => 'onMyCustomEvent2',],'priority' => -1,]);
        $this->setService('App\Core\EventDispatcher\Interfaces\IListenerProvider', $service);        return $service;
    }

    protected function PsrEventDispatcherListenerProviderInterface(): \App\Core\EventDispatcher\ListenerProvider    {
            $service = new \App\Core\EventDispatcher\ListenerProvider(...[]);
            $this->setService('Psr\EventDispatcher\ListenerProviderInterface', $service);        return $service;
    }

    protected function PsrEventDispatcherEventDispatcherInterface(): \App\Core\EventDispatcher\EventDispatcher    {
            $service = new \App\Core\EventDispatcher\EventDispatcher(...['listenerProvider' => $this->get('App\Core\EventDispatcher\Interfaces\IListenerProvider') ,]);
            $this->setService('Psr\EventDispatcher\EventDispatcherInterface', $service);        return $service;
    }

    protected function AppCoreEventDispatcherInterfacesIEventListener(): \App\Handlers\MyCustomEventHandler    {
            $service = new \App\Handlers\MyCustomEventHandler(...[]);
            $this->setService('App\Core\EventDispatcher\Interfaces\IEventListener', $service);        return $service;
    }

    protected function AppShortenerInterfacesIUrlEncoder(): \App\Shortener\Shortener    {
            $service = new \App\Shortener\Shortener(...['repository' => $this->get('App\Shortener\Interfaces\IUrlCodePairRepository') ,'urlValidator' => $this->get('App\Shortener\Interfaces\IUrlValidator') ,'urlParser' => $this->get('App\Shortener\Interfaces\IUrlParser') ,'codeGenerator' => $this->get('App\Shortener\Interfaces\ICodeGenerator') ,]);
            $this->setService('App\Shortener\Interfaces\IUrlEncoder', $service);        return $service;
    }

    protected function AppShortenerInterfacesIUrlDecoder(): \App\Shortener\Shortener    {
            $service = new \App\Shortener\Shortener(...['repository' => $this->get('App\Shortener\Interfaces\IUrlCodePairRepository') ,'urlValidator' => $this->get('App\Shortener\Interfaces\IUrlValidator') ,'urlParser' => $this->get('App\Shortener\Interfaces\IUrlParser') ,'codeGenerator' => $this->get('App\Shortener\Interfaces\ICodeGenerator') ,]);
            $this->setService('App\Shortener\Interfaces\IUrlDecoder', $service);        return $service;
    }

    protected function AppShortenerInterfacesIUrlParser(): \App\Shortener\Services\Parsers\UrlParser    {
            $service = new \App\Shortener\Services\Parsers\UrlParser(...[]);
            $this->setService('App\Shortener\Interfaces\IUrlParser', $service);        return $service;
    }

    protected function AppShortenerInterfacesIUrlValidator(): \App\Shortener\Services\Validators\UrlValidator    {
            $service = new \App\Shortener\Services\Validators\UrlValidator(...[]);
            $this->setService('App\Shortener\Interfaces\IUrlValidator', $service);        return $service;
    }

    protected function AppShortenerInterfacesICodeGenerator(): \App\Shortener\Services\CodeGenerator    {
            $service = new \App\Shortener\Services\CodeGenerator(...[]);
            $this->setService('App\Shortener\Interfaces\ICodeGenerator', $service);        return $service;
    }
}