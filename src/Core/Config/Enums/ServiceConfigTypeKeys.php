<?php

namespace App\Core\Config\Enums;

enum ServiceConfigTypeKeys
{
    public const string CLASSNAME = 'class';
    public const string ALIAS = 'alias';
    public const string NAMESPACE = 'namespace';
    public const string RESOURCE = 'resource';
    public const string EXCLUDE = 'exclude';
    public const string ARGS = 'arguments';
    public const string TAGS = 'tags';
    public const string CALLS = 'calls';
    public const string FACTORY = 'factory';
    public const string SINGLETON = 'singleton';
    public const string AUTO_INJECTING = 'auto_injecting';
    public const string AUTO_TAGGING = 'auto_tagging';

    public static function getAllKeys(): array
    {
        return [
            ServiceConfigTypeKeys::CLASSNAME,
            ServiceConfigTypeKeys::ALIAS,
            ServiceConfigTypeKeys::NAMESPACE,
            ServiceConfigTypeKeys::RESOURCE,
            ServiceConfigTypeKeys::EXCLUDE,
            ServiceConfigTypeKeys::ARGS,
            ServiceConfigTypeKeys::CALLS,
            ServiceConfigTypeKeys::FACTORY,
            ServiceConfigTypeKeys::TAGS,
            ServiceConfigTypeKeys::SINGLETON,
            ServiceConfigTypeKeys::AUTO_INJECTING,
            ServiceConfigTypeKeys::AUTO_TAGGING,
        ];
    }

    public static function unavailableTypeKeysForService(): array
    {
        return [
            ServiceConfigTypeKeys::ALIAS,
            ServiceConfigTypeKeys::NAMESPACE,
            ServiceConfigTypeKeys::RESOURCE,
            ServiceConfigTypeKeys::EXCLUDE,
        ];
    }

    public static function unavailableTypeKeysForAlias(): array
    {
        return [
            ServiceConfigTypeKeys::CLASSNAME,
            ServiceConfigTypeKeys::NAMESPACE,
            ServiceConfigTypeKeys::RESOURCE,
            ServiceConfigTypeKeys::EXCLUDE,
            ServiceConfigTypeKeys::ARGS,
            ServiceConfigTypeKeys::CALLS,
            ServiceConfigTypeKeys::FACTORY,
            ServiceConfigTypeKeys::TAGS,
            ServiceConfigTypeKeys::SINGLETON,
            ServiceConfigTypeKeys::AUTO_INJECTING,
            ServiceConfigTypeKeys::AUTO_TAGGING,
        ];
    }

    public static function unavailableTypeKeysForLoader(): array
    {
        return [
            ServiceConfigTypeKeys::CLASSNAME,
            ServiceConfigTypeKeys::ALIAS,
        ];
    }

    public static function unavailableTypeKeysForDirective(): array
    {
        return [
            ServiceConfigTypeKeys::CLASSNAME,
            ServiceConfigTypeKeys::NAMESPACE,
            ServiceConfigTypeKeys::RESOURCE,
            ServiceConfigTypeKeys::EXCLUDE,
            ServiceConfigTypeKeys::ALIAS,
        ];
    }

    public static function getStringKeys(): array
    {
        return [
            ServiceConfigTypeKeys::CLASSNAME,
            ServiceConfigTypeKeys::ALIAS,
            ServiceConfigTypeKeys::NAMESPACE,
            ServiceConfigTypeKeys::RESOURCE,
        ];
    }

    public static function getArrayKeys(): array
    {
        return [
            ServiceConfigTypeKeys::EXCLUDE,
            ServiceConfigTypeKeys::ARGS,
            ServiceConfigTypeKeys::CALLS,
            ServiceConfigTypeKeys::TAGS,
            ServiceConfigTypeKeys::FACTORY,
        ];
    }

    public static function getBoolKeys(): array
    {
        return [
            ServiceConfigTypeKeys::AUTO_INJECTING,
            ServiceConfigTypeKeys::AUTO_TAGGING,
            ServiceConfigTypeKeys::SINGLETON,
        ];
    }
}
