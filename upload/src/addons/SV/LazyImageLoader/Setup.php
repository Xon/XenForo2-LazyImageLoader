<?php

namespace SV\LazyImageLoader;

use SV\StandardLib\InstallerHelper;
use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;

class Setup extends AbstractSetup
{
    use InstallerHelper;
    use StepRunnerInstallTrait;
    use StepRunnerUninstallTrait;
    use StepRunnerUpgradeTrait;

    public function upgrade2050000Step1()
    {
        $this->renameOption('SV_LazyLoader_EnableDefault', 'svLazyLoader_EnableDefault');
        $this->renameOption('sv_forceLazySpoilerTag', 'svLazyLoader_ForceLazySpoilerTag');
        $this->renameOption('lazyLoaderPlaceholderUrl', 'svLazyLoader_PlaceholderUrl');
        $this->renameOption('svLazyLoaderBlankSvg', 'svLazyLoader_BlankSvg');
        $this->renameOption('svLazyLoadIcons', 'svLazyLoader_Icons');
        $this->renameOption('svNativeLazyLoading', 'svLazyLoader_NativeMode');
    }
}