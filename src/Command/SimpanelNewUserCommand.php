<?php
/**
 * Command to create a new user.
 *
 * @author William Rich
 * @copyright https://servers4all.documize.com/s/Wm5Pm0A1QQABQ1xw/simpanel/d/WnDQ5EA1QQABQ154/simpanel-license
 */

namespace App\Command;

use App\Service\User\UserManagementService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Entity\User;

class SimpanelNewUserCommand extends Command
{
    protected static $defaultName = 'simpanel:newUser';

    /**
     * @var UserManagementService
     */
    private $ums;

    /**
     * @var EntityManagerInterface - Doctrines entity manager.
     */
    private $em;

    public function __construct(?string $name = null , UserManagementService $userManagementService, EntityManagerInterface $em)
    {
        $this->ums = $userManagementService;
        $this->em = $em;

        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setDescription('Creates an admin user');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $fields = ['first name' , 'last name' , 'username' , 'password', 'password_confirm' , 'email'];
        $userField = [];

        $userField['subuser'] = 0;
        $userField['admin'] = 0;

        foreach ($fields as $field)
        {
            $fieldInput = $io->ask('Please provide the users: ' . $field);
            $userField[$field] = $fieldInput;

        }

        try{
            $user = $this->ums->createUser($userField);
            $this->em->persist($user);
            $this->em->flush();

            $io->success("User Created");
        } catch (\Exception $e) {
            $io->error('Created failed with message: ' . $e->getMessage());
        }
    }
}
