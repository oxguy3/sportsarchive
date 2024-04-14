<?php

namespace App\Command;

use App\Entity\Document;
use App\Entity\Headshot;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use League\Flysystem\UnableToRetrieveMetadata;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:fix-timestamps',
    description: 'Retroactively add timestamps to Doctrine entities',
    hidden: false
)]
class FixEntityTimestampsCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Filesystem $documentsFilesystem,
        private readonly Filesystem $headshotsFilesystem
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('type', InputArgument::REQUIRED, 'one of: documents, headshots');
        $this->addOption(
            'dry-run',
            null,
            InputOption::VALUE_OPTIONAL,
            'Run without modifying database?',
            false
        );
    }

    /**
     * @template T
     *
     * @param class-string<T> $entityClass
     *
     * @return T[]
     */
    private function getNullEntities(string $entityClass): array
    {
        // @phpstan-ignore-next-line
        $repo = $this->entityManager->getRepository($entityClass);
        $entities = $repo->createQueryBuilder('e')
            ->andWhere('e.updatedAt is NULL')
            ->getQuery()
            ->getResult();

        return $entities;
    }

    /**
     * @param class-string $entityClass
     */
    private function executeForFilesystemEntity(string $entityClass, Filesystem $filesystem, OutputInterface $output, bool $isDryRun): void
    {
        $entities = $this->getNullEntities($entityClass);
        foreach ($entities as $entity) {
            $id = $entity->getId();
            $path = null;

            if ($entityClass == Document::class) {
                $path = $entity->getFilePath();
            } elseif ($entityClass == Headshot::class) {
                $path = $entity->getFilename();
            } else {
                throw new \Exception("Unsupported entity type: {$entityClass}");
            }

            try {
                $lastModified = $filesystem->lastModified($path);
                $dt = new \DateTime('@'.(string) $lastModified);
                $entity->setUpdatedAt($dt);

                $dtstr = $dt->format('r');
                $output->writeln("<info>{$id} - {$dtstr}</info>");
            } catch (FilesystemException|UnableToRetrieveMetadata $exception) {
                $message = $exception->getMessage();
                $output->writeln("<error>Failure on #{$id}: {$message}</error>");
            }
        }
        if (!$isDryRun) {
            $this->entityManager->flush();
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $type = $input->getArgument('type');
        $isDryRun = $input->getOption('dry-run') !== false;
        if ($type == 'documents') {
            $this->executeForFilesystemEntity(Document::class, $this->documentsFilesystem, $output, $isDryRun);
        } elseif ($type == 'headshots') {
            $this->executeForFilesystemEntity(Headshot::class, $this->headshotsFilesystem, $output, $isDryRun);
        } else {
            throw new \Exception('Unsupported type');
        }

        return Command::SUCCESS;
    }
}
