<?php
/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class RoboFile extends \Robo\Tasks
{
    public function setup()
    {
        $this->io()->section("Clone repositories");
        $this->_mkdir('src');

        // Clone Import API Docker file
        $this->taskGitStack()
        ->stopOnFail()
        ->cloneRepo("git@github.com:OpenDataStack/elasticsearch-import-api-docker.git", "src/elasticsearch-import-api-docker")
        ->run();

        // Clone API server (Symfony app)
        $this->taskGitStack()
        ->stopOnFail()
        ->cloneRepo("git@github.com:OpenDataStack/elasticsearch-import-api-symfony.git", "src/elasticsearch-import-api-docker/src/elasticsearch-import-api-symfony")
        ->run();

        // Clone API client
        $this->taskGitStack()
        ->stopOnFail()
        ->cloneRepo("git@github.com:OpenDataStack/elasticsearch-import-api-client.git", "src/elasticsearch-import-api-client")
        ->run();

        // Run composer install for the symfony app
        $this->taskComposerInstall()
        ->dir("src/elasticsearch-import-api-docker/src/elasticsearch-import-api-symfony")
        ->noInteraction()
        ->run();
    }

    public function dockerUp()
    {
        $this->taskExec('docker-compose')->arg('stop')->run();
        $this->taskExec('docker-compose')->arg('up')->run();
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
