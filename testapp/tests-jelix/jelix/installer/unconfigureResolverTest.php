<?php


use Jelix\Installer\ModuleStatus;
use Jelix\Core\Infos\ModuleInfos;
use Jelix\Dependencies\Resolver;
use Jelix\Dependencies\ItemException;

class unconfigureResolverTest extends \Jelix\UnitTests\UnitTestCase
{

    function testUnconfigureNoModules()
    {
        $resolver = new \Jelix\Installer\InstallationResolver();
        $modules = $resolver->getItemsToUnConfigure([], []);
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
            'testinstall1.enabled' => true,
            'testinstall1.version' => '1.0',
            'testinstall1.dbprofile' => '',
            'testinstall1.localconf' => false,
        ], true);
        $modStatusTestinstall2 = new ModuleStatus('testinstall2', '/', [
            'testinstall2.installed' => true,
            'testinstall2.enabled' => true,
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

    function testGetItemsToUnconfigure()
    {
        $resolver = new \Jelix\Installer\InstallationResolver();
        $componentsList = $this->getComponentsList();

        // unconfigure one module that is unconfigured
        $modules = $resolver->getItemsToUnConfigure($componentsList, ['testinstall3']);
        $this->assertEquals(0, count($modules));

        // unconfigure one module
        $modules = $resolver->getItemsToUnConfigure($componentsList, ['testinstall2']);
        $this->assertEquals(1, count($modules));
        $module = $modules[0];
        $this->assertEquals('testinstall2', $module->getName());
        $this->assertTrue($module->isInstalled());
        $this->assertTrue($module->canBeInstalled());
        $this->assertEquals('2.0', $module->getCurrentVersion());
        $this->assertEquals(null, $module->getNextVersion());
        $this->assertEquals(3, $module->getAction());
        $dependencies = $module->getDependencies();
        $this->assertEquals(1, count($dependencies));
        $this->assertTrue(isset($dependencies['jelix']));
        $this->assertEquals([], $module->getAlternativeDependencies());

        // unconfigure two modules
        $modules = $resolver->getItemsToUnConfigure($componentsList, ['testinstall2', 'testinstall1']);
        $this->assertEquals(2, count($modules));

        $module = $modules[0];
        $this->assertEquals('testinstall2', $module->getName());
        $this->assertTrue($module->isInstalled());
        $this->assertTrue($module->canBeInstalled());
        $this->assertEquals('2.0', $module->getCurrentVersion());
        $this->assertEquals(null, $module->getNextVersion());
        $this->assertEquals(3, $module->getAction());
        $dependencies = $module->getDependencies();
        $this->assertEquals(1, count($dependencies));
        $this->assertTrue(isset($dependencies['jelix']));
        $this->assertEquals([], $module->getAlternativeDependencies());

        $module = $modules[1];
        $this->assertEquals('testinstall1', $module->getName());
        $this->assertTrue($module->isInstalled());
        $this->assertTrue($module->canBeInstalled());
        $this->assertEquals('1.0', $module->getCurrentVersion());
        $this->assertEquals(null, $module->getNextVersion());
        $this->assertEquals(3, $module->getAction());
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
        $componentsList['testinstall3'][0]->dependencies[] =
            array('name' => 'testinstall2', 'type' => 'module', 'version' => '2.0.*', 'optional' => false)
        ;

        // configure no modules
        $modules = $resolver->getItemsToUnConfigure($componentsList, []);
        $this->assertEquals([], $modules);

        $modules = $resolver->getItemsToUnConfigure($componentsList, ['testinstall3']);
        $this->assertEquals([], $modules);

        // configure one module
        $modules = $resolver->getItemsToUnConfigure($componentsList, ['testinstall1']);
        $this->assertEquals(1, count($modules));
        $module = $modules[0];
        $this->assertEquals('testinstall1', $module->getName());
        $this->assertTrue($module->isInstalled());
        $this->assertTrue($module->canBeInstalled());
        $this->assertEquals('1.0', $module->getCurrentVersion());
        $this->assertEquals(null, $module->getNextVersion());
        $this->assertEquals(3, $module->getAction());
        $dependencies = $module->getDependencies();
        $this->assertEquals(2, count($dependencies));
        $this->assertTrue(isset($dependencies['jelix']));
        $this->assertTrue(isset($dependencies['testinstall2']));
        $this->assertEquals([], $module->getAlternativeDependencies());

        // configure two modules
        $modules = $resolver->getItemsToUnConfigure($componentsList, ['testinstall2']);
        $this->assertEquals(2, count($modules));

        $module = $modules[1];
        $this->assertEquals('testinstall2', $module->getName());
        $this->assertTrue($module->isInstalled());
        $this->assertTrue($module->canBeInstalled());
        $this->assertEquals('2.0', $module->getCurrentVersion());
        $this->assertEquals(null, $module->getNextVersion());
        $this->assertEquals(3, $module->getAction());
        $dependencies = $module->getDependencies();
        $this->assertEquals(1, count($dependencies));
        $this->assertTrue(isset($dependencies['jelix']));
        $this->assertEquals([], $module->getAlternativeDependencies());

        $module = $modules[0];
        $this->assertEquals('testinstall1', $module->getName());
        $this->assertTrue($module->isInstalled());
        $this->assertTrue($module->canBeInstalled());
        $this->assertEquals('1.0', $module->getCurrentVersion());
        $this->assertEquals(null, $module->getNextVersion());
        $this->assertEquals(3, $module->getAction());
        $dependencies = $module->getDependencies();
        $this->assertEquals(2, count($dependencies));
        $this->assertTrue(isset($dependencies['jelix']));
        $this->assertTrue(isset($dependencies['testinstall2']));
        $this->assertEquals([], $module->getAlternativeDependencies());
    }


    function testUnconfigureModulesToUpgrade()
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

        // configure no modules
        $modules = $resolver->getItemsToUnConfigure($componentsList, []);
        $this->assertEquals([], $modules);

        // configure one module
        $modules = $resolver->getItemsToUnConfigure($componentsList, ['testinstall1']);
        $this->assertEquals(1, count($modules));
        $module = $modules[0];
        $this->assertEquals('testinstall1', $module->getName());
        $this->assertTrue($module->isInstalled());
        $this->assertTrue($module->canBeInstalled());
        $this->assertEquals('1.0', $module->getCurrentVersion());
        $this->assertEquals(null, $module->getNextVersion());
        $this->assertEquals(3, $module->getAction());
        $dependencies = $module->getDependencies();
        $this->assertEquals(2, count($dependencies));
        $this->assertTrue(isset($dependencies['jelix']));
        $this->assertTrue(isset($dependencies['testinstall2']));
        $this->assertEquals([], $module->getAlternativeDependencies());

        // configure two modules
        $modules = $resolver->getItemsToUnConfigure($componentsList, ['testinstall1', 'testinstall2']);
        $this->assertEquals(2, count($modules));

        $module = $modules[1];
        $this->assertEquals('testinstall2', $module->getName());
        $this->assertTrue($module->isInstalled());
        $this->assertTrue($module->canBeInstalled());
        $this->assertEquals('2.0', $module->getCurrentVersion());
        $this->assertEquals(null, $module->getNextVersion());
        $this->assertEquals(3, $module->getAction());
        $dependencies = $module->getDependencies();
        $this->assertEquals(1, count($dependencies));
        $this->assertTrue(isset($dependencies['jelix']));
        $this->assertEquals([], $module->getAlternativeDependencies());

        $module = $modules[0];
        $this->assertEquals('testinstall1', $module->getName());
        $this->assertTrue($module->isInstalled());
        $this->assertTrue($module->canBeInstalled());
        $this->assertEquals('1.0', $module->getCurrentVersion());
        $this->assertEquals(null, $module->getNextVersion());
        $this->assertEquals(3, $module->getAction());
        $dependencies = $module->getDependencies();
        $this->assertEquals(2, count($dependencies));
        $this->assertTrue(isset($dependencies['jelix']));
        $this->assertTrue(isset($dependencies['testinstall2']));
        $this->assertEquals([], $module->getAlternativeDependencies());

    }

    function testUnconfigureModulesToUpgradeFail()
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


        $this->expectException(ItemException::class);
        $this->expectExceptionCode(ItemException::ERROR_ITEM_TO_INSTALL_SHOULD_BE_REMOVED);

        $modules = $resolver->getItemsToUnConfigure($componentsList, ['testinstall2']);
    }
}
