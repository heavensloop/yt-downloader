<?php

namespace App\Command;

use App\Service\Videos;
use App\Service\Youtube;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'download-videos',
    description: 'Add a short description for your command',
)]
class DownloadVideosCommand extends Command
{
    public function __construct(
        private readonly Youtube $youtube,
        private readonly Videos $videos,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('show-command-only', null, InputOption::VALUE_NONE, 'Show only the command to download the video')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->info("Finding next video to download...");
        $count = 1;

        while($video = $this->videos->getNext()) {
            $io->info("Downloading video {$count}: {$video['snippet']['title']}");
            $showCommandOnly = $input->getOption('show-command-only');
            $command = $this->youtube->download($video['id']['videoId'], !$showCommandOnly);

            if ($showCommandOnly) {
                $io->note($command);
            }

            $count++;
        }

        $io->success('Downloads complete.');

        return Command::SUCCESS;
    }
}
