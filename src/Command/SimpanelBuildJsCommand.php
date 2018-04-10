<?php
/**
 * Symfony command to build JS assets.
 *
 * @author William Rich
 * @copyright https://servers4all.documize.com/s/Wm5Pm0A1QQABQ1xw/simpanel/d/WnDQ5EA1QQABQ154/simpanel-license
 */

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
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

        if (false == strpos($command, 'ERROR')) {
            $io->success($command);
        } else {
            $io->error($command);
        }
    }
}
