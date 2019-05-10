<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\HelperInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class ConfigCommand extends Command
{
    use ConfigFileTrait;

    private const DESCRIPTION = 'Create a configuration file locally or globally.';

    private const HELP = <<< 'EOH'
Create a configuration file. If no --global is provided, the assumption is
a local .keep-a-changelog.ini file in the current directory. If the file 
already exists, you can use --overwrite to replace it.
EOH;

    /**
     * Used for testing, to allow mocking the question helper.
     *
     * @var ?HelperInterface
     */
    private $questionHelper;

    protected function configure() : void
    {
        $this->setDescription(self::DESCRIPTION);
        $this->setHelp(self::HELP);
        $this->addOption(
            'global',
            'g',
            InputOption::VALUE_NONE,
            'Save the config file globally'
        );
        $this->addOption(
            'overwrite',
            'o',
            InputOption::VALUE_NONE,
            'Overwrite the changelog file, if it exists'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $overwrite = $input->getOption('overwrite') ?: false;
        $configFile = $this->getConfigFile($input);

        if (file_exists($configFile) && ! $overwrite) {
            throw Exception\ConfigFileExistsException::forFile($configFile);
        }

        $helper = $this->getQuestionHelper();
        $question = new ChoiceQuestion(
            sprintf('Please select the provider (Default: %s)', Config::PROVIDER_GITHUB),
            Config::PROVIDERS,
            0
        );
        $question->setErrorMessage('Provider %s is invalid.');
        $provider = $helper->ask($input, $output, $question);

        $question = new Question('Please enter the personal token for the provider (Empty to skip): ', '');
        $token = $helper->ask($input, $output, $question);

        $question = new Question('Please enter the custom domain for the provider, if any (Empty to skip): ', '');
        $domain = $helper->ask($input, $output, $question);

        $config = new Config($token, $provider, $domain);

        $this->saveConfigFile($configFile, $config);

        $output->writeln(sprintf('<info>Created config file "%s".</info>', $configFile));

        return 0;
    }

    private function getQuestionHelper() : HelperInterface
    {
        if ($this->questionHelper) {
            return $this->questionHelper;
        }
        return $this->getHelper('question');
    }
}
