<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\ConfigCommand;

class EditLocalConfigListener extends AbstractEditConfigListener
{
    public function configEditRequested(EditConfigEvent $event) : bool
    {
        return $event->editLocal();
    }

    public function getConfigFile() : string
    {
        return sprintf('%s/.keep-a-changelog.ini', getcwd());
    }
}
