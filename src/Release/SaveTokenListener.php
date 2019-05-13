<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Release;

class SaveTokenListener
{
    /**
     * Command for testing if directory exists.
     *
     * For testing purposes only. Signature is:
     *
     * <code>
     * function (string $dirname) : bool
     * </code>
     *
     * @internal
     * @var callable
     */
    public $isDir = 'is_dir';

    /**
     * Command for creating a new directory.
     *
     * For testing purposes only. Signature is:
     *
     * <code>
     * function (string $dirname, int $mode, bool $recursive) : void
     * </code>
     *
     * @internal
     * @var callable
     */
    public $mkdir = 'mkdir';

    /**
     * Command for writing a file with contents.
     *
     * For testing purposes only. Signature is:
     *
     * <code>
     * function (string $filename, string $contents) : void
     * </code>
     *
     * @internal
     * @var callable
     */
    public $filePutContents = 'file_put_contents';

    /**
     * Command for setting file permissions.
     *
     * For testing purposes only. Signature is:
     *
     * <code>
     * function (string $filename, int $mask) : void
     * </code>
     *
     * @internal
     * @var callable
     */
    public $chmod = 'chmod';

    public function __invoke(SaveTokenEvent $event) : void
    {
        $home            = getenv('HOME');
        $tokenFile       = sprintf('%s/.keep-a-changelog/token', $home);
        $isDir           = $this->isDir;
        $mkdir           = $this->mkdir;
        $filePutContents = $this->filePutContents;
        $chmod           = $this->chmod;

        if (! $isDir(dirname($tokenFile))) {
            $mkdir(dirname($tokenFile), 0700, true);
        }

        $filePutContents($tokenFile, $event->token());
        $chmod($tokenFile, 0600);
    }
}
