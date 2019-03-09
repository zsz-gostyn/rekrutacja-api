<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Subscriber;
use App\Entity\School;

class ClearSpamCommand extends Command
{
    protected static $defaultName = 'app:clear-spam';
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Clear unwanted resources');
        $this->setHelp('This command allows you to clear unwanted resources');
        $this->addArgument('age-limit', InputArgument::OPTIONAL, 'Resource\'s age limit', 86400); // 86400 seconds equals one day
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ageLimit = $input->getArgument('age-limit');
        $subscribers = $this->entityManager->getRepository(Subscriber::class)->getUnwantedSubscribers($ageLimit);
        
        foreach ($subscribers as $subscriber) {
            $this->entityManager->remove($subscriber);
        }
        
        $this->entityManager->flush();

        $unassignedSchools = $this->entityManager->getRepository(School::class)->getUnwantedSchools($ageLimit);
        foreach ($unassignedSchools as $school) {
            $this->entityManager->remove($school);
            $output->writeln([$school->getName()]);
        }

        $this->entityManager->flush();
    }
}
