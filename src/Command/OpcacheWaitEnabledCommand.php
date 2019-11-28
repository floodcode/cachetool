<?php

/*
 * This file is part of CacheTool.
 *
 * (c) Samuel Gordalina <samuel.gordalina@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CacheTool\Command;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class OpcacheWaitEnabledCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('opcache:wait:enabled')
            ->setDescription('Wait until opcache is enabled')
            ->addOption(
                'timeout',
                null,
                InputOption::VALUE_REQUIRED,
                'Wait timeout is seconds'
            )
            ->setHelp('');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $timeout = $input->hasOption('timeout') ? intval($input->getOption('timeout')) : 0;
        $startTime = time();

        do {
            try {
                $info = $this->getCacheTool()->opcache_get_status(false);
            } catch (\Exception $ex) {
                $info = false;
            }

            $enabled = $this->isOpcacheEnabled($info);

            if (!$enabled && $timeout > 0 && time() - $startTime >= $timeout) {
                throw new \RuntimeException('OPcache wait timeout exceeded');
            }

            if (!$enabled) {
                sleep(1);
            }
        } while (!$enabled);

        $table = new Table($output);
        $table->setHeaders(['Enabled']);
        $table->setRows([[$enabled]]);
        $table->render();
    }

    /**
     * @param array $info
     * @return bool
     */
    private function isOpcacheEnabled($info)
    {
        return is_array($info) && array_key_exists('opcache_enabled', $info) && $info['opcache_enabled'];
    }
}
