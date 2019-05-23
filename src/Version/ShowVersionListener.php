<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Version;

use Phly\KeepAChangelog\Common\ChangelogParser;
use Phly\KeepAChangelog\Exception;

use function file_get_contents;
use function sprintf;

class ShowVersionListener
{
    public function __invoke(ShowVersionEvent $event) : void
    {
        $version    = $event->version();
        $changelogs = file_get_contents($event->changelogFile());
        $parser     = new ChangelogParser();

        try {
            $releaseDate = $parser->findReleaseDateForVersion($changelogs, $version);
            $changelog   = $parser->findChangelogForVersion($changelogs, $version);
        } catch (Exception\ChangelogNotFoundException $e) {
            $event->changelogVersionNotFound();
            return;
        } catch (Exception\ChangelogMissingDateException $e) {
            $event->changelogMissingDate();
            return;
        } catch (Exception\InvalidChangelogFormatException $e) {
            $event->changelogMalformed();
            return;
        }

        $output = $event->output();

        $output->writeln(sprintf(
            '<info>Showing changelog for version %s (released %s):</info>',
            $version,
            $releaseDate
        ));
        $output->writeln('');
        $output->write($changelog);
        $output->writeln('');
    }
}
