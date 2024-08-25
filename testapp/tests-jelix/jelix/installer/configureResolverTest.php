<?php


use Jelix\Installer\ModuleStatus;
use Jelix\Core\Infos\ModuleInfos;
use Jelix\Dependencies\Resolver;

class configureResolverTest extends \Jelix\UnitTests\UnitTestCase
{

    function testConfigureNoModules()
    {
        $resolver = new \Jelix\Installer\InstallationResolver();
        $modules = $resolver->getItemsToConfigure([], [], false);
        $this->assertEquals([], $modules);
    }

    protected function getComponentsList()
    {
        $modInfosJelix = ModuleInfos::load(__DIR__.'/../../../vendor/jelix/jelix-essential/jelix/core-modules/jelix');
        $modStatusJelix = new ModuleStatus('jelix', '/', [
            'jelix.installed' => true,
            'jelix.enabled' => true,
            'jelix.version' => $modInfosJelix->version,
            'jelix.dbprofile' => '',
        ], true);
        $modStatusTestinstall1 = new ModuleStatus('testinstall1', '/', [
            'testinstall1.installed' => false,
            'testinstall1.enabled' => false,
            'testinstall1.version' => '1.0',
            'testinstall1.dbprofile' => '',
            'testinstall1.localconf' => false,
        ], true);
        $modStatusTestinstall2 = new ModuleStatus('testinstall2', '/', [
            'testinstall2.installed' => false,
            'testinstall2.enabled' => false,
            'testinstall2.version' => '2.0',
            'testinstall2.dbprofile' => '',
            'testinstall2.localconf' => false,
        ], true);
        $modStatusTestinstall3 = new ModuleStatus('testinstall3', '/', [
            'testinstall3.installed' => false,
            'testinstall3.enabled' => false,
            'testinstall3.version' => '3.0',
            'testinstall3.dbprofile' => '',
            'testinstall3.localconf' => false,
        ], true);

        $modInfosTestinstall1 = ModuleInfos::load(__DIR__.'/../../../modules/testinstall1');
        $modInfosTestinstall1->version = '1.0';
        $modInfosTestinstall2 = ModuleInfos::load(__DIR__.'/../../../modules/testinstall2');
        $modInfosTestinstall2->version = '2.0';
        $modInfosTestinstall3 = ModuleInfos::load(__DIR__.'/../../../modules/testinstall3');
        $modInfosTestinstall3->version = '3.0';

        return array(
            'jelix'=>[$modInfosJelix, $modStatusJelix],
            'testinstall1'=>[$modInfosTestinstall1, $modStatusTestinstall1],
            'testinstall2'=>[$modInfosTestinstall2, $modStatusTestinstall2],
            'testinstall3'=>[$modInfosTestinstall3, $modStatusTestinstall3],
        );
    }

    function testGetItemsToConfigure()
    {
        $resolver = new \Jelix\Installer\InstallationResolver();
        $componentsList = $this->getComponentsList();
        // configure no modules
        $modules = $resolver->getItemsToConfigure($componentsList, [], false);
        $this->assertEquals([], $modules);

        // configure one module
        $modules = $resolver->getItemsToConfigure($componentsList, ['testinstall2'], false);
        $this->assertEquals(1, count($modules));
        $module = $modules[0];
        $this->assertEquals('testinstall2', $module->getName());
        $this->assertFalse($module->isInstalled());
        $this->assertTrue($module->canBeInstalled());
        $this->assertEquals('2.0', $module->getCurrentVersion());
        $this->assertEquals(null, $module->getNextVersion());
        $this->assertEquals(1, $module->getAction());
        $dependencies = $module->getDependencies();
        $this->assertEquals(1, count($dependencies));
        $this->assertTrue(isset($dependencies['jelix']));
        $this->assertEquals([], $module->getAlternativeDependencies());

        // configure two modules
        $modules = $resolver->getItemsToConfigure($componentsList, ['testinstall2', 'testinstall1'], false);
        $this->assertEquals(2, count($modules));

        $module = $modules[0];
        $this->assertEquals('testinstall2', $module->getName());
        $this->assertFalse($module->isInstalled());
        $this->assertTrue($module->canBeInstalled());
        $this->assertEquals('2.0', $module->getCurrentVersion());
        $this->assertEquals(null, $module->getNextVersion());
        $this->assertEquals(1, $module->getAction());
        $dependencies = $module->getDependencies();
        $this->assertEquals(1, count($dependencies));
        $this->assertTrue(isset($dependencies['jelix']));
        $this->assertEquals([], $module->getAlternativeDependencies());

        $module = $modules[1];
        $this->assertEquals('testinstall1', $module->getName());
        $this->assertFalse($module->isInstalled());
        $this->assertTrue($module->canBeInstalled());
        $this->assertEquals('1.0', $module->getCurrentVersion());
        $this->assertEquals(null, $module->getNextVersion());
        $this->assertEquals(1, $module->getAction());
        $dependencies = $module->getDependencies();
        $this->assertEquals(1, count($dependencies));
        $this->assertTrue(isset($dependencies['jelix']));
        $this->assertEquals([], $module->getAlternativeDependencies());

    }

    function testGetItemsToConfigureForce()
    {
        $resolver = new \Jelix\Installer\InstallationResolver();
        $componentsList = $this->getComponentsList();

        // configure one module already configured
        $componentsList['testinstall2'][1]->isInstalled  = false;
        $componentsList['testinstall2'][1]->isEnabled = true;

        $modules = $resolver->getItemsToConfigure($componentsList, ['testinstall2'], false);
        $this->assertEquals(0, count($modules));

        // force configure one module alreacdy configured
        $modules = $resolver->getItemsToConfigure($componentsList, ['testinstall2'], true);
        $this->assertEquals(1, count($modules));
        $module = $modules[0];
        $this->assertEquals('testinstall2', $module->getName());
        $this->assertTrue($module->isInstalled());
        $this->assertTrue($module->canBeInstalled());
        $this->assertEquals('2.0', $module->getCurrentVersion());
        $this->assertEquals(null, $module->getNextVersion());
        $this->assertEquals(1, $module->getAction());
        $dependencies = $module->getDependencies();
        $this->assertEquals(1, count($dependencies));
        $this->assertTrue(isset($dependencies['jelix']));
        $this->assertEquals([], $module->getAlternativeDependencies());

        // configure two modules
        $modules = $resolver->getItemsToConfigure($componentsList, ['testinstall2', 'testinstall1'], false);
        $this->assertEquals(1, count($modules));

        $module = $modules[0];
        $this->assertEquals('testinstall1', $module->getName());
        $this->assertFalse($module->isInstalled());
        $this->assertTrue($module->canBeInstalled());
        $this->assertEquals('1.0', $module->getCurrentVersion());
        $this->assertEquals(null, $module->getNextVersion());
        $this->assertEquals(1, $module->getAction());
        $dependencies = $module->getDependencies();
        $this->assertEquals(1, count($dependencies));
        $this->assertTrue(isset($dependencies['jelix']));
        $this->assertEquals([], $module->getAlternativeDependencies());

        // force configure two modules
        $modules = $resolver->getItemsToConfigure($componentsList, ['testinstall2', 'testinstall1'], true);
        $this->assertEquals(2, count($modules));

        $module = $modules[0];
        $this->assertEquals('testinstall2', $module->getName());
        $this->assertTrue($module->isInstalled());
        $this->assertTrue($module->canBeInstalled());
        $this->assertEquals('2.0', $module->getCurrentVersion());
        $this->assertEquals(null, $module->getNextVersion());
        $this->assertEquals(1, $module->getAction());
        $dependencies = $module->getDependencies();
        $this->assertEquals(1, count($dependencies));
        $this->assertTrue(isset($dependencies['jelix']));
        $this->assertEquals([], $module->getAlternativeDependencies());

        $module = $modules[1];
        $this->assertEquals('testinstall1', $module->getName());
        $this->assertFalse($module->isInstalled());
        $this->assertTrue($module->canBeInstalled());
        $this->assertEquals('1.0', $module->getCurrentVersion());
        $this->assertEquals(null, $module->getNextVersion());
        $this->assertEquals(1, $module->getAction());
        $dependencies = $module->getDependencies();
        $this->assertEquals(1, count($dependencies));
        $this->assertTrue(isset($dependencies['jelix']));
        $this->assertEquals([], $module->getAlternativeDependencies());

    }


    function testGetItemsToConfigureWithDependencies()
    {
        $resolver = new \Jelix\Installer\InstallationResolver();
        $componentsList = $this->getComponentsList();

        $componentsList['testinstall1'][0]->dependencies[] =
            array('name' => 'testinstall2', 'type' => 'module', 'version' => '2.0.*', 'optional' => false)
        ;

        // configure no modules
        $modules = $resolver->getItemsToConfigure($componentsList, [], false);
        $this->assertEquals([], $modules);

        // configure one module
        $modules = $resolver->getItemsToConfigure($componentsList, ['testinstall2'], false);
        $this->assertEquals(1, count($modules));
        $module = $modules[0];
        $this->assertEquals('testinstall2', $module->getName());
        $this->assertFalse($module->isInstalled());
        $this->assertTrue($module->canBeInstalled());
        $this->assertEquals('2.0', $module->getCurrentVersion());
        $this->assertEquals(null, $module->getNextVersion());
        $this->assertEquals(1, $module->getAction());
        $dependencies = $module->getDependencies();
        $this->assertEquals(1, count($dependencies));
        $this->assertTrue(isset($dependencies['jelix']));
        $this->assertEquals([], $module->getAlternativeDependencies());

        // configure two modules
        $modules = $resolver->getItemsToConfigure($componentsList, ['testinstall1'], false);
        $this->assertEquals(2, count($modules));

        $module = $modules[0];
        $this->assertEquals('testinstall2', $module->getName());
        $this->assertFalse($module->isInstalled());
        $this->assertTrue($module->canBeInstalled());
        $this->assertEquals('2.0', $module->getCurrentVersion());
        $this->assertEquals(null, $module->getNextVersion());
        $this->assertEquals(1, $module->getAction());
        $dependencies = $module->getDependencies();
        $this->assertEquals(1, count($dependencies));
        $this->assertTrue(isset($dependencies['jelix']));
        $this->assertEquals([], $module->getAlternativeDependencies());

        $module = $modules[1];
        $this->assertEquals('testinstall1', $module->getName());
        $this->assertFalse($module->isInstalled());
        $this->assertTrue($module->canBeInstalled());
        $this->assertEquals('1.0', $module->getCurrentVersion());
        $this->assertEquals(null, $module->getNextVersion());
        $this->assertEquals(1, $module->getAction());
        $dependencies = $module->getDependencies();
        $this->assertEquals(2, count($dependencies));
        $this->assertTrue(isset($dependencies['jelix']));
        $this->assertTrue(isset($dependencies['testinstall2']));
        $this->assertEquals([], $module->getAlternativeDependencies());


        $componentsList['testinstall2'][1]->isInstalled = true;
        $componentsList['testinstall2'][1]->isEnabled = true;

        $modules = $resolver->getItemsToConfigure($componentsList, ['testinstall1'], false);
        $this->assertEquals(1, count($modules));

        $module = $modules[0];
        $this->assertEquals('testinstall1', $module->getName());
        $this->assertFalse($module->isInstalled());
        $this->assertTrue($module->canBeInstalled());
        $this->assertEquals('1.0', $module->getCurrentVersion());
        $this->assertEquals(null, $module->getNextVersion());
        $this->assertEquals(1, $module->getAction());
        $dependencies = $module->getDependencies();
        $this->assertEquals(2, count($dependencies));
        $this->assertTrue(isset($dependencies['jelix']));
        $this->assertTrue(isset($dependencies['testinstall2']));
        $this->assertEquals([], $module->getAlternativeDependencies());

        $modules = $resolver->getItemsToConfigure($componentsList, ['testinstall1'], true);
        $this->assertEquals(1, count($modules));

        $module = $modules[0];
        $this->assertEquals('testinstall1', $module->getName());
        $this->assertFalse($module->isInstalled());
        $this->assertTrue($module->canBeInstalled());
        $this->assertEquals('1.0', $module->getCurrentVersion());
        $this->assertEquals(null, $module->getNextVersion());
        $this->assertEquals(1, $module->getAction());
        $dependencies = $module->getDependencies();
        $this->assertEquals(2, count($dependencies));
        $this->assertTrue(isset($dependencies['jelix']));
        $this->assertTrue(isset($dependencies['testinstall2']));
        $this->assertEquals([], $module->getAlternativeDependencies());
    }


    function testConfigureToConfigureModulesToUpgrade()
    {
        $componentsList = $this->getComponentsList();

        $componentsList['testinstall1'][1]->isInstalled  = true;
        $componentsList['testinstall1'][1]->isEnabled = true;
        $componentsList['testinstall1'][0]->version = '1.5';
        $componentsList['testinstall1'][0]->dependencies[] =
            array('name'=>'testinstall2', 'type'=>'module', 'version'=>'2.5.*', 'optional'=>false)
        ;

        $componentsList['testinstall2'][1]->isInstalled  = true;
        $componentsList['testinstall2'][1]->isEnabled = true;
        $componentsList['testinstall2'][0]->version = '2.5.1';

        $resolver = new \Jelix\Installer\InstallationResolver();
        $modules = $resolver->getItemsToConfigure($componentsList, ['testinstall1'], false);
        $this->assertEquals(1, count($modules));

        $modules = $resolver->getItemsToConfigure($componentsList, ['testinstall1'], true);
        $this->assertEquals(2, count($modules));

        $module = $modules[0];
        $this->assertEquals('testinstall2', $module->getName());
        $this->assertTrue($module->isInstalled());
        $this->assertTrue($module->canBeInstalled());
        $this->assertEquals('2.0', $module->getCurrentVersion());
        $this->assertEquals('2.5.1', $module->getNextVersion());
        $this->assertEquals(Resolver::ACTION_UPGRADE, $module->getAction());
        $dependencies = $module->getDependencies();
        $this->assertEquals(1, count($dependencies));
        $this->assertTrue(isset($dependencies['jelix']));
        $this->assertEquals([], $module->getAlternativeDependencies());

        $module = $modules[1];
        $this->assertEquals('testinstall1', $module->getName());
        $this->assertTrue($module->isInstalled());
        $this->assertTrue($module->canBeInstalled());
        $this->assertEquals('1.0', $module->getCurrentVersion());
        $this->assertEquals('1.5', $module->getNextVersion());
        $this->assertEquals(Resolver::ACTION_UPGRADE, $module->getAction());
        $dependencies = $module->getDependencies();
        $this->assertEquals(2, count($dependencies));
        $this->assertTrue(isset($dependencies['jelix']));
        $this->assertTrue(isset($dependencies['testinstall2']));
        $this->assertEquals([], $module->getAlternativeDependencies());

        $modules = $resolver->getItemsToConfigure($componentsList, ['testinstall2'], true);
        $this->assertEquals(1, count($modules));

        $module = $modules[0];
        $this->assertEquals('testinstall2', $module->getName());
        $this->assertTrue($module->isInstalled());
        $this->assertTrue($module->canBeInstalled());
        $this->assertEquals('2.0', $module->getCurrentVersion());
        $this->assertEquals('2.5.1', $module->getNextVersion());
        $this->assertEquals(Resolver::ACTION_UPGRADE, $module->getAction());
        $dependencies = $module->getDependencies();
        $this->assertEquals(1, count($dependencies));
        $this->assertTrue(isset($dependencies['jelix']));
        $this->assertEquals([], $module->getAlternativeDependencies());
    }
}
