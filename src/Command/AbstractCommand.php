<?php

namespace Oscmarb\MigrationsMultipleDatabase\Command;

use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\Command\DoctrineCommand;
use Oscmarb\MigrationsMultipleDatabase\Bundle\DependencyInjection\DependencyFactoryLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand extends Command
{
    public const SUCCESS = 0;

    /**
     * @var DependencyFactoryLoader
     */
    private $configuration;

    public function __construct(DependencyFactoryLoader $configuration)
    {
        $this->configuration = $configuration;

        parent::__construct();
    }

    /**
     * @return class-string<DoctrineCommand>
     */
    abstract protected function commandClass(): string;

    protected function configure(): void
    {
        $this->setDefinition($this->createDoctrineCommand()->getDefinition());

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $input->getOption('em');

        if (false === is_string($em) && null !== $em) {
            throw new \RuntimeException('invalid em option value');
        }

        $newInput = $this->getSanitizedNewInput($input);

        foreach ($this->getDependencyFactories($em) as $dependencyFactory) {
            $doctrineCommand = $this->createDoctrineCommand($dependencyFactory);
            $doctrineCommand->run($newInput, $output);
        }

        return self::SUCCESS;
    }

    private function getSanitizedNewInput(InputInterface $input): ArrayInput
    {
        $definition = $this->createDoctrineCommand()->getDefinition();
        $rawNewInput = [];

        foreach ($input->getArguments() as $argumentName => $value) {
            if ('command' === $argumentName) {
                continue;
            }

            try {
                $inputArgument = $definition->getArgument($argumentName);
            } catch (\Throwable $exception) {
                continue;
            }

            if ($inputArgument->getDefault() === $value) {
                continue;
            }

            $rawNewInput[$argumentName] = $value;
        }

        foreach ($input->getOptions() as $optionName => $optionValue) {
            if ('em' === $optionName) {
                continue;
            }

            try {
                $inputOption = $definition->getOption($optionName);
            } catch (\Throwable $exception) {
                continue;
            }

            if ($inputOption->getDefault() === $optionValue) {
                continue;
            }

            $rawNewInput["--$optionName"] = $optionValue;
        }

        $newInput = new ArrayInput($rawNewInput);
        $newInput->setInteractive($input->isInteractive());

        return $newInput;
    }

    /**
     * @return DependencyFactory[]
     *
     * @throws \RuntimeException
     */
    private function getDependencyFactories(string $entityManager = null): array
    {
        $dependencyFactories = [];

        if (null === $entityManager || '' === $entityManager) {
            $dependencyFactories = $this->configuration->getDependencyFactories();
        } elseif (null !== $this->configuration->getConfigurationByEntityManagerName($entityManager)) {
            $dependencyFactories = [$this->configuration->getConfigurationByEntityManagerName($entityManager)];
        }

        if (0 === count($dependencyFactories)) {
            throw new \RuntimeException('No entity manager found');
        }

        return $dependencyFactories;
    }

    private function createDoctrineCommand(DependencyFactory $dependencyFactory = null): DoctrineCommand
    {
        return new ($this->commandClass())($dependencyFactory);
    }
}
