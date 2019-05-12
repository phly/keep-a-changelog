<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Release;

use Phly\KeepAChangelog\IOTrait;
use Psr\EventDispatcher\StoppableEventInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

use function sprintf;

class PrepareChangelogEvent implements StoppableEventInterface
{
    use IOTrait;

    /** @var null|string */
    private $changelogFile;

    /** @var bool */
    private $changelogFileIsUnreadable = false;

    /** @var null|string */
    private $formattedChangelog;

    /** @var bool */
    private $parsingFailed = false;

    /** @var null|string */
    private $rawChangelog;

    /** @var string */
    private $version;

    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        string $version
    ) {
        $this->input   = $input;
        $this->output  = $output;
        $this->version = $version;
    }

    public function isPropagationStopped() : bool
    {
        if ($this->changelogFileIsUnreadable) {
            return true;
        }

        if ($this->parsingFailed) {
            return true;
        }

        if ($this->formattedChangelog) {
            return true;
        }

        return false;
    }

    public function changelogIsReady() : bool
    {
        return ! $this->parsingFailed
            && ! empty($this->formattedChangelog);
    }

    public function changelogFile() : ?string
    {
        return $this->changelogFile;
    }

    public function version() : string
    {
        return $this->version;
    }

    public function changelogFileIsUnreadable(string $changelogFile) : void
    {
        $this->changelogFileIsUnreadable = true;
        $this->output()->writeln(sprintf(
            '<error>Changelog file "%s" is unreadable.</error>',
            $changelogFile
        ));
    }

    public function setChangelogFile(string $changelogFile) : void
    {
        $this->changelogFile = $changelogFile;
    }

    public function errorParsingChangelog(Throwable $e) : void
    {
        $this->parsingFailed = true;
        $this->output()->writeln(sprintf(
            '<error>An error occurred parsing the changelog file "%s" for the release "%s":</error>',
            $this->changelogFile ?: '{none}',
            $this->version
        ));
        $this->output()->writeln($e->getMessage());
    }

    public function setRawChangelog(string $changelog) : void
    {
        if ($this->formattedChangelog) {
            return;
        }
        $this->rawChangelog = $changelog;
    }

    public function setFormattedChangelog(string $changelog) : void
    {
        $this->formattedChangelog = $changelog;
    }

    public function changelog() : ?string
    {
        if ($this->formattedChangelog) {
            return $this->formattedChangelog;
        }

        if ($this->rawChangelog) {
            return $this->rawChangelog;
        }

        return null;
    }
}
