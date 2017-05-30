<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Model\UserModel;

class UserCreateCommand extends Command
{
    protected $userModel;

    public function __construct(UserModel $userModel)
    {
        $this->userModel = $userModel;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('user:create')
            ->setDescription('Create a user without validation')
            ->addArgument(
                'email',
                InputArgument::REQUIRED,
                ''
            )
            ->addArgument(
                'username',
                InputArgument::OPTIONAL,
                ''
            )
            ->addArgument(
                'password',
                InputArgument::OPTIONAL,
                ''
            )
            ->addArgument(
                'role',
                InputArgument::OPTIONAL,
                ''
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $email = $input->getArgument('email');
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');
        $role = $input->getArgument('role');

        $user = $this->userModel->forceRegister($email, $username, $password, $role);

        $output->writeln('User created with :');
        $output->writeln('- email : ' . $user->getEmail());
        $output->writeln('- username : ' . $user->getUsername());
        $output->writeln('- password : ' . $user->getPlainPassword());
        $output->writeln('- role : ' . $user->getRoles()[0]);
    }
}
