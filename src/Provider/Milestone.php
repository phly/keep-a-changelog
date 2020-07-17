<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2020 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Provider;

class Milestone
{
    /** @var string */
    private $description;

    /** @var int */
    private $id;

    /** @var string */
    private $title;

    public function __construct(int $id, string $title, string $description = '')
    {
        $this->id          = $id;
        $this->title       = $title;
        $this->description = $description;
    }

    public function id() : int
    {
        return $this->id;
    }

    public function title() : string
    {
        return $this->title;
    }

    public function description() : string
    {
        return $this->description;
    }
}
