<?php


class jAppContextTest extends PHPUnit_Framework_TestCase {

    function testContext() {
    
        $appPath = jApp::appPath();
        $varPath = jApp::varPath();
        $logPath = jApp::logPath();
        $appConfigPath = jApp::appConfigPath();
        $configPath = jApp::configPath();
        $wwwPath = jApp::wwwPath();
        $scriptsPath = jApp::scriptsPath();
        $tempPath = jApp::tempPath();

        // first save
        jApp::saveContext();
        
        // verify that we still have the current path
        $this->assertEquals($appPath, jApp::appPath());
        $this->assertEquals($varPath, jApp::varPath());
        $this->assertEquals($logPath, jApp::logPath());
        $this->assertEquals($appConfigPath, jApp::appConfigPath());
        $this->assertEquals($configPath, jApp::configPath());
        $this->assertEquals($wwwPath, jApp::wwwPath());
        $this->assertEquals($scriptsPath, jApp::scriptsPath());
        $this->assertEquals($tempPath, jApp::tempPath());

        // change the path
        jApp::initPaths('/myapp/');
        $this->assertEquals('/myapp/', jApp::appPath());
        $this->assertEquals('/myapp/app/config/', jApp::appConfigPath());
        $this->assertEquals('/myapp/var/', jApp::varPath());
        $this->assertEquals('/myapp/var/log/', jApp::logPath());
        $this->assertEquals('/myapp/var/config/', jApp::configPath());
        $this->assertEquals('/myapp/www/', jApp::wwwPath());
        $this->assertEquals('/myapp/scripts/', jApp::scriptsPath());
        $this->assertEquals($tempPath, jApp::tempPath());

        // second save
        jApp::saveContext();
        jApp::initPaths('/myapp2/');
        $this->assertEquals('/myapp2/', jApp::appPath());
        $this->assertEquals('/myapp2/app/config/', jApp::appConfigPath());
        $this->assertEquals('/myapp2/var/', jApp::varPath());
        $this->assertEquals('/myapp2/var/log/', jApp::logPath());
        $this->assertEquals('/myapp2/var/config/', jApp::configPath());
        $this->assertEquals('/myapp2/www/', jApp::wwwPath());
        $this->assertEquals('/myapp2/scripts/', jApp::scriptsPath());
        $this->assertEquals($tempPath, jApp::tempPath());

        // pop the second save, we should be with the first saved values
        jApp::restoreContext();
        $this->assertEquals('/myapp/', jApp::appPath());
        $this->assertEquals('/myapp/app/config/', jApp::appConfigPath());
        $this->assertEquals('/myapp/var/', jApp::varPath());
        $this->assertEquals('/myapp/var/log/', jApp::logPath());
        $this->assertEquals('/myapp/var/config/', jApp::configPath());
        $this->assertEquals('/myapp/www/', jApp::wwwPath());
        $this->assertEquals('/myapp/scripts/', jApp::scriptsPath());
        $this->assertEquals($tempPath, jApp::tempPath());

        // pop the first save, we should be with initial paths
        jApp::restoreContext();
        $this->assertEquals($appPath, jApp::appPath());
        $this->assertEquals($appConfigPath, jApp::appConfigPath());
        $this->assertEquals($varPath, jApp::varPath());
        $this->assertEquals($logPath, jApp::logPath());
        $this->assertEquals($configPath, jApp::configPath());
        $this->assertEquals($wwwPath, jApp::wwwPath());
        $this->assertEquals($scriptsPath, jApp::scriptsPath());
        $this->assertEquals($tempPath, jApp::tempPath());
    }
}