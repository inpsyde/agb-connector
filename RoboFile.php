<?php

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class RoboFile extends \Robo\Tasks
{
    /**
     * Minify CSS
     */
    public function minifyCss()
    {
        $this->taskMinify('assets/css/style.css')->run();
    }

     /**
     * Minify JS
     */
    public function minifyJs()
    {
        $this->taskMinify('assets/js/settings.js')->run();
    }

    /**
     * Build package for wp.org
     * @return mixed
     */
    public function buildPackage()
    {

        $this->taskConcat([
            'wp-org/readme.txt',
            'changelog.md',
        ])
            ->to(sys_get_temp_dir() . '/readme.txt')
            ->run();

        return $this
            ->taskPack('agb-connector.zip')
            ->addDir('agb-connector/assets', 'assets')
            ->addDir('agb-connector/src', 'src')
            ->addFile('agb-connector/agb-connector.php', 'agb-connector.php')
            ->addFile('agb-connector/readme.txt', sys_get_temp_dir() . '/readme.txt')
            ->addFile('agb-connector/uninstall.php', 'uninstall.php')
            ->run();
    }
}