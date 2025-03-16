<?php

namespace App\Core\Config;

use App\Core\Config\Enums\ConfigTypeKeys;
use App\Core\Config\Enums\ServiceConfigTypeKeys;
use App\Core\Config\Exceptions\ConfigStructureException;
use App\Core\Config\Interfaces\IConfigStructureValidator;

class ConfigStructureValidator implements IConfigStructureValidator
{
    /**
     * @throws ConfigStructureException
     */
    public function validate(array $configs): void
    {
        $this->validateConfigTypes($configs);
        $this->validateParameters($configs[ConfigTypeKeys::PARAMETERS] ?? []);
        $this->validateServices($configs[ConfigTypeKeys::SERVICES] ?? []);
    }

    /**
     * @throws ConfigStructureException
     */
    protected function validateConfigTypes(array $configs): void
    {
        if (isset($configs[ConfigTypeKeys::PARAMETERS]) && !is_array($configs[ConfigTypeKeys::PARAMETERS])) {
            throw new ConfigStructureException('Config type \'' . ucfirst(ConfigTypeKeys::PARAMETERS) . '\' must be an array');
        }

        if (isset($configs[ConfigTypeKeys::SERVICES]) && !is_array($configs[ConfigTypeKeys::SERVICES])) {
            throw new ConfigStructureException('Config type \'' . ucfirst(ConfigTypeKeys::SERVICES) . '\' must be an array');
        }
    }

    /**
     * @throws ConfigStructureException
     */
    protected function validateParameters(mixed $parameters): void
    {
        if (is_array($parameters)) {
            foreach ($parameters as $key => $value) {
                if (is_string($key)) {
                    $this->validateParameterKey($key);
                }
                $this->validateParameters($value);
            }
            return;
        }

        if (is_string($parameters)) {
            $this->validateParameterStringValue($parameters);
            return;
        }

        if (is_numeric($parameters) || is_bool($parameters) || is_null($parameters)) {
            return;
        }

        $parameters = var_export($parameters, true);
        throw new ConfigStructureException(
            "Parameter '{$parameters}' value must have the following types: array, string, numeric, bool, or null"
        );
    }

    /**
     * @throws ConfigStructureException
     */
    protected function validateParameterKey(string $key): void
    {
        if (!preg_match('/^[\w.]+$/', $key)) {
            throw new ConfigStructureException(
                "Parameter '{$key}' name is invalid. Must not be empty and contains only letters, numbers and (-, _, .)"
            );
        }
    }

    /**
     * @throws ConfigStructureException
     */
    protected function validateParameterStringValue(string $value): void
    {
        if (substr_count($value, '%') % 2 !== 0) {
            throw new ConfigStructureException("Parameter '{$value}' value has unclosed symbol '%'");
        }

        preg_match_all('/%%|%([^%]+)%/', $value, $matches);
        foreach ($matches[1] as $match) {
            if ($match === '') {
                continue;
            }
            $this->validateParameterKey($match);
        }
    }

    /**
     * @throws ConfigStructureException
     */
    protected function validateName(string $name): void
    {
        if (preg_match('/^[a-zA-Z_]+[\w]*$/', $name) !== 1) {
            throw new ConfigStructureException("'{$name}' is not a valid name. It must contain letters, numbers, and underscores but must not start with a number");
        }
    }

    protected function validateArrayKeys(array $arr, callable $clb): void
    {
        foreach ($arr as $key => $value) {
            $clb($key);
        }
    }

    /**
     * @throws ConfigStructureException
     */
    protected function validateServices(array $services): void
    {
        foreach ($services as $id => $config) {
            $this->validateServiceId($id);

            if (is_string($config) && ($id === '_default' || $id === '_instanceof')) {
                throw new ConfigStructureException("Directive '{$id}' must be an array");
            }

            if ($id === '_default') {
                $this->validateServiceDefaultConfig($config);
            } elseif ($id === '_instanceof') {
                $this->validateServiceInstanceOfConfig($config);
            } elseif (is_array($config) || is_string($config)) {
                $this->validateServiceConfig($id, $config);
            } else {
                throw new ConfigStructureException("Service '{$id}' config must be an array or string");
            }
        }
    }

    /**
     * @throws ConfigStructureException
     */
    protected function validateServiceId(mixed $id): void
    {
        if (!is_string($id) || preg_match('/^[a-zA-Z_\\\\]+[\w\\\\]*$/', $id) !== 1) {
            $id = var_export($id, true);
            throw new ConfigStructureException(
                "Service {$id} id is invalid. Must not be an empty string and contain only letters, numbers and (\\, -. _, .)"
            );
        }
    }

    /**
     * @throws ConfigStructureException
     */
    protected function validateServiceDefaultConfig(array $config): void
    {
        try {
            $this->validateServiceDirectiveTypeKeys($config);
            $this->validateSharedServiceConfig($config);
        } catch (ConfigStructureException $e) {
            throw new ConfigStructureException("Service '_default'. " . $e->getMessage());
        }

    }

    /**
     * @throws ConfigStructureException
     */
    protected function validateServiceInstanceOfConfig(array $config): void
    {
        foreach ($config as $key => $value) {
            try {
                $this->validateServiceId($key);
                $this->validateServiceDirectiveTypeKeys($value);
                $this->validateSharedServiceConfig($config);
            } catch (ConfigStructureException $e) {
                throw new ConfigStructureException("Service '_instanceof', '{$key}'. " . $e->getMessage());
            }
        }
    }

    /**
     * @throws ConfigStructureException
     */
    protected function validateServiceConfig(string $id, string|array $config): void
    {
        if (is_string($config)) {
            $this->validateServiceId(str_starts_with($config, '@') ? substr($config, 1) : $config);
            return;
        }

        try {
            $this->validateServiceConfigTypeKeys($config);
            $this->validateSharedServiceConfig($config);
        } catch (ConfigStructureException $e) {
            throw new ConfigStructureException("Service '{$id}'. " . $e->getMessage());
        }
    }

    /**
     * @throws ConfigStructureException
     */
    protected function validateSharedServiceConfig(array $config): void
    {
        $this->validateServiceConfigValueTypes($config);

        if (isset($config[ServiceConfigTypeKeys::ALIAS])) {
            $this->validateServiceId($config[ServiceConfigTypeKeys::ALIAS]);
        }

        $this->validateServiceConfigFactory($config);
        try {
            $this->validateArrayKeys($config[ServiceConfigTypeKeys::ARGS] ?? [], fn($key) => $this->validateName($key));
        } catch (ConfigStructureException $e) {
            throw new ConfigStructureException('Config type \'' . ServiceConfigTypeKeys::ARGS . '\'. ' .  $e->getMessage());
        }
        $this->validateServiceConfigArgument($config[ServiceConfigTypeKeys::ARGS] ?? []);
        $this->validateServiceConfigMethodCalls($config);
    }



    /**
     * @throws ConfigStructureException
     */
    protected function validateAvailableServiceConfigTypeKeys(array $config): void
    {
        $availableKeys = ServiceConfigTypeKeys::getAllKeys();
        foreach ($config as $key => $value) {
            if (!in_array($key, $availableKeys)) {
                throw new ConfigStructureException("Config type '{$key}' is not a valid type");
            }
        }
    }

    /**
     * @throws ConfigStructureException
     */
    protected function validateServiceDirectiveTypeKeys(array $config): void
    {
        $this->validateAvailableServiceConfigTypeKeys($config);

        foreach (ServiceConfigTypeKeys::unavailableTypeKeysForDirective() as $key) {
            if (isset($config[$key])) {
                throw new ConfigStructureException(
                    "Config type '{$key}' is unavailable type for such a service type"
                );
            }
        }
    }

    /**
     * @throws ConfigStructureException
     */
    protected function validateServiceConfigTypeKeys(array $config): void
    {
        $this->validateAvailableServiceConfigTypeKeys($config);

        $unavailableTypeKeys = match (true) {
            isset($config[ServiceConfigTypeKeys::RESOURCE]) => ServiceConfigTypeKeys::unavailableTypeKeysForLoader(),
            isset($config[ServiceConfigTypeKeys::ALIAS]) => ServiceConfigTypeKeys::unavailableTypeKeysForAlias(),
            default => ServiceConfigTypeKeys::unavailableTypeKeysForService()
        };

        foreach ($unavailableTypeKeys as $key) {
            if (isset($config[$key])) {
                throw new ConfigStructureException(
                    "Config type '{$key}' is unavailable type for such a service type"
                );
            }
        }
    }

    /**
     * @throws ConfigStructureException
     */
    protected function validateServiceConfigValueTypes(array $config): void
    {
        foreach (ServiceConfigTypeKeys::getStringKeys() as $key) {
            if (isset($config[$key]) && !is_string($config[$key])) {
                throw new ConfigStructureException("Config type '{$key}' must be a string");
            }
        }

        foreach (ServiceConfigTypeKeys::getArrayKeys() as $key) {
            if (isset($config[$key]) && !is_array($config[$key])) {
                throw new ConfigStructureException("Config type '{$key}' must be an array");
            }
        }

        foreach (ServiceConfigTypeKeys::getBoolKeys() as $key) {
            if (isset($config[$key]) && !is_bool($config[$key])) {
                throw new ConfigStructureException("Config type '{$key}' must be a boolean");
            }
        }
    }

    /**
     * @throws ConfigStructureException
     */
    protected function validateServiceConfigFactory(array $config): void
    {
        if (isset($config[ServiceConfigTypeKeys::FACTORY])) {
            $factory = $config[ServiceConfigTypeKeys::FACTORY];

            if (
                is_array($factory) && count($factory) === 2 &&
                (is_string($factory[0]) || is_null($factory[0])) &&
                is_string($factory[1])
            ) {
                try {
                    $factory[0] ?? $this->validateName($factory[0]);
                    $this->validateName($factory[1]);
                } catch (ConfigStructureException $e) {
                    throw new ConfigStructureException('Config type ' . ServiceConfigTypeKeys::FACTORY . ' ' . $e->getMessage());
                }

                return;
            }

            throw new ConfigStructureException(
                'Config type ' . ServiceConfigTypeKeys::FACTORY  . ' must be an array with null and a string or two string elements'
            );
        }
    }

    /**
     * @throws ConfigStructureException
     */
    protected function validateServiceConfigArgument(mixed $argument): void
    {
        if (is_array($argument)) {
            foreach ($argument as $value) {
                $this->validateServiceConfigArgument($value);
            }
            return;
        }

        if (is_string($argument)) {
            $this->validateServiceConfigStringArgument($argument);
            return;
        }

        if (is_numeric($argument) || is_bool($argument) || is_null($argument)) {
            return;
        }

        $argument = var_export($argument, true);
        throw new ConfigStructureException(
            "Config argument '{$argument}' must be one of the following types: (array, string, int, float, bool, null)"
        );
    }

    /**
     * @throws ConfigStructureException
     */
    protected function validateServiceConfigStringArgument(string $argument): void
    {
        try {
            $this->validateParameterStringValue($argument);
        } catch (ConfigStructureException $e) {
            throw new ConfigStructureException("Config argument '{$argument}'. " . $e->getMessage());
        }

        if ($argument === '@' || $argument === '$') {
            throw new ConfigStructureException(
                "Config argument can't be '@' or '$' because it's used as a mark for a service or tag. Use '@@' or '$$' instead if you want to get a string '@' or '$'"
            );
        }

        if (str_starts_with($argument, '@') && !str_starts_with($argument, '@@')) {
            try {
                $this->validateServiceId(substr($argument, 1));
            } catch (ConfigStructureException $e) {
                throw new ConfigStructureException("Config argument '{$argument}'. " . $e->getMessage());
            }
        }
    }

    /**
     * @throws ConfigStructureException
     */
    protected function validateServiceConfigMethodCalls(array $config): void
    {
        if (isset($config[ServiceConfigTypeKeys::CALLS])) {
            foreach ($config[ServiceConfigTypeKeys::CALLS] as $method => $arguments) {
                try {
                    $this->validateName($method);
                    $this->validateArrayKeys($arguments, fn($key) => $this->validateName($key));
                    $this->validateServiceConfigArgument($arguments);
                } catch (ConfigStructureException $e) {
                    throw new ConfigStructureException('Config type ' . ServiceConfigTypeKeys::CALLS . ", method '{$method}'. " . $e->getMessage());
                }
            }
        }
    }
}