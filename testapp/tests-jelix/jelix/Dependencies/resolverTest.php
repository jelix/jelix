<?php
/**
* @author      Laurent Jouanneau
* @copyright   2016 Laurent Jouanneau
* @link        http://jelix.org
* @licence     MIT
*/

use Jelix\Dependencies\Resolver;
use Jelix\Dependencies\Item;

class resolverTest extends \PHPUnit\Framework\TestCase {

    /**
     *
     */
    public function testOneItemNoDeps() {
        $packA = new Item('testA', "1.0", false);
        $packA->setAction(Resolver::ACTION_NONE);
        $resolver = new Resolver();
        $resolver->addItem($packA);
        $chain = $resolver->getDependenciesChainForInstallation();
        $this->assertEquals(array(), $chain);

        $packA->setAction(Resolver::ACTION_INSTALL);
        $chain = $resolver->getDependenciesChainForInstallation();
        $this->assertEquals(1, count($chain));
        $this->assertEquals('testA', $chain[0]->getName());
        $this->assertEquals(Resolver::ACTION_INSTALL, $chain[0]->getAction());
    }

    public function testTwoDependItems() {
        $packA = new Item('testA', "1.0", false);
        $packA->setAction(Resolver::ACTION_INSTALL);
        $packA->addDependency('testB', '1.0.*');
        $packB = new Item('testB', "1.0", false);
        $packB->setAction(Resolver::ACTION_NONE);
        $packC = new Item('testC', "1.0", false);
        $packC->setAction(Resolver::ACTION_NONE);

        $resolver = new Resolver();
        $resolver->addItem($packA);
        $resolver->addItem($packB);
        $resolver->addItem($packC);
        $chain = $resolver->getDependenciesChainForInstallation();

        $this->assertEquals(2, count($chain));
        $this->assertEquals('testB', $chain[0]->getName());
        $this->assertEquals(Resolver::ACTION_INSTALL, $chain[0]->getAction());
        $this->assertEquals('testA', $chain[1]->getName());
        $this->assertEquals(Resolver::ACTION_INSTALL, $chain[1]->getAction());
    }

    public function testUpgrade() {
        $packA = new Item('testA', "1.0", false);
        $packA->setAction(Resolver::ACTION_INSTALL);
        $packA->addDependency('testB', '1.0.*');
        $packB = new Item('testB', "1.0", false);
        $packB->setAction(Resolver::ACTION_NONE);
        $packC = new Item('testC', "1.0", true);
        $packC->setAction(Resolver::ACTION_UPGRADE, "1.1");

        $resolver = new Resolver();
        $resolver->addItem($packA);
        $resolver->addItem($packB);
        $resolver->addItem($packC);
        $chain = $resolver->getDependenciesChainForInstallation();

        $this->assertEquals(3, count($chain));
        $this->assertEquals('testB', $chain[0]->getName());
        $this->assertEquals(Resolver::ACTION_INSTALL, $chain[0]->getAction());
        $this->assertEquals('testA', $chain[1]->getName());
        $this->assertEquals(Resolver::ACTION_INSTALL, $chain[1]->getAction());
        $this->assertEquals('testC', $chain[2]->getName());
        $this->assertEquals(Resolver::ACTION_UPGRADE, $chain[2]->getAction());
    }

    public function testUpgradeWithLowerVersion() {
        $packA = new Item('testA', "1.0", false);
        $packA->setAction(Resolver::ACTION_INSTALL);
        $packA->addDependency('testB', '1.0.*');
        $packB = new Item('testB', "1.0", false);
        $packB->setAction(Resolver::ACTION_NONE);
        $packC = new Item('testC', "1.1", true);
        $packC->setAction(Resolver::ACTION_UPGRADE, "1.0");

        $resolver = new Resolver();
        $resolver->addItem($packA);
        $resolver->addItem($packB);
        $resolver->addItem($packC);
        $chain = $resolver->getDependenciesChainForInstallation();

        $this->assertEquals(3, count($chain));
        $this->assertEquals('testB', $chain[0]->getName());
        $this->assertEquals(Resolver::ACTION_INSTALL, $chain[0]->getAction());
        $this->assertEquals('testA', $chain[1]->getName());
        $this->assertEquals(Resolver::ACTION_INSTALL, $chain[1]->getAction());
        $this->assertEquals('testC', $chain[2]->getName());
        $this->assertEquals(Resolver::ACTION_UPGRADE, $chain[2]->getAction());
    }

    /**
     */
    public function testTwoDependItemsNoForceInstall() {
        $packA = new Item('testA', "1.0", false);
        $packA->setAction(Resolver::ACTION_INSTALL);
        $packA->addDependency('testB', '1.0.*');
        $packB = new Item('testB', "1.0", false);
        $packB->setAction(Resolver::ACTION_NONE);
        $packC = new Item('testC', "1.0", false);
        $packC->setAction(Resolver::ACTION_NONE);

        $resolver = new Resolver();
        $resolver->addItem($packA);
        $resolver->addItem($packB);
        $resolver->addItem($packC);
        $this->expectException(\Jelix\Dependencies\ItemException::class);
        $this->expectExceptionCode(11);
        $chain = $resolver->getDependenciesChainForInstallation(false);

    }


    public function testTwoDependItemsForceInstall() {
        $packA = new Item('testA', "1.0", true);
        $packA->setAction(Resolver::ACTION_INSTALL);
        $packA->addDependency('testB', '1.0.*');
        $packB = new Item('testB', "1.0", false);
        $packB->setAction(Resolver::ACTION_NONE);
        $packC = new Item('testC', "1.0", false);
        $packC->setAction(Resolver::ACTION_NONE);

        $resolver = new Resolver();
        $resolver->addItem($packA);
        $resolver->addItem($packB);
        $resolver->addItem($packC);
        $chain = $resolver->getDependenciesChainForInstallation(true);

        $this->assertEquals(2, count($chain));
        $this->assertEquals('testB', $chain[0]->getName());
        $this->assertEquals(Resolver::ACTION_INSTALL, $chain[0]->getAction());
        $this->assertEquals('testA', $chain[1]->getName());
        $this->assertEquals(Resolver::ACTION_INSTALL, $chain[1]->getAction());
    }


    public function testTwoDependItemsForceReinstall() {
        $packA = new Item('testA', "1.0", true);
        $packA->setAction(Resolver::ACTION_INSTALL);
        $packA->addDependency('testB', '1.0.*');
        $packB = new Item('testB', "1.0", true);
        $packB->setAction(Resolver::ACTION_NONE);
        $packC = new Item('testC', "1.0", true);
        $packC->setAction(Resolver::ACTION_NONE);

        $resolver = new Resolver();
        $resolver->addItem($packA);
        $resolver->addItem($packB);
        $resolver->addItem($packC);
        $chain = $resolver->getDependenciesChainForInstallation(true);

        $this->assertEquals(1, count($chain));
        $this->assertEquals('testA', $chain[0]->getName());
        $this->assertEquals(Resolver::ACTION_INSTALL, $chain[0]->getAction());
    }

    /**
     */
    public function testForbidInstallDependencies() {
        $packA = new Item('testA', "1.0", false);
        $packA->setAction(Resolver::ACTION_INSTALL);
        $packA->addDependency('testB', '1.0.*');
        $packB = new Item('testB', "1.0", false);
        $packB->setAction(Resolver::ACTION_NONE);
        $packC = new Item('testC', "1.0", false);
        $packC->setAction(Resolver::ACTION_NONE);

        $resolver = new Resolver();
        $resolver->addItem($packA);
        $resolver->addItem($packB);
        $resolver->addItem($packC);
        $this->expectException(\Jelix\Dependencies\ItemException::class);
        $this->expectExceptionCode(11);

        $chain = $resolver->getDependenciesChainForInstallation(false);
    }


    /**
     */
    public function testInstallDependenciesThatCannotBeInstalled() {
        $packA = new Item('testA', "1.0", false);
        $packA->setAction(Resolver::ACTION_INSTALL);
        $packA->addDependency('testB', '1.0.*');
        $packB = new Item('testB', "1.0", false, false);
        $packB->setAction(Resolver::ACTION_NONE);
        $packC = new Item('testC', "1.0", false);
        $packC->setAction(Resolver::ACTION_NONE);

        $resolver = new Resolver();
        $resolver->addItem($packA);
        $resolver->addItem($packB);
        $resolver->addItem($packC);
        $this->expectException(\Jelix\Dependencies\ItemException::class);
        $this->expectExceptionCode(11);

        $chain = $resolver->getDependenciesChainForInstallation();
    }

    /**
     */
    public function testCircularDependencies() {
        $packA = new Item('testA', "1.0", false);
        $packA->setAction(Resolver::ACTION_INSTALL);
        $packA->addDependency('testB', '1.0.*');
        $packB = new Item('testB', "1.0", false);
        $packB->setAction(Resolver::ACTION_NONE);
        $packB->addDependency('testC', '1.0.*');
        $packC = new Item('testC', "1.0", false);
        $packC->setAction(Resolver::ACTION_NONE);
        $packC->addDependency('testA', '1.0.*');

        $resolver = new Resolver();
        $resolver->addItem($packA);
        $resolver->addItem($packB);
        $resolver->addItem($packC);
        $this->expectException(\Jelix\Dependencies\ItemException::class);
        $this->expectExceptionCode(1);

        $chain = $resolver->getDependenciesChainForInstallation();
    }

    public function testComplexInstallDependencies() {
        $packA = new Item('testA', "1.0", false);
        $packA->setAction(Resolver::ACTION_INSTALL);
        $packA->addDependency('testB');
        $packA->addDependency('testC');
        $packB = new Item('testB', "1.0", false);
        $packB->setAction(Resolver::ACTION_NONE);
        $packC = new Item('testC', "1.0", false);
        $packC->setAction(Resolver::ACTION_NONE);


        $packD = new Item('testD', "1.0", false);
        $packD->setAction(Resolver::ACTION_INSTALL);
        $packD->addDependency('testB');
        $packD->addDependency('testE');
        $packE = new Item('testE', "1.0", false);
        $packE->setAction(Resolver::ACTION_NONE);

        $resolver = new Resolver();
        $resolver->addItem($packA);
        $resolver->addItem($packB);
        $resolver->addItem($packC);
        $resolver->addItem($packD);
        $resolver->addItem($packE);
        $chain = $resolver->getDependenciesChainForInstallation();

        $this->assertEquals(5, count($chain));
        $this->assertEquals('testB', $chain[0]->getName());
        $this->assertEquals(Resolver::ACTION_INSTALL, $chain[0]->getAction());
        $this->assertEquals('testC', $chain[1]->getName());
        $this->assertEquals(Resolver::ACTION_INSTALL, $chain[1]->getAction());
        $this->assertEquals('testA', $chain[2]->getName());
        $this->assertEquals(Resolver::ACTION_INSTALL, $chain[2]->getAction());
        $this->assertEquals('testE', $chain[3]->getName());
        $this->assertEquals(Resolver::ACTION_INSTALL, $chain[3]->getAction());
        $this->assertEquals('testD', $chain[4]->getName());
        $this->assertEquals(Resolver::ACTION_INSTALL, $chain[4]->getAction());
    }

    public function testRemoveOneItemNoDeps() {
        $packA = new Item('testA', "1.0", true);
        $packA->setAction(Resolver::ACTION_REMOVE);
        $resolver = new Resolver();
        $resolver->addItem($packA);
        $chain = $resolver->getDependenciesChainForInstallation();

        $this->assertEquals(1, count($chain));
        $this->assertEquals('testA', $chain[0]->getName());
        $this->assertEquals(Resolver::ACTION_REMOVE, $chain[0]->getAction());
    }

    public function testRemoveUninstalledItem() {
        $packA = new Item('testA', "1.0", false);
        $packA->setAction(Resolver::ACTION_REMOVE);
        $resolver = new Resolver();
        $resolver->addItem($packA);
        $chain = $resolver->getDependenciesChainForInstallation();

        $this->assertEquals(0, count($chain));
    }


    public function testRemoveOneDependItems() {
        $packA = new Item('testA', "1.0", true);
        $packA->setAction(Resolver::ACTION_REMOVE);
        $packA->addDependency('testB', '1.0.*');

        $packB = new Item('testB', "1.0", false);
        $packB->setAction(Resolver::ACTION_NONE);
        $packC = new Item('testC', "1.0", true);
        $packC->setAction(Resolver::ACTION_NONE);

        $resolver = new Resolver();
        $resolver->addItem($packA);
        $resolver->addItem($packB);
        $resolver->addItem($packC);
        $chain = $resolver->getDependenciesChainForInstallation();

        $this->assertEquals(1, count($chain));
        $this->assertEquals('testA', $chain[0]->getName());
        $this->assertEquals(Resolver::ACTION_REMOVE, $chain[0]->getAction());
    }

    public function testRemoveOneAncesterDependItems() {
        $packA = new Item('testA', "1.0", true);
        $packA->setAction(Resolver::ACTION_REMOVE);
        $packB = new Item('testB', "1.0", true);
        $packB->setAction(Resolver::ACTION_NONE);
        $packB->addDependency('testA', '1.0.*');
        $packC = new Item('testC', "1.0", false);
        $packC->setAction(Resolver::ACTION_NONE);

        $resolver = new Resolver();
        $resolver->addItem($packA);
        $resolver->addItem($packB);
        $resolver->addItem($packC);
        $chain = $resolver->getDependenciesChainForInstallation();

        $this->assertEquals(2, count($chain));
        $this->assertEquals('testB', $chain[0]->getName());
        $this->assertEquals(Resolver::ACTION_REMOVE, $chain[0]->getAction());
        $this->assertEquals('testA', $chain[1]->getName());
        $this->assertEquals(Resolver::ACTION_REMOVE, $chain[1]->getAction());
    }

    public function testRemoveOneAncesterAltDependItems() {
        $packA = new Item('testA', "1.0", true);
        $packA->setAction(Resolver::ACTION_REMOVE);
        $packB = new Item('testB', "1.0", true);
        $packB->setAction(Resolver::ACTION_NONE);
        $packB->addDependency('testA', '1.0.*');
        $packC = new Item('testC', "1.0", true);
        $packC->setAction(Resolver::ACTION_NONE);
        $packC->addAlternativeDependencies(array('testA' => '1.0.*', 'testD' => '1.0.*'));
        $packD = new Item('testD', false, "1.0", Resolver::ACTION_NONE);

        $resolver = new Resolver();
        $resolver->addItem($packA);
        $resolver->addItem($packB);
        $resolver->addItem($packC);
        $resolver->addItem($packD);
        $chain = $resolver->getDependenciesChainForInstallation();

        $this->assertEquals(3, count($chain));
        $this->assertEquals('testB', $chain[0]->getName());
        $this->assertEquals(Resolver::ACTION_REMOVE, $chain[0]->getAction());
        $this->assertEquals('testC', $chain[1]->getName());
        $this->assertEquals(Resolver::ACTION_REMOVE, $chain[1]->getAction());
        $this->assertEquals('testA', $chain[2]->getName());
        $this->assertEquals(Resolver::ACTION_REMOVE, $chain[2]->getAction());
    }

    public function testRemoveOneAncesterAltDependCascadeItems() {
        $packA = new Item('testA', "1.0", true);
        $packA->setAction(Resolver::ACTION_REMOVE);
        $packB = new Item('testB', "1.0", true);
        $packB->setAction(Resolver::ACTION_NONE);
        $packB->addDependency('testA', '1.0.*');
        $packC = new Item('testC', "1.0", true);
        $packC->setAction(Resolver::ACTION_NONE);
        $packC->addAlternativeDependencies(array('testB' => '1.0.*', 'testD' => '1.0.*'));
        $packD = new Item('testD', false, "1.0", Resolver::ACTION_NONE);

        $resolver = new Resolver();
        $resolver->addItem($packA);
        $resolver->addItem($packB);
        $resolver->addItem($packC);
        $resolver->addItem($packD);
        $chain = $resolver->getDependenciesChainForInstallation();

        $this->assertEquals(3, count($chain));
        $this->assertEquals('testC', $chain[0]->getName());
        $this->assertEquals(Resolver::ACTION_REMOVE, $chain[0]->getAction());
        $this->assertEquals('testB', $chain[1]->getName());
        $this->assertEquals(Resolver::ACTION_REMOVE, $chain[1]->getAction());
        $this->assertEquals('testA', $chain[2]->getName());
        $this->assertEquals(Resolver::ACTION_REMOVE, $chain[2]->getAction());
    }

    /**
     */
    public function testRemoveOneAncesterToInstallDependItems() {
        $packA = new Item('testA', "1.0", true);
        $packA->setAction(Resolver::ACTION_REMOVE);
        $packB = new Item('testB', "1.0", false);
        $packB->setAction(Resolver::ACTION_INSTALL);
        $packB->addDependency('testA', '1.0.*');
        $packC = new Item('testC', "1.0", false);
        $packC->setAction(Resolver::ACTION_NONE);

        $resolver = new Resolver();
        $resolver->addItem($packA);
        $resolver->addItem($packB);
        $resolver->addItem($packC);
        $this->expectException(\Jelix\Dependencies\ItemException::class);
        $this->expectExceptionCode(5);

        $chain = $resolver->getDependenciesChainForInstallation();
    }

    /**
     */
    public function testRemoveCircularDependencies() {
        $packA = new Item('testA', "1.0", true);
        $packA->setAction(Resolver::ACTION_REMOVE);
        $packA->addDependency('testC', '1.0.*');
        $packB = new Item('testB', "1.0", true);
        $packB->setAction(Resolver::ACTION_NONE);
        $packB->addDependency('testA', '1.0.*');
        $packC = new Item('testC', "1.0", true);
        $packC->setAction(Resolver::ACTION_NONE);
        $packC->addDependency('testB', '1.0.*');

        $resolver = new Resolver();
        $resolver->addItem($packA);
        $resolver->addItem($packB);
        $resolver->addItem($packC);
        $this->expectException(\Jelix\Dependencies\ItemException::class);
        $this->expectExceptionCode(4);

        $chain = $resolver->getDependenciesChainForInstallation();
    }

    public function testInstallRemove() {
        $packA = new Item('testA', "1.0", false);
        $packA->setAction(Resolver::ACTION_INSTALL);
        $packA->addDependency('testB');
        $packB = new Item('testB', "1.0", false);
        $packB->setAction(Resolver::ACTION_NONE);
        $packC = new Item('testC', "1.0", true);
        $packC->setAction(Resolver::ACTION_NONE);
        $packC->addDependency('testD');
        $packD = new Item('testD', "1.0", true);
        $packD->setAction(Resolver::ACTION_REMOVE);
        $packE = new Item('testE', "1.0", true);
        $packE->setAction(Resolver::ACTION_NONE);

        $resolver = new Resolver();
        $resolver->addItem($packE);
        $resolver->addItem($packA);
        $resolver->addItem($packD);
        $resolver->addItem($packB);
        $resolver->addItem($packC);
        $chain = $resolver->getDependenciesChainForInstallation();

        $this->assertEquals(4, count($chain));
        $this->assertEquals('testB', $chain[0]->getName());
        $this->assertEquals(Resolver::ACTION_INSTALL, $chain[0]->getAction());
        $this->assertEquals('testA', $chain[1]->getName());
        $this->assertEquals(Resolver::ACTION_INSTALL, $chain[1]->getAction());
        $this->assertEquals('testC', $chain[2]->getName());
        $this->assertEquals(Resolver::ACTION_REMOVE, $chain[2]->getAction());
        $this->assertEquals('testD', $chain[3]->getName());
        $this->assertEquals(Resolver::ACTION_REMOVE, $chain[3]->getAction());
    }


    /**
     *
     */
    public function testNoConflictItems() {
        $packA = new Item('testA', "1.0", false);
        $packA->setAction(Resolver::ACTION_INSTALL);
        $packB = new Item('testB', "1.0", false);
        $packB->setAction(Resolver::ACTION_NONE);
        $packA->addIncompatibility('testB', '*');
        $packC = new Item('testC', "1.0", false);
        $packC->setAction(Resolver::ACTION_NONE);

        $resolver = new Resolver();
        $resolver->addItem($packA);
        $resolver->addItem($packB);
        $resolver->addItem($packC);
        $chain = $resolver->getDependenciesChainForInstallation();
        $this->assertEquals(array($packA), $chain);
    }

    /**
     *
     */
    public function testConflictItems() {
        $packA = new Item('testA', "1.0", false);
        $packA->setAction(Resolver::ACTION_INSTALL);
        $packB = new Item('testB', "1.0", false);
        $packB->setAction(Resolver::ACTION_INSTALL);
        $packA->addIncompatibility('testB', '*');
        $packC = new Item('testC', "1.0", false);
        $packC->setAction(Resolver::ACTION_NONE);

        $resolver = new Resolver();
        $resolver->addItem($packA);
        $resolver->addItem($packB);
        $resolver->addItem($packC);
        $this->expectException(\Jelix\Dependencies\ItemException::class);
        $this->expectExceptionCode(8);
        $this->expectExceptionMessage('Item testB is in conflicts with item testA');

        $chain = $resolver->getDependenciesChainForInstallation();
    }

    /**
     *
     */
    public function testConflictItemAlreadyInstalled() {
        $packA = new Item('testA', "1.0", false);
        $packA->setAction(Resolver::ACTION_INSTALL);
        $packA->addIncompatibility('testB', '*');
        $packB = new Item('testB', "1.0", true);
        $packB->setAction(Resolver::ACTION_NONE);
        $packC = new Item('testC', "1.0", false);
        $packC->setAction(Resolver::ACTION_NONE);

        $resolver = new Resolver();
        $resolver->addItem($packA);
        $resolver->addItem($packB);
        $resolver->addItem($packC);
        $this->expectException(\Jelix\Dependencies\ItemException::class);
        $this->expectExceptionCode(7);
        $this->expectExceptionMessage('Item testB is in conflicts with item testA');
        $chain = $resolver->getDependenciesChainForInstallation();
    }


    /**
     *
     */
    public function testNoConflictWithRemovedItem() {
        $packA = new Item('testA', "1.0", false);
        $packA->setAction(Resolver::ACTION_INSTALL);
        $packA->addIncompatibility('testB', '*');
        $packB = new Item('testB', "1.0", true);
        $packB->setAction(Resolver::ACTION_REMOVE);
        $packC = new Item('testC', "1.0", false);
        $packC->setAction(Resolver::ACTION_NONE);

        $resolver = new Resolver();
        $resolver->addItem($packA);
        $resolver->addItem($packB);
        $resolver->addItem($packC);
        $chain = $resolver->getDependenciesChainForInstallation();
        $this->assertEquals(array($packA, $packB), $chain);
    }

    public function testChoiceOneItemInstalled() {
        $packA = new Item('testA', "1.0", false);
        $packA->setAction(Resolver::ACTION_INSTALL);
        $packA->addAlternativeDependencies(array(
            'testB'=>'1.0.*',
            'testC'=>'1.0.*',
            )
        );

        $packB = new Item('testB', "1.0", true);
        $packB->setAction(Resolver::ACTION_NONE);
        $packC = new Item('testC', "1.0", false);
        $packC->setAction(Resolver::ACTION_NONE);
        $packD = new Item('testD', "1.0", false);
        $packD->setAction(Resolver::ACTION_INSTALL);

        $resolver = new Resolver();
        $resolver->addItem($packA);
        $resolver->addItem($packB);
        $resolver->addItem($packC);
        $resolver->addItem($packD);
        $chain = $resolver->getDependenciesChainForInstallation();

        $this->assertEquals(2, count($chain));
        $this->assertEquals('testA', $chain[0]->getName());
        $this->assertEquals(Resolver::ACTION_INSTALL, $chain[0]->getAction());
        $this->assertEquals('testD', $chain[1]->getName());
        $this->assertEquals(Resolver::ACTION_INSTALL, $chain[1]->getAction());
    }

    public function testChoiceOneItemToInstall() {
        $packA = new Item('testA', "1.0", false);
        $packA->setAction(Resolver::ACTION_INSTALL);
        $packA->addAlternativeDependencies(array(
                'testB'=>'1.0.*',
                'testC'=>'1.0.*',
            )
        );

        $packB = new Item('testB', "1.0", false);
        $packB->setAction(Resolver::ACTION_NONE);
        $packB->addDependency('testD');
        //$packC = new Item('testC', "1.0", false);
        //$packC->setAction(Resolver::ACTION_NONE);
        $packD = new Item('testD', "1.0", false);
        $packD->setAction(Resolver::ACTION_NONE);

        $resolver = new Resolver();
        $resolver->addItem($packA);
        $resolver->addItem($packB);
        //$resolver->addItem($packC);
        $resolver->addItem($packD);
        $chain = $resolver->getDependenciesChainForInstallation();

        $this->assertEquals(3, count($chain));
        $this->assertEquals('testD', $chain[0]->getName());
        $this->assertEquals(Resolver::ACTION_INSTALL, $chain[0]->getAction());
        $this->assertEquals('testB', $chain[1]->getName());
        $this->assertEquals(Resolver::ACTION_INSTALL, $chain[1]->getAction());
        $this->assertEquals('testA', $chain[2]->getName());
        $this->assertEquals(Resolver::ACTION_INSTALL, $chain[2]->getAction());
    }

    /**
     *
     */
    public function testChoiceAmbigusItems() {
        $packA = new Item('testA', "1.0", false);
        $packA->setAction(Resolver::ACTION_INSTALL);
        $packA->addAlternativeDependencies(array(
                'testB'=>'1.0.*',
                'testC'=>'1.0.*',
                'testE'=>'1.0.*'
            )
        );

        $packB = new Item('testB', "1.0", false);
        $packB->setAction(Resolver::ACTION_NONE);
        $packB->addDependency('testD');
        $packC = new Item('testC', "1.0", false);
        $packC->setAction(Resolver::ACTION_NONE);
        $packD = new Item('testD', "1.0", false);
        $packD->setAction(Resolver::ACTION_NONE);

        $resolver = new Resolver();
        $resolver->addItem($packA);
        $resolver->addItem($packB);
        $resolver->addItem($packC);
        $resolver->addItem($packD);
        $this->expectException(\Jelix\Dependencies\ItemException::class);
        $this->expectExceptionCode(10);
        $this->expectExceptionMessage('Item testA depends on alternative items but there are ambiguities to choose them. Installed one of them before installing it.');
        $chain = $resolver->getDependenciesChainForInstallation();
    }

    /**
     *
     */
    public function testChoiceBadVersionItem() {
        $packA = new Item('testA', "1.0", false);
        $packA->setAction(Resolver::ACTION_INSTALL);
        $packA->addAlternativeDependencies(array(
                'testB'=>'1.1.*',
                'testC'=>'1.2.*',
            )
        );

        $packB = new Item('testB', "1.0", false);
        $packB->setAction(Resolver::ACTION_NONE);
        $packB->addDependency('testD');
        //$packC = new Item('testC', "1.0", false);
        //$packC->setAction(Resolver::ACTION_NONE);
        $packD = new Item('testD', "1.0", false);
        $packD->setAction(Resolver::ACTION_NONE);

        $resolver = new Resolver();
        $resolver->addItem($packA);
        $resolver->addItem($packB);
        //$resolver->addItem($packC);
        $resolver->addItem($packD);
        $this->expectException(\Jelix\Dependencies\ItemException::class);
        $this->expectExceptionCode(9);
        $this->expectExceptionMessage('Item testA depends on alternative items but there are unknown or do not met installation criterias. Install or upgrade one of them before installing it');

        $chain = $resolver->getDependenciesChainForInstallation();
    }


    /**
     *
     */
    public function testChoiceUnknownItems() {
        $packA = new Item('testA', "1.0", false);
        $packA->setAction(Resolver::ACTION_INSTALL);
        $packA->addAlternativeDependencies(array(
                'testB'=>'1.1.*',
                'testC'=>'1.2.*',
            )
        );

        //$packB = new Item('testB', "1.0", false);
        //$packA->setAction(Resolver::ACTION_NONE);
        //$packB->addDependency('testD');
        //$packC = new Item('testC', "1.0", false);
        //$packC->setAction(Resolver::ACTION_NONE);
        $packD = new Item('testD', "1.0", false);
        $packD->setAction(Resolver::ACTION_NONE);

        $resolver = new Resolver();
        $resolver->addItem($packA);
        //$resolver->addItem($packB);
        //$resolver->addItem($packC);
        $resolver->addItem($packD);
        $this->expectException(\Jelix\Dependencies\ItemException::class);
        $this->expectExceptionCode(9);
        $this->expectExceptionMessage('Item testA depends on alternative items but there are unknown or do not met installation criterias. Install or upgrade one of them before installing it');

        $chain = $resolver->getDependenciesChainForInstallation();
    }

    /**
     *
     */
    public function testChoiceItemHasBadDependency() {
        $packA = new Item('testA', "1.0", false);
        $packA->setAction(Resolver::ACTION_INSTALL);
        $packA->addAlternativeDependencies(array(
                'testB'=>'1.0.*',
                'testC'=>'1.0.*',
            )
        );

        $packB = new Item('testB', "1.0", false);
        $packB->setAction(Resolver::ACTION_NONE);
        $packB->addDependency('testD');
        //$packC = new Item('testC', "1.0", false)
        //$packC->setAction(Resolver::ACTION_NONE);
        //$packD = new Item('testD', "1.0", false);
        //$packD->setAction(Resolver::ACTION_NONE);

        $resolver = new Resolver();
        $resolver->addItem($packA);
        $resolver->addItem($packB);
        //$resolver->addItem($packC);
        //$resolver->addItem($packD);
        $this->expectException(\Jelix\Dependencies\ItemException::class);
        $this->expectExceptionCode(6);
        $this->expectExceptionMessage('For item testB, some items are missing: testD');

        $chain = $resolver->getDependenciesChainForInstallation();
    }


    public function testOptionalDependencies() {
        /*
                A->B
                A->C
                D->B
                D->E optional
        */
        $packA = new Item('testA', "1.0", false);
        $packA->setAction(Resolver::ACTION_INSTALL);
        $packA->addDependency('testB');
        $packA->addDependency('testC');

        $packB = new Item('testB', "1.0", false);
        $packB->setAction(Resolver::ACTION_NONE);

        $packC = new Item('testC', "1.0", false);
        $packC->setAction(Resolver::ACTION_NONE);

        $packD = new Item('testD', "1.0", false);
        $packD->setAction(Resolver::ACTION_INSTALL);
        $packD->addDependency('testB');
        $packD->addDependency('testE', '*', true);

        $resolver = new Resolver();
        $resolver->addItem($packA);
        $resolver->addItem($packB);
        $resolver->addItem($packC);
        $resolver->addItem($packD);

        $chain = $resolver->getDependenciesChainForInstallation();

        $this->assertEquals(4, count($chain));
        $this->assertEquals('testB', $chain[0]->getName());
        $this->assertEquals(Resolver::ACTION_INSTALL, $chain[0]->getAction());
        $this->assertEquals('testC', $chain[1]->getName());
        $this->assertEquals(Resolver::ACTION_INSTALL, $chain[1]->getAction());
        $this->assertEquals('testA', $chain[2]->getName());
        $this->assertEquals(Resolver::ACTION_INSTALL, $chain[2]->getAction());
        $this->assertEquals('testD', $chain[3]->getName());
        $this->assertEquals(Resolver::ACTION_INSTALL, $chain[3]->getAction());
    }

    /**
     *
     */
    public function testOptionalDependenciesWithMissingDependency() {
        /*
                A->B optional and missing
                A->C
                D->B
                D->E optional
        */

        $packA = new Item('testA', "1.0", false);
        $packA->setAction(Resolver::ACTION_INSTALL);
        $packA->addDependency('testB', '*', true);
        $packA->addDependency('testC');

        $packC = new Item('testC', "1.0", false);
        $packC->setAction(Resolver::ACTION_NONE);

        $packD = new Item('testD', "1.0", false);
        $packD->setAction(Resolver::ACTION_INSTALL);
        $packD->addDependency('testB');
        $packD->addDependency('testE', '*', true);

        $resolver = new Resolver();
        $resolver->addItem($packA);
        $resolver->addItem($packC);
        $resolver->addItem($packD);

        $this->expectException(\Jelix\Dependencies\ItemException::class);
        $this->expectExceptionCode(6);
        $this->expectExceptionMessage('For item testD, some items are missing: testB');
        $chain = $resolver->getDependenciesChainForInstallation();
    }


    public function testTwoDependItemsInstallOneItem()
    {
        $packA = new Item('testA', "1.0", false);
        $packA->setAction(Resolver::ACTION_INSTALL);

        $packB = new Item('testB', "1.0", false);
        $packB->setAction(Resolver::ACTION_INSTALL);
        $packB->addDependency('testA', '1.0.*');

        $packC = new Item('testC', "1.0", false);
        $packC->setAction(Resolver::ACTION_NONE);

        $resolver = new Resolver();
        $resolver->addItem($packA);
        $resolver->addItem($packB);
        $resolver->addItem($packC);
        $chain = $resolver->getDependenciesChainForSpecificItems('testB', true);

        $this->assertEquals(2, count($chain));
        $this->assertEquals('testA', $chain[0]->getName());
        $this->assertEquals(Resolver::ACTION_INSTALL, $chain[0]->getAction());
        $this->assertEquals('testB', $chain[1]->getName());
        $this->assertEquals(Resolver::ACTION_INSTALL, $chain[1]->getAction());
    }

    public function testFourDependItemsInstallTwoItems()
    {
        $packA = new Item('testA', "1.0", false);
        $packA->setAction(Resolver::ACTION_INSTALL);

        $packB = new Item('testB', "1.0", false);
        $packB->setAction(Resolver::ACTION_INSTALL);
        $packB->addDependency('testA', '1.0.*');

        $packC = new Item('testC', "1.0", false);
        $packC->setAction(Resolver::ACTION_NONE);

        $packD = new Item('testD', "1.0", false);
        $packD->setAction(Resolver::ACTION_INSTALL);
        $packB->addDependency('testF', '1.0.*');

        $packE = new Item('testE', "1.0", false);
        $packE->setAction(Resolver::ACTION_INSTALL);

        $packF = new Item('testF', "1.0", false);
        $packF->setAction(Resolver::ACTION_NONE);

        $resolver = new Resolver();
        $resolver->addItem($packA);
        $resolver->addItem($packB);
        $resolver->addItem($packC);
        $resolver->addItem($packD);
        $resolver->addItem($packE);
        $resolver->addItem($packF);
        $chain = $resolver->getDependenciesChainForSpecificItems(['testB', 'testD'], true);

        $this->assertEquals(4, count($chain));
        $this->assertEquals('testA', $chain[0]->getName());
        $this->assertEquals(Resolver::ACTION_INSTALL, $chain[0]->getAction());
        $this->assertEquals('testF', $chain[1]->getName());
        $this->assertEquals(Resolver::ACTION_INSTALL, $chain[1]->getAction());
        $this->assertEquals('testB', $chain[2]->getName());
        $this->assertEquals(Resolver::ACTION_INSTALL, $chain[2]->getAction());
        $this->assertEquals('testD', $chain[3]->getName());
        $this->assertEquals(Resolver::ACTION_INSTALL, $chain[3]->getAction());
    }

    public function testOptionalFourDependItemsInstallTwoItems()
    {
        $packA = new Item('testA', "1.0", false);
        $packA->setAction(Resolver::ACTION_INSTALL);

        $packB = new Item('testB', "1.0", true);
        $packB->setAction(Resolver::ACTION_NONE);
        $packB->addDependency('testA', '1.0.*');

        $packC = new Item('testC', "1.0", false);
        $packC->setAction(Resolver::ACTION_NONE);

        $packD = new Item('testD', "1.0", false);
        $packD->setAction(Resolver::ACTION_INSTALL);
        $packB->addDependency('testF', '1.0.*');

        $packE = new Item('testE', "1.0", false);
        $packE->setAction(Resolver::ACTION_INSTALL);

        $packF = new Item('testF', "1.0", false);
        $packF->setAction(Resolver::ACTION_NONE);

        $resolver = new Resolver();
        $resolver->addItem($packA);
        $resolver->addItem($packB);
        $resolver->addItem($packC);
        $resolver->addItem($packD);
        $resolver->addItem($packE);
        $resolver->addItem($packF);
        $chain = $resolver->getDependenciesChainForSpecificItems(['testB', 'testD'], false);

        $this->assertEquals(1, count($chain));
        $this->assertEquals('testD', $chain[0]->getName());
        $this->assertEquals(Resolver::ACTION_INSTALL, $chain[0]->getAction());
    }

    public function testTwoDependItemsInstallOneItemOnlyIfNeeded()
    {
        $packA = new Item('testA', "1.0", true);
        $packA->setAction(Resolver::ACTION_NONE);

        $packB = new Item('testB', "1.0", true);
        $packB->setAction(Resolver::ACTION_NONE);
        $packB->addDependency('testA', '1.0.*');

        $packC = new Item('testC', "1.0", false);
        $packC->setAction(Resolver::ACTION_NONE);

        $resolver = new Resolver();
        $resolver->addItem($packA);
        $resolver->addItem($packB);
        $resolver->addItem($packC);
        $chain = $resolver->getDependenciesChainForSpecificItems('testB');

        $this->assertEquals(0, count($chain));
    }

    public function testTwoDependItemsInstallOneItemOnlyIfNeeded2()
    {
        $packA = new Item('testA', "1.0", true);
        $packA->setAction(Resolver::ACTION_NONE);

        $packB = new Item('testB', "1.0", false);
        $packB->setAction(Resolver::ACTION_NONE);
        $packB->addDependency('testA', '1.0.*');

        $packC = new Item('testC', "1.0", false);
        $packC->setAction(Resolver::ACTION_NONE);

        $resolver = new Resolver();
        $resolver->addItem($packA);
        $resolver->addItem($packB);
        $resolver->addItem($packC);
        $chain = $resolver->getDependenciesChainForSpecificItems('testB');

        $this->assertEquals(0, count($chain));
    }

    public function testInstallOneItemThatIsADependency()
    {
        $packA = new Item('testA', "1.0", false);
        $packA->setAction(Resolver::ACTION_INSTALL);

        $packB = new Item('testB', "1.0", false);
        $packB->setAction(Resolver::ACTION_INSTALL);
        $packB->addDependency('testA', '1.0.*');

        $packC = new Item('testC', "1.0", false);
        $packC->setAction(Resolver::ACTION_NONE);

        $resolver = new Resolver();
        $resolver->addItem($packA);
        $resolver->addItem($packB);
        $resolver->addItem($packC);
        $chain = $resolver->getDependenciesChainForSpecificItems('testA', true);

        $this->assertEquals(1, count($chain));
        $this->assertEquals('testA', $chain[0]->getName());
        $this->assertEquals(Resolver::ACTION_INSTALL, $chain[0]->getAction());
    }

    public function testTwoDependItemsUpgradeOneItemWithAlreadyInstalledDependency()
    {
        $packA = new Item('testA', "1.0", true);
        $packA->setAction(Resolver::ACTION_NONE);

        $packB = new Item('testB', "1.0", true);
        $packB->setAction(Resolver::ACTION_UPGRADE, "1.1");
        $packB->addDependency('testA', '1.0.*');

        $packC = new Item('testC', "1.0", false);
        $packC->setAction(Resolver::ACTION_NONE);

        $resolver = new Resolver();
        $resolver->addItem($packA);
        $resolver->addItem($packB);
        $resolver->addItem($packC);
        $chain = $resolver->getDependenciesChainForSpecificItems('testB', true);

        $this->assertEquals(1, count($chain));
        $this->assertEquals('testB', $chain[0]->getName());
        $this->assertEquals(Resolver::ACTION_UPGRADE, $chain[0]->getAction());
    }

    public function testTwoDependItemsUpgradeOneItemWithAlreadyInstalledDependencyAndNoForce()
    {
        $packA = new Item('testA', "1.0", true);
        $packA->setAction(Resolver::ACTION_NONE);

        $packB = new Item('testB', "1.0", true);
        $packB->setAction(Resolver::ACTION_UPGRADE, "1.1");
        $packB->addDependency('testA', '1.0.*');

        $packC = new Item('testC', "1.0", false);
        $packC->setAction(Resolver::ACTION_NONE);

        $resolver = new Resolver();
        $resolver->addItem($packA);
        $resolver->addItem($packB);
        $resolver->addItem($packC);
        $chain = $resolver->getDependenciesChainForSpecificItems('testB', false);

        $this->assertEquals(1, count($chain));
        $this->assertEquals('testB', $chain[0]->getName());
        $this->assertEquals(Resolver::ACTION_UPGRADE, $chain[0]->getAction());
    }

    public function testTwoDependItemsUpgradeOneItemWithUninstalledDependency()
    {
        $packA = new Item('testA', "1.0", false);
        $packA->setAction(Resolver::ACTION_NONE);

        $packB = new Item('testB', "1.0", true);
        $packB->setAction(Resolver::ACTION_UPGRADE, "1.1");
        $packB->addDependency('testA', '1.0.*');

        $packC = new Item('testC', "1.0", false);
        $packC->setAction(Resolver::ACTION_NONE);

        $resolver = new Resolver();
        $resolver->addItem($packA);
        $resolver->addItem($packB);
        $resolver->addItem($packC);
        $chain = $resolver->getDependenciesChainForSpecificItems('testB', true);

        $this->assertEquals(2, count($chain));
        $this->assertEquals('testA', $chain[0]->getName());
        $this->assertEquals(Resolver::ACTION_INSTALL, $chain[0]->getAction());
        $this->assertEquals('testB', $chain[1]->getName());
        $this->assertEquals(Resolver::ACTION_UPGRADE, $chain[1]->getAction());
    }

    public function testTwoDependItemsUpgradeOneItemWithDependencyToUpgrade()
    {
        $packA = new Item('testA', "1.0", true);
        $packA->setAction(Resolver::ACTION_UPGRADE, "1.2");

        $packB = new Item('testB', "1.0", true);
        $packB->setAction(Resolver::ACTION_UPGRADE, "1.1");
        $packB->addDependency('testA', '1.2.*');

        $packC = new Item('testC', "1.0", false);
        $packC->setAction(Resolver::ACTION_NONE);

        $resolver = new Resolver();
        $resolver->addItem($packA);
        $resolver->addItem($packB);
        $resolver->addItem($packC);
        $chain = $resolver->getDependenciesChainForSpecificItems('testB', true);

        $this->assertEquals(2, count($chain));
        $this->assertEquals('testA', $chain[0]->getName());
        $this->assertEquals(Resolver::ACTION_UPGRADE, $chain[0]->getAction());
        $this->assertEquals('testB', $chain[1]->getName());
        $this->assertEquals(Resolver::ACTION_UPGRADE, $chain[1]->getAction());
    }

    public function testTwoDependItemsUpgradeOneItemWithDependencyToUpgradeAndNoForce()
    {
        $packA = new Item('testA', "1.0", true);
        $packA->setAction(Resolver::ACTION_UPGRADE, "1.2");

        $packB = new Item('testB', "1.0", true);
        $packB->setAction(Resolver::ACTION_UPGRADE, "1.1");
        $packB->addDependency('testA', '1.2.*');

        $packC = new Item('testC', "1.0", false);
        $packC->setAction(Resolver::ACTION_NONE);

        $resolver = new Resolver();
        $resolver->addItem($packA);
        $resolver->addItem($packB);
        $resolver->addItem($packC);
        $chain = $resolver->getDependenciesChainForSpecificItems('testB', false);

        $this->assertEquals(2, count($chain));
        $this->assertEquals('testA', $chain[0]->getName());
        $this->assertEquals(Resolver::ACTION_UPGRADE, $chain[0]->getAction());
        $this->assertEquals('testB', $chain[1]->getName());
        $this->assertEquals(Resolver::ACTION_UPGRADE, $chain[1]->getAction());
    }

    public function testTwoDependItemsUpgradeOneItemWithNoUpgradedDependency()
    {
        $packA = new Item('testA', "1.0", true);
        $packA->setAction(Resolver::ACTION_NONE);

        $packB = new Item('testB', "1.0", true);
        $packB->setAction(Resolver::ACTION_UPGRADE, "1.1");
        $packB->addDependency('testA', '1.1.*');

        $packC = new Item('testC', "1.0", false);
        $packC->setAction(Resolver::ACTION_NONE);

        $resolver = new Resolver();
        $resolver->addItem($packA);
        $resolver->addItem($packB);
        $resolver->addItem($packC);

        $this->expectException(\Jelix\Dependencies\ItemException::class);
        $this->expectExceptionCode(2);
        $this->expectExceptionMessage('Version of item \'testA\' (1.0) does not match required version by item testB (1.1.*)');
        $chain = $resolver->getDependenciesChainForInstallation();
    }

    public function testFourDependItemsFullUpgradeOfSomeModules()
    {
        $packA = new Item('testA', "1.0", true);
        $packA->setAction(Resolver::ACTION_UPGRADE, "1.1.0");

        $packB = new Item('testB', "1.2", true);
        $packB->setAction(Resolver::ACTION_UPGRADE, "1.3.0");
        $packB->addDependency('testA', '1.1.*');

        $packC = new Item('testC', "1.0", false);
        $packC->setAction(Resolver::ACTION_NONE);

        $packD = new Item('testD', "1.4", true);
        $packD->setAction(Resolver::ACTION_UPGRADE, "1.5.0");
        $packB->addDependency('testF', '1.7.*');

        $packE = new Item('testE', "1.5.0", true);
        $packE->setAction(Resolver::ACTION_UPGRADE, "1.5.1");

        $packF = new Item('testF', "1.6.0", true);
        $packF->setAction(Resolver::ACTION_UPGRADE, "1.7.0");

        $resolver = new Resolver();
        $resolver->addItem($packA);
        $resolver->addItem($packB);
        $resolver->addItem($packC);
        $resolver->addItem($packD);
        $resolver->addItem($packE);
        $resolver->addItem($packF);
        $chain = $resolver->getDependenciesChainForSpecificItems(['testB', 'testD'], true);

        $this->assertEquals(4, count($chain));
        $this->assertEquals('testA', $chain[0]->getName());
        $this->assertEquals(Resolver::ACTION_UPGRADE, $chain[0]->getAction());
        $this->assertEquals('testF', $chain[1]->getName());
        $this->assertEquals(Resolver::ACTION_UPGRADE, $chain[1]->getAction());
        $this->assertEquals('testB', $chain[2]->getName());
        $this->assertEquals(Resolver::ACTION_UPGRADE, $chain[2]->getAction());
        $this->assertEquals('testD', $chain[3]->getName());
        $this->assertEquals(Resolver::ACTION_UPGRADE, $chain[3]->getAction());
    }
}
