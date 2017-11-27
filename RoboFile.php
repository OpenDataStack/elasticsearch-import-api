<?php
/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class RoboFile extends \Robo\Tasks
{

    private function _sources() {
        $sources = [
            // Import API Docker file
            "git@github.com:OpenDataStack/elasticsearch-import-api-docker.git" => "src/elasticsearch-import-api-docker",
            // API server (Symfony app)
            "git@github.com:OpenDataStack/elasticsearch-import-api-symfony.git" => "src/elasticsearch-import-api-docker/src/elasticsearch-import-api-symfony",
            // API client
            "git@github.com:OpenDataStack/elasticsearch-import-api-client.git" => "src/elasticsearch-import-api-client"
        ];
        return $sources;
    }

    public function setup()
    {
        $this->io()->section("Set up project for development");
        $this->_mkdir('src');

        foreach ($this->_sources() as $repo => $destination) {
            // Clone 
            $this->taskGitStack()
            ->stopOnFail()
            ->cloneRepo($rep, $destination)
            ->run();
        }

        // Run composer install for the symfony app
        $this->taskComposerInstall()
        ->dir("src/elasticsearch-import-api-docker/src/elasticsearch-import-api-symfony")
        ->noInteraction()
        ->run();
    }

    public function gitPull()
    {
        $this->io()->section("Update all repositories");

        foreach ($this->_sources() as $repo => $destination) {
            $this->taskGitStack()
            ->dir($destination)
            ->stopOnFail()
            ->pull()
            ->run();
        }
    }

    public function gitPush()
    {
        $this->io()->section("Push all repositories");

        foreach ($this->_sources() as $repo => $destination) {
            $this->taskGitStack()
            ->dir($destination)
            ->stopOnFail()
            ->push()
            ->run();
        }
    }

    public function gitStatus()
    {
        $this->io()->section("Status of all repositories");

        foreach ($this->_sources() as $repo => $destination) {
            $this->taskExec("git status")
            ->dir($destination)
            ->run();
        }
    }

    public function dockerUpProd()
    {
        $this->taskExec('docker-compose')->arg('stop')->run();
        $this->taskExec('docker-compose')->arg('up')->run();
    }

    public function dockerUpDev()
    {
        $this->taskExec('docker-compose')->arg('stop')->run();
        $this->taskExec('docker-compose -f docker-compose.yml -f docker-compose.dev.yml')->arg('up')->run();
    }

    public function dockerRebuild()
    {
        $this->taskExec('docker-compose')->arg('stop')->run();
        $this->taskExec('docker-compose')
        ->arg('up')
        ->option('build')
        ->run();
    }

    public function dockerPush()
    {
        print "TODO";
    }

    public function test()
    {
      $this->taskPHPUnit()
        ->dir("src/elasticsearch-import-api-client")
        ->files('./src/tests/*')
        ->run();
    }

}
