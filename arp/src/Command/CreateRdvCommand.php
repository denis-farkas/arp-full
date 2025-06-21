<?php
// src/Command/CreatePackCommand.php
namespace App\Command;

use App\Entity\Rdv;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


class CreateRdvCommand extends Command
{
    protected static $defaultName = 'app:create-rdv';
    protected static $defaultDescription = 'Create rdv';

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->setName('app:create-rdv');
            
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $rdv = [
            ['name' => 'rendez-vous', 'price' => 50,'duration'=>30],
        ];

        foreach ($rdv as $rdvData) {
            $rdv = new Rdv();
            $rdv->setName($rdvData['name']);           
            $rdv->setPrice($rdvData['price']);
            $rdv->setDuration($rdvData['duration']);
            
            $this->entityManager->persist($rdv);
        }

        $this->entityManager->flush();

        $io->success('rendez-vous have been created successfully.');

        return Command::SUCCESS;
    }
}