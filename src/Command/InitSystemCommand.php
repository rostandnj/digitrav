<?php
/**
 * Created by PhpStorm.
 * User: rostandnj
 * Date: 13/3/19
 * Time: 1:07 PM
 */

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


class InitSystemCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:init-system';

    protected $container;


    public function __construct(ContainerInterface $c)
    {
        // best practices recommend to call the parent constructor first and
        // then set your own properties. That wouldn't work in this case
        // because configure() needs the properties set in this constructor
        $this->container = $c;
        //$this->requirePassword = $requirePassword;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('initialisation of the system by creating SADMIN user,role table,quater table.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to initialise of the system by creating SADMIN user,role table,quater table');

        //$this->addArgument('password', $this->requirePassword ? InputArgument::REQUIRED : InputArgument::OPTIONAL, 'User password');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $systemService = $this->container->get('init_system');
        $statut = $systemService->getStatut();
        

        if($statut["statut"]==1) $output->writeln($statut["message"]);
        else
        {
            $statut = $systemService->initSystem();
            $output->writeln($statut["message"]);

        }


    }
}