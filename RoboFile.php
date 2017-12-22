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
            "git@github.com:OpenDataStack/elasticsearch-import-api-client.git" => "src/elasticsearch-import-api-client",
            // DKAN Dockerfile / Image
            "git@github.com:OpenDataStack/dkan-opendatastack-docker.git" => "src/dkan-opendatastack-docker",
            // DKAN Open Data Stack Source
            "git@github.com:OpenDataStack/dkan-opendatastack.git" => "src/dkan-opendatastack-docker/src/dkan-opendatastack"
        ];
        return $sources;
    }

    public function setup()
    {
        $this->io()->section("Set up project for development");
        $this->_mkdir('src');

        foreach ($this->_sources() as $repo => $destination) {
            if (!file_exists($destination . '/.git')) {
                // Clone 
                $this->taskGitStack()
                ->stopOnFail()
                ->cloneRepo($repo, $destination)
                ->run();                
            }
        }

        // Run composer install for the symfony app
        $this->taskComposerInstall()
        ->dir("src/elasticsearch-import-api-docker/src/elasticsearch-import-api-symfony")
        ->noInteraction()
        ->run();
    }

    // Version Control Operations

    public function gitPull()
    {
        $this->io()->section("Update all repositories");

        foreach ($this->_sources() + ['repo' => '.'] as $repo => $destination) {
            if (!file_exists($destination)) {
                $this->io->error("Repo directory does not exist, have you ran `robo setup`?");
            } else {
                $this->taskGitStack()
                     ->dir($destination)
                     ->stopOnFail()
                     ->pull()
                     ->run();
            }
        }
    }

    public function gitPush()
    {
        $this->io()->section("Push all repositories");

        foreach ($this->_sources() + ['repo' => '.'] as $repo => $destination) {
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

        foreach ($this->_sources() + ['repo' => '.'] as $repo => $destination) {
            $this->taskExec("git status")
            ->dir($destination)
            ->run();
        }
    }

    // Docker Operations

    public function dockerUpProd()
    {
        $this->taskExec('docker-compose')->arg('stop')->run();
        $this->taskExec('docker-compose')->arg('up')->run();
    }

    public function dockerUpDev()
    {
        $dockerCompose = [
            'docker-compose',
            '-f docker-compose.yml',
        ];

        // 'Darwin' or 'Linux'
        if (PHP_OS == 'Darwin') {
            $this->taskExec('docker-sync')->arg('stop')->run();
            // TODO add fast start arg cutting out clean step
            $this->taskExec('docker-sync')->arg('clean')->run();
            $this->taskExec('docker-sync')->arg('start')->run();
        }
        // 'Darwin' or 'Linux' => darwin or linux
        $dockerCompose[] = '-f docker-compose.dev.' . strtolower(PHP_OS) . '.yml';
        //$dockerCompose[] = '-f docker-compose.dev.linux.yml';

        $this->taskExec('docker-compose')->arg('stop')->run();
        $this->taskExec(implode(' ', $dockerCompose))->args('up')->run();

    }

    public function dockerRebuild()
    {
        $this->taskExec('docker-compose')->arg('stop')->run();
        $this->taskExec('docker-compose')
             ->arg('build')
             ->run();
    }

    public function dockerPush()
    {
        print "TODO";
    }

    // PHPUnit Tests
    
    public function test()
    {
      $this->taskPHPUnit()
        ->dir("src/elasticsearch-import-api-client")
        ->files('./src/tests/*')
          ->group('Integration')
        ->run();
    }

    public function dkanInstall()
    {
        $this->taskExecStack()
            ->stopOnFail()
            ->exec('time docker-compose exec --user=www-data dkan_apache_php /bin/bash -c "cd /var/www/html/docroot && drush si dkan --verbose --account-pass=\'admin\' --site-name=\'DKAN\' install_configure_form.update_status_module=\'array(FALSE,FALSE)\' --yes"')
            ->exec('time docker-compose exec --user=www-data dkan_apache_php /bin/bash -c "cd /var/www/html/docroot && drush  -y en custom_config"')
            ->exec('time docker-compose exec --user=www-data dkan_apache_php /bin/bash -c "cd /var/www/html/docroot && drush fr -y  --force custom_config"')
            ->exec('time docker-compose exec --user=www-data dkan_apache_php /bin/bash -c "cd /var/www/html/docroot && drush cc all"')
            ->run();

    }

    public function dkanDrush(array $args)
    {
        $this->taskExec('
            docker-compose exec --user=www-data dkan_apache_php /bin/bash -c "cd /var/www/html/docroot && drush ' . implode(' ', $args) . '"
        ')->run();
    }

}
