<?php

/**
 * Copyright (c) 2020 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MoonwalkingBits\Composer\Plugin\WordPress;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Util\Filesystem;

class Autoloader implements PluginInterface, EventSubscriberInterface
{
    use MergesAssociativeArrayTrait;

    private Composer $composer;
    private IOInterface $io;

    public static function getSubscribedEvents(): array
    {
        return [
            'post-autoload-dump' => 'generateAutoloadFile'
        ];
    }

    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function deactivate(Composer $composer, IOInterface $io): void
    {
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function uninstall(Composer $composer, IOInterface $io): void
    {
    }

    public function generateAutoloadFile(Event $event): void
    {
        $filesystem = new Filesystem();
        $vendorDirectory = $event->getComposer()->getConfig()->get('vendor-dir');

        $filesystem->ensureDirectoryExists($vendorDirectory);

        $autoloadFileContent = <<<EOF
            <?php

            require_once 'autoload.php';

            \$autoloader = new Moonwalking_Bits\\Autoloader();

            spl_autoload_register( array( \$autoloader, 'load_class' ) );


            EOF;

        $autoloadingRules = $this->getAutoloadingRules($event->isDevMode());

        foreach ($autoloadingRules as $namespace => $directories) {
            $autoloadFileContent .= implode(
                '',
                array_map(function (string $directory) use ($namespace) {
                    return '$autoloader->add_namespace_mapping(' .
                        var_export($namespace, true) . ', ' .
                        'dirname(__DIR__) . ' . var_export('../' . ltrim($directory, '/'), true) .
                        ");\n";
                }, $directories)
            );
        }

        $autoloadFileContent .= <<<EOF

            return \$autoloader;

            EOF;

        $this->filePutContentsIfModified("{$vendorDirectory}/wordpress-autoload.php", $autoloadFileContent);
    }

    private function getAutoloadingRules(bool $isDevMode): array
    {
        $autoloadGenerator = new AutoloadGenerator($this->composer->getEventDispatcher(), $this->io);

        $autoloadingRules = $autoloadGenerator->parseAutoloads(
            $autoloadGenerator->buildPackageMap(
                $this->composer->getInstallationManager(),
                $this->composer->getPackage(),
                $this->composer->getRepositoryManager()->getLocalRepository()->getCanonicalPackages()
            ),
            $this->composer->getPackage(),
            !$isDevMode
        )['wordpress'] ?? [];

        if ($isDevMode) {
            $autoloadingRules = $this->mergeAssociativeArrays(
                $autoloadingRules,
                $this->ensureArrayValues($this->composer->getPackage()->getDevAutoload()['wordpress'] ?? []),
                MergeStrategy::MERGE_INDEXED_ARRAYS
            );
        }

        return $autoloadingRules;
    }

    private function ensureArrayValues(array $rules): array
    {
        array_walk($rules, function (&$directories) {
            $directories = (array)$directories;
        });

        return $rules;
    }

    private function filePutContentsIfModified(string $path, string $content)
    {
        $currentContent = @file_get_contents($path);

        if (!$currentContent || ($currentContent !== $content)) {
            return file_put_contents($path, $content);
        }

        return 0;
    }
}
