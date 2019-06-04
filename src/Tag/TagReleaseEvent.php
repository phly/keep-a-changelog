<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Tag;

use Phly\KeepAChangelog\Common\AbstractEvent;
use Phly\KeepAChangelog\Common\ChangelogAwareEventInterface;
use Phly\KeepAChangelog\Common\ChangelogProviderTrait;
use Phly\KeepAChangelog\Common\VersionValidationTrait;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TagReleaseEvent extends AbstractEvent implements ChangelogAwareEventInterface
{
    use ChangelogProviderTrait;
    use VersionValidationTrait;

    /** @var string */
    private $tagName;

    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        EventDispatcherInterface $dispatcher,
        string $version,
        string $tagName
    ) {
        $this->input   = $input;
        $this->output  = $output;
        $this->dispatcher  = $dispatcher;
        $this->version = $version;
        $this->tagName = $tagName;
    }

    public function isPropagationStopped() : bool
    {
        return $this->failed;
    }

    public function package() : ?string
    {
        return $this->config()->package();
    }

    public function tagName() : string
    {
        return $this->tagName;
    }

    public function taggingComplete() : void
    {
        $this->output->writeln(sprintf(
            '<info>Created tag "%s" for package "%s" using the following notes:</info>',
            $this->tagName,
            $this->package()
        ));

        $this->output->write($this->changelog());
    }

    public function taggingFailed() : void
    {
        $this->failed = true;
        $this->output->writeln('<error>Error creating tag!</error>');
        $this->output->writeln('Check the output logs for details');
    }
}
