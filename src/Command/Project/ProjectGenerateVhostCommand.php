<?php
namespace AlterNET\Cli\Command\Project;

use AlterNET\Cli\Command\CommandBase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class ProjectGenerateVhostCommand
 * @author Arek van Schaijk <arek@alternet.nl>
 */
class ProjectGenerateVhostCommand extends CommandBase
{

    /**
     * Configure
     *
     */
    protected function configure()
    {
        $this->setName('project:generatevhost');
        $this->setDescription('Generates the Vhost file for the project');
    }

    /**
     * Executes the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $fileSystem = new Filesystem();

        $fileSystem->touch([
            '.alternet/local/access.log',
            '.alternet/local/.log',
        ]);


        $output = '<VirtualHost *:80>' . PHP_EOL;
        $output .= '	DocumentRoot 	"' . realpath(getcwd()) . '"' . PHP_EOL;
        $i = 0;
        if (($domains = $environmentConfig->getDomains())) {
            foreach ($domains as $domain) {
                switch ($i) {
                    case 0:
                        $output .= '	ServerName 		' . $domain . PHP_EOL;
                        break;
                    default:
                        $output .= '	ServerAlias		' . $domain . PHP_EOL;
                }
                $i++;
            }
        }
        $output .= '	ErrorLog 		"' . realpath($projectRootPath . '/.alternet/local/error.log') . '"' . PHP_EOL;
        $output .= '	CustomLog 		"' . realpath($projectRootPath . '/.alternet/local/access.log') . '"' . PHP_EOL;
        $output .= '</VirtualHost>';
        return $output;


    }



}