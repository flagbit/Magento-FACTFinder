<?php

/**
 * path to robo file
 */
require 'robo/Tasks/MagentoDeployment.php';

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class RoboFile extends \Flagbit\Tasks\MagentoDeployment {

    /**
     * @param       $instance
     * @param array $opt
     *
     * @return mixed|void
     */
    public function deploy($instance, array $opt = ['tag|t' => null, 'branch|b' => null, 'no-test' => false, 'backup' => false, 'sync|s' => false]) {
        $this->stopOnFail();
        $deployment = $this->getDeployment($instance);
        if(!empty($opt['tag'])) {
            $deployment->setTag($opt['tag']);
        }
        if(!empty($opt['branch'])) {
            $deployment->setBranch($opt['branch']);
        }

        if(!$opt['no-test']) {
            $this->updateFBSServer($deployment->getTesting(), $deployment->getBranch());
            $this->runBehatTests($deployment->getTesting(), 'testing');
            $this->runPHPUnitTests($deployment->getTesting());
        }

        if($opt['backup'] === true) {
            $this->createDumpWithMagerun2();
        }

        $this->createDeploymentDirectory();
        $this->cloneGit('git@bitbucket.org:flagbit/some-repository.git', $deployment->getBranch(), $deployment->getTempPath());
        $this->composerInstall($deployment->getTempPath().'/src');
        $this->runNPMInstall();

        $this->copyFilesToServer();
        $this->linkSharedResources([
            'app/etc/env.php',
            'pub/media',
            'pub/sitemap.xml',
            'var/backups',
            'var/composer_home',
            'var/importexport',
            'var/import_history',
            'var/log',
            'var/session',
            'var/tmp'
        ]);
        $this->runMageConfigSync();
        $this->runMagentoSetDeployMode();
        $this->runMagentoUpgradeAndCompile();
        $this->runMagentoStaticContentDeploy();
        $this->publishRevision();
        $this->runMagentoFlushCache();
        $this->removeOldRevisions();
        $this->removeDeploymentDirectory();
    }

    /**
     *
     */
    protected function runNPMInstall() {
        $this->say('Installing NPM Packages');
        $this->taskNpmInstall()
             ->dir($this->deployment->getTempPath().'src/pub')
             ->run();
    }
}