<?php

namespace App\Command;

use App\Catalogue\KodokanCatalogue;
use App\Entity\MotionFormat;
use App\Entity\Technique;
use App\Entity\WazaCategory;
use App\Motion\MotionStorage;
use App\Repository\TechniqueRepository;
use App\Repository\WazaCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[AsCommand(name: 'app:seed', description: 'Load the Kodokan catalogue and the bundled motions. Safe to run again.')]
final class SeedCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly WazaCategoryRepository $categories,
        private readonly TechniqueRepository $techniques,
        private readonly MotionStorage $motions,
        private readonly string $seedMotionDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('replace-motions', null, InputOption::VALUE_NONE, 'Overwrite motions that already exist with the bundled ones');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $slugger = new AsciiSlugger();
        $created = $updated = 0;

        foreach (KodokanCatalogue::categories() as $cPos => $c) {
            $category = $this->categories->findOneBy(['slug' => $c['slug']]);
            if (null === $category) {
                $category = new WazaCategory($c['slug'], $c['name'], $c['kanji'], $c['english'], $c['family'], $cPos);
                $this->em->persist($category);
            } else {
                $category->update($c['name'], $c['kanji'], $c['english'], $c['family'], $cPos);
            }

            foreach ($c['techniques'] as $tPos => $t) {
                $slug = $slugger->slug($t['name'])->lower()->toString();
                $technique = $this->techniques->findOneBy(['slug' => $slug]);
                if (null === $technique) {
                    $this->em->persist(new Technique($slug, $t['name'], $t['kanji'], $t['english'], $category, $tPos, $t['gokyo'], $t['prohibited'], $t['notes']));
                    ++$created;
                } else {
                    $technique->update($t['name'], $t['kanji'], $t['english'], $category, $tPos, $t['gokyo'], $t['prohibited'], $t['notes']);
                    ++$updated;
                }
            }
        }
        $this->em->flush();
        $io->writeln(sprintf('Techniques: %d created, %d updated.', $created, $updated));

        $loaded = 0;
        // No GLOB_BRACE: it does not exist on musl-based systems such as Alpine.
        foreach (glob($this->seedMotionDir.'/*') ?: [] as $file) {
            $format = MotionFormat::fromFilename($file);
            if (null === $format || !is_file($file)) {
                continue;
            }
            $slug = pathinfo($file, \PATHINFO_FILENAME);
            $technique = $this->techniques->findOneBy(['slug' => $slug]);
            if (null === $technique) {
                $io->warning(sprintf('Skipped %s: no technique with slug "%s".', basename($file), $slug));
                continue;
            }
            if (null !== $technique->getMotion() && !$input->getOption('replace-motions')) {
                continue;
            }
            $this->motions->store($technique, $format, (string) file_get_contents($file));
            ++$loaded;
        }
        $io->writeln(sprintf('Motions: %d loaded from %s.', $loaded, basename($this->seedMotionDir)));
        $io->success('Catalogue is ready.');

        return Command::SUCCESS;
    }
}
