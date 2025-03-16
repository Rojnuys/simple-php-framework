<?php

use App\Core\DependencyInjection\Container\ContainerBuilder;

?>
<?= '<?php' ?>
<?php
    function getMethodName(string $id): string
    {
        return str_replace('\\', '', $id);
    }

    function getClassName(string $class): string
    {
        return '\\' . $class;
    }

    function getArguments(mixed $argument, ContainerBuilder $container): void
    {
        if (is_array($argument)) {
            echo '[';
            foreach ($argument as $key => $value) {
                if (is_string($key)) {
                    echo '\'' . $key . '\' => ';
                } else {
                    echo $key . ' => ';
                }
                getArguments($value, $container);
                echo ',';
            }
            echo ']';
            return;
        }

        if (!is_string($argument)) {
            echo var_export($argument, true);
            return;
        }

        if ($argument === '') {
            echo '\'\'';
            return;
        }

        if (preg_match('/^%([^%]+)%$/', $argument, $matches)) {
            echo var_export($container->getParameter($matches[1]), true);
            return;
        }

        $argument = preg_replace_callback('/%%|%([^%]+)%/', function ($matches) use ($argument, $container) {
            if ($matches[0] === '%%') {
                return '%';
            }

            return $container->getParameter($matches[1]);
        }, $argument);

        if (str_starts_with($argument, '@@') || str_starts_with($argument, '$$')) {
            echo '\'' . substr($argument, 1) . '\'';
            return;
        }

        if (str_starts_with($argument, '@')) {
            echo sprintf('$this->get(\'%s\') ', substr($argument, 1));
            return;
        }

        if (str_starts_with($argument, '$')) {
            echo '[' . array_reduce(
                    $container->getServiceIdsByTag(substr($argument, 1)),
                    fn($acc, $id) => $acc . sprintf('$this->get(\'%s\'), ', $id),
                    ''
                ) . ']';
            return;
        }

        echo '\'' . $argument . '\'';
    }
?>

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use App\Core\DependencyInjection\Container\Container;
use App\Core\DependencyInjection\Container\ParamContainer;

class CachedContainer extends Container
{
    public function __construct()
    {
        $this->parameterContainer = new ParamContainer(<?= var_export($container->getParameters(), true) ?>);

        $this->aliases = [
            <?php foreach ($container->getAliases() as $alias => $id): ?>
            '<?= $alias ?>' => '<?= $id ?>',
            <?php endforeach; ?>
        ];

        $this->methodMap = [
            <?php foreach ($container->getServiceDefinitions() as $id => $serviceDefinition): ?>
            '<?= $id ?>' => '<?= getMethodName($id) ?>',
            <?php endforeach; ?>
        ];
    }
<?php foreach ($container->getServiceDefinitions() as $id => $serviceDefinition): ?>

    protected function <?= str_replace('\\', '', $id) ?>(): <?= getClassName($serviceDefinition->getClass()) ?>
    {
    <?php if ($serviceDefinition->hasFactory()): ?>
        <?php
            $factory = $serviceDefinition->getFactory();

            if ($factory[0] === null) {
                $factory[0] = $serviceDefinition->getClass();
            }

            if (str_starts_with($factory[0], '@')) {
                $factoryService = '$this->get(\'' . substr($factory[0], 1) . '\')';
            }
        ?>

        $service = call_user_func_array([<?= str_starts_with($factory[0], '@') ? $factoryService : var_export($factory[0], true) ?>, <?= var_export($factory[1], true) ?>], <?php getArguments($serviceDefinition->getArguments(), $container)?>);
    <?php else: ?>
        $service = new <?= getClassName($serviceDefinition->getClass()) ?>(...<?php getArguments($serviceDefinition->getArguments(), $container) ?>);
    <?php endif; ?>
    <?php foreach ($serviceDefinition->getMethodCalls() as $methodCall): ?>
        $service->{'<?= $methodCall[0] ?>'}(...<?php getArguments($methodCall[1], $container); ?>);
    <?php endforeach; ?>
    <?php if ($serviceDefinition->isSingleton()): ?>$this->setService('<?= $id ?>', $service);<?php endif; ?>
        return $service;
    }
<?php endforeach; ?>
}