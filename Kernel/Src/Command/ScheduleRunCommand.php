<?php

namespace Src\Command;

use Exception;
use GO\Scheduler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class ScheduleRunCommand extends Command
{
    protected static $defaultName = "schedule:run";

    protected function configure()
    {
        $this->setDescription("Run the scheduled commands");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $scheduledJobs = Yaml::parseFile(__DIR__ . "/../../../config/scheduled_jobs.yml");
            $scheduler = new Scheduler();
            foreach ($scheduledJobs as $identifier => $command) {
                $scheduler
                ->php(__DIR__ . "/../../../bin/console.php", null, [
                    $command["command"] => null
                ], $identifier)->at($command["at"])
                ->then(function ($out) use ($output) {
                    $output->writeln($out);
                });
            }
            $scheduler->run();
            return Command::SUCCESS;
        } catch (Exception $ex) {
            $output->writeln("<error>" . $ex->getMessage() . "</error>");
            return Command::FAILURE;
        }
    }
}
