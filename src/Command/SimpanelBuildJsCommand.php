<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SimpanelBuildJsCommand extends Command
{
    protected static $defaultName = 'simpanel:buildJs';

    protected function configure()
    {
        $this
          ->setDescription('Builds the javascript assests needed');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $command = shell_exec('yarn build');

        if (strpos($command, 'ERROR') == false) {
            $io->success($command);
        } else {
            $io->error($command);
        }

    }
}
