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

class RedisCacheDeletionWorker extends \Jelix\Scripts\ModuleCommandAbstract
{
    protected function configure()
    {
        $this
            ->setName('jcache:redis:delete')
            ->setDescription('Worker to delete jCache items stored into a Redis database')
            ->setHelp('')
            ->addArgument(
                'profile',
                InputArgument::REQUIRED,
                'profile of jCache to use to access to Redis'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $redisPlugin = \jCache::getDriver($input->getArgument('profile'));

        if (get_class($redisPlugin) != 'redis_phpCacheDriver'
            && get_class($redisPlugin) != 'redis_extCacheDriver') {
            $output->writeln('<error>Error, wrong profile. It does not use a redis driver</error>');

            return 1;
        }

        $output->writeln('---  Starting worker...');
        $redis = $redisPlugin->getRedis();
        if (get_class($redisPlugin) == 'redis_phpCacheDriver') {
            $this->deletionLoop('jcacheredisdelkeys', $output, $redis, false);
        } elseif (get_class($redisPlugin) == 'redis_extCacheDriver') {
            $this->deletionLoop('jcacheredisdelkeys', $output, $redis, true);
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
