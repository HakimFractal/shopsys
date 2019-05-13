<?php

declare(strict_types=1);

namespace Shopsys\Releaser\FileManipulator;

use Nette\Utils\Strings;
use PharIo\Version\Version;
use Symfony\Component\Finder\SplFileInfo;

final class FrameworkVersionFileManipulator
{
    /**
     * @var string
     */
    public const FRAMEWORK_VERSION_FILE_PATH = '/packages/framework/src/ShopsysFrameworkBundle.php';

    /**
     * @var string
     */
    private const FRAMEWORK_VERSION_PATTEN = "/public const VERSION = '(.+)';/";

    /**
     * @param \Symfony\Component\Finder\SplFileInfo $splFileInfo
     * @param \PharIo\Version\Version $version
     * @return string
     */
    public function updateFrameworkKernelVersion(SplFileInfo $splFileInfo, Version $version): string
    {
        return Strings::replace(
            $splFileInfo->getContents(),
            self::FRAMEWORK_VERSION_PATTEN,
            function ($match) use ($version) {
                return str_replace($match[1], $this->getVersionWithoutPrefix($version), $match[0]);
            }
        );
    }

    /**
     * @param \PharIo\Version\Version $version
     * @return string
     */
    private function getVersionWithoutPrefix(Version $version): string
    {
        return ltrim($version->getVersionString(), 'v');
    }
}
