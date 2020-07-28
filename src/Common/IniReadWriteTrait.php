<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Common;

use Phly\KeepAChangelog\Exception;

use function array_merge;
use function array_values;
use function implode;
use function is_array;
use function is_bool;
use function is_object;
use function is_readable;
use function is_resource;
use function is_scalar;
use function parse_ini_file;
use function sprintf;

trait IniReadWriteTrait
{
    /**
     * @throws Exception\FileNotReadableException
     * @throws Exception\IniParsingFailedException
     */
    public function readIniFile(string $file): array
    {
        if (! is_readable($file)) {
            throw Exception\FileNotReadableException::forFile($file);
        }

        $data = parse_ini_file($file, $processSections = true);

        if (false === $data) {
            throw Exception\IniParsingFailedException::forFile($file);
        }

        return $data;
    }

    /**
     * @throws Exception\InvalidIniSectionDataException
     */
    public function arrayToIniString(array $data): string
    {
        $aggregator = [];

        foreach ($data as $name => $values) {
            if (! is_array($values)) {
                throw Exception\InvalidIniSectionDataException::forSection($name);
            }

            $sectionAggregator = [
                sprintf('[%s]', $name),
            ];

            foreach ($values as $key => $value) {
                $sectionAggregator = array_merge(
                    $sectionAggregator,
                    $this->processIniPair($key, $value)
                );
            }

            $aggregator = array_merge($aggregator, $sectionAggregator, ['']);
        }

        return implode("\n", $aggregator);
    }

    /**
     * @param mixed $value
     */
    private function processIniPair(string $key, $value): array
    {
        $this->validateValue($value, $key);

        if (! is_array($value)) {
            return [sprintf('%s = %s', $key, $this->normalizeIniValue($value))];
        }

        if ($this->isAssociativeArray($value)) {
            return $this->processIniHashMap($value, $key);
        }

        return $this->processIniList($value, $key);
    }

    /**
     * @param mixed $value
     */
    private function normalizeIniValue($value): string
    {
        if (null === $value) {
            return 'null';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }
    }

    private function processIniHashMap(array $map, string $name): array
    {
        $aggregate = [];
        foreach ($map as $key => $value) {
            $aggregate = array_merge($aggregate, $this->processIniPair(
                sprintf('%s[%s]', $name, $key),
                $value
            ));
        }
        return $aggregate;
    }

    private function processIniList(array $list, string $name): array
    {
        $name      = sprintf('%s[]', $name);
        $aggregate = [];
        foreach ($list as $value) {
            $aggregate = array_merge($aggregate, $this->processIniPair($name, $value));
        }
        return $aggregate;
    }

    /**
     * @throws Exception\InvalidIniValueException
     * @param mixed $value
     */
    private function validateValue($value, string $name): void
    {
        if (is_resource($value) || is_object($value)) {
            throw Exception\InvalidIniValueException::forValue($value, $name);
        }
    }

    /**
     * @param mixed $value
     */
    private function isAssociativeArray($value): bool
    {
        if (! is_array($value) || empty($value)) {
            return false;
        }

        return array_values($value) !== $value;
    }
}
