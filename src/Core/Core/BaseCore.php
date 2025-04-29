<?php

namespace App\Core\Core;

use App\Core\Core\Enums\CoreMode;
use App\Core\Core\Interfaces\IModule;
use App\Core\DependencyInjection\Container\ContainerBuilder;
use App\Core\DependencyInjection\Container\ContainerDumper;
use App\Core\DependencyInjection\Container\Interfaces\IContainer;
use App\Core\DependencyInjection\Container\ParamContainerBuilder;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

abstract class BaseCore
{
    protected ?IContainer $container = null;
    protected ?string $projectDirectoryPath = null;
    protected CoreMode $mode = CoreMode::PRODUCTION;
    protected string $cachedContainerClassName = 'CachedContainer';

    /**
     * @var IModule[]
     */
    protected array $modules = [];

    public function __construct()
    {
    }

    public function addModule(IModule $module): static
    {
        $this->modules[] = $module;
        return $this;
    }

    public function setProjectDirectoryPath(string $projectDirectoryPath): static
    {
        if (!file_exists($projectDirectoryPath)) {
            throw new \InvalidArgumentException("Project directory '$projectDirectoryPath' does not exist");
        }

        $this->projectDirectoryPath = $projectDirectoryPath;
        return $this;
    }

    public function getProjectDirectoryPath(): string
    {
        if ($this->projectDirectoryPath === null) {
            return dirname(__DIR__);
        }

        return $this->projectDirectoryPath;
    }

    public function getCacheDirectoryPath(): string
    {
        return $this->getProjectDirectoryPath() . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'cache';
    }

    public function getCachedContainerFilePath(): string
    {
        return $this->getCacheDirectoryPath() . DIRECTORY_SEPARATOR . $this->cachedContainerClassName . '.php';
    }

    public function production(): static
    {
        $this->mode = CoreMode::PRODUCTION;
        return $this;
    }

    public function development(): static
    {
        $this->mode = CoreMode::DEVELOPMENT;
        return $this;
    }

    protected function runModules(): void
    {
        $lastModuleRunIndex = 0;

        try {
            for (; $lastModuleRunIndex < count($this->modules); $lastModuleRunIndex++) {
                $this->modules[$lastModuleRunIndex]->run($this->container);
            }
        } catch (\Throwable) {
            try {
                $this->stopModules($lastModuleRunIndex);
            } catch (\RuntimeException $e) {
                throw new \RuntimeException("Error while running module with index '{$lastModuleRunIndex}'. " . $e->getMessage());
            }
        }
    }

    protected function stopModules(?int $lastModuleRunIndex = null): void
    {
        $lastModuleStopIndex = 0;

        try {
            for (; $lastModuleStopIndex < $lastModuleRunIndex ?? count($this->modules); $lastModuleStopIndex++) {
                $this->modules[$lastModuleStopIndex]->stop($this->container);
            }
        } catch (\Throwable) {
            throw new \RuntimeException("Error while stopping module with index '{$lastModuleStopIndex}'");
        }
    }

    protected function loadCoreConfiguration(ContainerBuilder $container): void
    {
        $container->setParameter('core.cache_dir', $this->getCacheDirectoryPath());
    }

    protected function loadModulesConfiguration(ContainerBuilder $container): void
    {
        foreach ($this->modules as $module) {
            $module->getConfigurator()?->configure($container);
        }
    }

    protected function preBuildCore(ContainerBuilder $container): void
    {
    }

    protected function preBuildModules(ContainerBuilder $container): void
    {
        foreach ($this->modules as $module) {
            if ($module->getPreBuildModifier() !== null) {
                $container->addPreBuildModifier($module->getPreBuildModifier());
            }
        }
    }

    /**
     * @throws \ReflectionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function buildContainer(): void
    {
        $container = new ContainerBuilder(
            new ParamContainerBuilder(),
            new ContainerDumper(),
            true
        );

        $this->loadCoreConfiguration($container);
        $this->loadModulesConfiguration($container);

        $this->preBuildCore($container);
        $this->preBuildModules($container);

        $this->container = $container->build($this->mode === CoreMode::PRODUCTION ? $this->getCachedContainerFilePath() : null);
    }

    /**
     * @throws \ReflectionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function loadContainer(): void
    {
        if (file_exists($this->getCachedContainerFilePath())) {
            require_once $this->getCachedContainerFilePath();
            $this->container = (new \ReflectionClass($this->cachedContainerClassName))->newInstance();
            return;
        }

        $this->buildContainer();
    }
}