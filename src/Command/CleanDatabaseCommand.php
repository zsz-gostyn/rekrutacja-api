<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Subscriber;
use App\Entity\School;
use App\Entity\Token;

class CleanDatabaseCommand extends Command
{
    protected static $defaultName = 'app:clean-database';
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Czyści bazę danych z niepotrzebnych zasobów');
        $this->setHelp('Ta komenda pozwala Ci wyczyścić bazę danych z niepotrzebnych zasobów');
        $this->addArgument('age-limit', InputArgument::OPTIONAL, 'Mininalny czas istnienia zasobu', 86400); // 86400 seconds equals one day
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
        }
        $this->entityManager->flush();

        $invalidTokens = $this->entityManager->getRepository(Token::class)->getInvalidTokens();
        foreach ($invalidTokens as $invalidToken) {
            $this->entityManager->remove($invalidToken);
        }
        $this->entityManager->flush();

    }
}
