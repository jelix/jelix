<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2016-2019 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
 */

namespace Jelix\JelixModule\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RedisKvdbDeletionWorker extends \Jelix\Scripts\ModuleCommandAbstract
{
    protected function configure()
    {
        $this
            ->setName('jkvdb:redis:delete')
            ->setDescription('Worker to delete jKvDb items stored into a Redis database')
            ->setHelp('')
            ->addArgument(
                'profile',
                InputArgument::REQUIRED,
                'profile of jKvDb to use to access to Redis'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $redisDriver = \jKvDb::getConnection($input->getArgument('profile'));

        if (get_class($redisDriver) != 'redis_phpKVDriver' &&
            get_class($redisDriver) != 'redis_extKVDriver') {
            $output->writeln('<error>Error, wrong profile. It does not use a redis driver</error>');

            return 1;
        }

        $output->writeln('---  Starting worker...');
        $redis = $redisDriver->getRedis();
        if (get_class($redisDriver) == 'redis_phpKVDriver') {
            $this->deletionLoop('jkvdbredisdelkeys', $output, $redis, false);
        } elseif (get_class($redisDriver) == 'redis_extKVDriver') {
            $this->deletionLoop('jkvdbredisdelkeys', $output, $redis, true);
        }

        return 0;
    }

    protected function deletionLoop($key, OutputInterface $output, $redis, $isExt)
    {
        while (true) {
            if ($isExt) {
                $prefixKey = $redis->lPop($key);
            } else {
                $prefixKey = $redis->lpop($key);
            }

            if (!$prefixKey) {
                sleep(1);

                continue;
            }
            if ($output->isVerbose()) {
                $output->writeln("flush {$prefixKey}");
            }
            $redis->flushByPrefix($prefixKey);
        }
    }
}
