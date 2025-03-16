<?php

namespace App\Core\FileSystem;

use App\Core\FileSystem\Interfaces\IClassNameScanner;

class ClassNameScanner implements IClassNameScanner
{
    public function scan(string $namespace, string $resource, array $excludes = []): \Generator
    {
        $namespacePath = $this->getNamespacePath($namespace);

        $iterator = (new Scanner($namespacePath . $resource))->getIterator();
        $iterator = new \CallbackFilterIterator($iterator, function ($fileInfo) use ($namespacePath, $excludes) {
            if ($fileInfo->isDir()) {
                return false;
            }

            if ($fileInfo->getExtension() !== 'php') {
                return false;
            }

            foreach ($excludes as $exclude) {
                $path = $namespacePath . trim($exclude, DIRECTORY_SEPARATOR);
                if (preg_match(Glob::toRegex($path), $fileInfo->getPathname()) === 1) {
                    return false;
                }
            }

            return true;
        });

        foreach ($iterator as $fileInfo) {
            yield from $this->getDeclaredClassesFromFile($fileInfo->getRealPath());
        }
    }

    protected function getNamespacePath(string $namespace): string
    {
        $loader = spl_autoload_functions()[0][0];
        $namespaces = $loader->getPrefixesPsr4();

        if (!isset($namespaces[$namespace])) {
            throw new \InvalidArgumentException("Namespace {$namespace} does not exist");
        }

        return $namespaces[$namespace][0] . DIRECTORY_SEPARATOR;
    }

    protected function getDeclaredClassesFromFile(string $filePath): array
    {
        $classes = [];
        $namespace = '';
        $tokens = token_get_all(file_get_contents($filePath));

        for ($i = 0; $i < count($tokens); $i++) {
            $token = $tokens[$i];

            if (is_array($token) && $token[0] === T_NAMESPACE) {

                $namespace = '';

                for ($j = $i + 1; $j < count($tokens); $j++) {
                    if ($tokens[$j] === ';' || $tokens[$j] === '{') {
                        break;
                    }

                    $namespace .= is_array($tokens[$j]) ? $tokens[$j][1] : $tokens[$j];
                }
            }

            if (is_array($token) && $token[0] === T_CLASS) {
                $isClassDeclaration = false;

                for ($j = $i - 1; $j >= 0; $j--) {
                    if (is_array($tokens[$j]) && in_array($tokens[$j][0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT])) {
                        continue;
                    }

                    if (is_array($tokens[$j]) && in_array($tokens[$j][0], [T_ABSTRACT, T_FINAL, T_READONLY])) {
                        $isClassDeclaration = true;
                        break;
                    }

                    if (in_array($tokens[$j], [';', '{', '}', ']'])) {
                        $isClassDeclaration = true;
                        break;
                    }

                    break;
                }

                if ($isClassDeclaration) {
                    for ($j = $i + 1; $j < count($tokens); $j++) {
                        if ($tokens[$j] === '{') {
                            $className = $namespace ? $namespace . '\\' . $tokens[$i + 2][1] : $tokens[$i + 2][1];
                            $classes[] = trim($className);
                            break;
                        }
                    }
                }
            }
        }

        return $classes;
    }
}