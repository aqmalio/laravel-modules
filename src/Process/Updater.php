<?php

namespace Nwidart\Modules\Process;

use Nwidart\Modules\Module;

class Updater extends Runner
{
    /**
     * Update the dependencies for the specified module by given the module name.
     *
     * @param string $module
     */
    public function update($module)
    {
        $module = $this->module->findOrFail($module);

        chdir(base_path());

        $this->installRequires($module);
        dd('safe');
        $this->installDevRequires($module);
        $this->copyScriptsToMainComposerJson($module);
    }

    /**
     * @param Module $module
     */
    private function installRequires(Module $module)
    {
        $packages = $module->getComposerAttr('require', []);

        $package = '';
        foreach ($packages as $name => $version) {
            $package .= "\"{$name}:{$version}\" ";
        }
        $this->run("composer require {$package}");
    }

    /**
     * @param Module $module
     */
    private function installDevRequires(Module $module)
    {
        $devPackages = $module->getComposerAttr('require-dev', []);

        $package = '';
        foreach ($devPackages as $name => $version) {
            $package .= "\"{$name}:{$version}\" ";
        }
        $this->run("composer require --dev {$package}");
    }

    /**
     * @param Module $module
     */
    private function copyScriptsToMainComposerJson(Module $module)
    {
        $scripts = $module->getComposerAttr('scripts', []);

        $composer = json_decode(file_get_contents(base_path('composer.json')), true);

        foreach ($scripts as $key => $script) {
            if (array_key_exists($key, $composer['scripts'])) {
                $composer['scripts'][$key] = array_unique(array_merge($composer['scripts'][$key], $script));
                continue;
            }
            $composer['scripts'] = array_merge($composer['scripts'], [$key => $script]);
        }

        file_put_contents(base_path('composer.json'), json_encode($composer, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }
}
