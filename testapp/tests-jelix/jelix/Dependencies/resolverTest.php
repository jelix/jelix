<?php
/**
* @author      Laurent Jouanneau
* @copyright   2016 Laurent Jouanneau
* @link        http://jelix.org
* @licence     MIT
*/

use Jelix\Dependencies\Resolver;
use Jelix\Dependencies\Item;

class resolverTest extends PHPUnit_Framework_TestCase {

    /**
     *
     */
    public function testOneItemNoDeps() {
        $packA = new Item('testA', false, "1.0", Resolver::ACTION_NONE);
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
        $packA = new Item('testA', false, "1.0", Resolver::ACTION_INSTALL);
        $packB = new Item('testB', false, "1.0", Resolver::ACTION_NONE);
        $packA->addDependency('testB', '1.0.*');
        $packC = new Item('testC', false, "1.0", Resolver::ACTION_NONE);

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

    /**
     * @expectedException \Jelix\Dependencies\ItemException
     * @expectedExceptionCode 1
     */
    public function testCircularDependencies() {
        $packA = new Item('testA', false, "1.0", Resolver::ACTION_INSTALL);
        $packA->addDependency('testB', '1.0.*');
        $packB = new Item('testB', false, "1.0", Resolver::ACTION_NONE);
        $packB->addDependency('testC', '1.0.*');
        $packC = new Item('testC', false, "1.0", Resolver::ACTION_NONE);
        $packC->addDependency('testA', '1.0.*');

        $resolver = new Resolver();
        $resolver->addItem($packA);
        $resolver->addItem($packB);
        $resolver->addItem($packC);
        $chain = $resolver->getDependenciesChainForInstallation();
    }

    public function testComplexInstallDependencies() {
        $packA = new Item('testA', false, "1.0", Resolver::ACTION_INSTALL);
        $packA->addDependency('testB');
        $packA->addDependency('testC');
        $packB = new Item('testB', false, "1.0", Resolver::ACTION_NONE);
        $packC = new Item('testC', false, "1.0", Resolver::ACTION_NONE);
        $packD = new Item('testD', false, "1.0", Resolver::ACTION_INSTALL);
        $packD->addDependency('testB');
        $packD->addDependency('testE');
        $packE = new Item('testE', false, "1.0", Resolver::ACTION_NONE);

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
        $packA = new Item('testA', true, "1.0", Resolver::ACTION_REMOVE);
        $resolver = new Resolver();
        $resolver->addItem($packA);
        $chain = $resolver->getDependenciesChainForInstallation();

        $this->assertEquals(1, count($chain));
        $this->assertEquals('testA', $chain[0]->getName());
        $this->assertEquals(Resolver::ACTION_REMOVE, $chain[0]->getAction());
    }

    public function testRemoveUninstalledItem() {
        $packA = new Item('testA', false, "1.0", Resolver::ACTION_REMOVE);
        $resolver = new Resolver();
        $resolver->addItem($packA);
        $chain = $resolver->getDependenciesChainForInstallation();

        $this->assertEquals(0, count($chain));
    }


    public function testRemoveOneDependItems() {
        $packA = new Item('testA', true, "1.0", Resolver::ACTION_REMOVE);
        $packA->addDependency('testB', '1.0.*');

        $packB = new Item('testB', false, "1.0", Resolver::ACTION_NONE);
        $packC = new Item('testC', true, "1.0", Resolver::ACTION_NONE);

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
        $packA = new Item('testA', true, "1.0", Resolver::ACTION_REMOVE);
        $packB = new Item('testB', true, "1.0", Resolver::ACTION_NONE);
        $packB->addDependency('testA', '1.0.*');
        $packC = new Item('testC', false, "1.0", Resolver::ACTION_NONE);

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

    /**
     * @expectedException \Jelix\Dependencies\ItemException
     * @expectedExceptionCode 5
     */
    public function testRemoveOneAncesterToInstallDependItems() {
        $packA = new Item('testA', true, "1.0", Resolver::ACTION_REMOVE);
        $packB = new Item('testB', false, "1.0", Resolver::ACTION_INSTALL);
        $packB->addDependency('testA', '1.0.*');
        $packC = new Item('testC', false, "1.0", Resolver::ACTION_NONE);

        $resolver = new Resolver();
        $resolver->addItem($packA);
        $resolver->addItem($packB);
        $resolver->addItem($packC);
        $chain = $resolver->getDependenciesChainForInstallation();
    }

    /**
     * @expectedException \Jelix\Dependencies\ItemException
     * @expectedExceptionCode 4
     */
    public function testRemoveCircularDependencies() {
        $packA = new Item('testA', true, "1.0", Resolver::ACTION_REMOVE);
        $packA->addDependency('testC', '1.0.*');
        $packB = new Item('testB', true, "1.0", Resolver::ACTION_NONE);
        $packB->addDependency('testA', '1.0.*');
        $packC = new Item('testC', true, "1.0", Resolver::ACTION_NONE);
        $packC->addDependency('testB', '1.0.*');

        $resolver = new Resolver();
        $resolver->addItem($packA);
        $resolver->addItem($packB);
        $resolver->addItem($packC);
        $chain = $resolver->getDependenciesChainForInstallation();
    }

    public function testInstallRemove() {
        $packA = new Item('testA', false, "1.0", Resolver::ACTION_INSTALL);
        $packA->addDependency('testB');
        $packB = new Item('testB', false, "1.0", Resolver::ACTION_NONE);
        $packC = new Item('testC', true, "1.0", Resolver::ACTION_NONE);
        $packC->addDependency('testD');
        $packD = new Item('testD', true, "1.0", Resolver::ACTION_REMOVE);
        $packE = new Item('testE', true, "1.0", Resolver::ACTION_NONE);

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
        $packA = new Item('testA', false, "1.0", Resolver::ACTION_INSTALL);
        $packB = new Item('testB', false, "1.0", Resolver::ACTION_NONE);
        $packA->addIncompatibility('testB', '*');
        $packC = new Item('testC', false, "1.0", Resolver::ACTION_NONE);

        $resolver = new Resolver();
        $resolver->addItem($packA);
        $resolver->addItem($packB);
        $resolver->addItem($packC);
        $chain = $resolver->getDependenciesChainForInstallation();
        $this->assertEquals(array($packA), $chain);
    }

    /**
     *
     * @expectedException \Jelix\Dependencies\ItemException
     * @expectedExceptionCode 8
     * @expectedExceptionMessage Item testB is in conflicts with item testA
     */
    public function testConflictItems() {
        $packA = new Item('testA', false, "1.0", Resolver::ACTION_INSTALL);
        $packB = new Item('testB', false, "1.0", Resolver::ACTION_INSTALL);
        $packA->addIncompatibility('testB', '*');
        $packC = new Item('testC', false, "1.0", Resolver::ACTION_NONE);

        $resolver = new Resolver();
        $resolver->addItem($packA);
        $resolver->addItem($packB);
        $resolver->addItem($packC);
        $chain = $resolver->getDependenciesChainForInstallation();
    }

    /**
     *
     * @expectedException \Jelix\Dependencies\ItemException
     * @expectedExceptionCode 7
     * @expectedExceptionMessage Item testB is in conflicts with item testA
     */
    public function testConflictItemAlreadyInstalled() {
        $packA = new Item('testA', false, "1.0", Resolver::ACTION_INSTALL);
        $packA->addIncompatibility('testB', '*');
        $packB = new Item('testB', true, "1.0", Resolver::ACTION_NONE);
        $packC = new Item('testC', false, "1.0", Resolver::ACTION_NONE);

        $resolver = new Resolver();
        $resolver->addItem($packA);
        $resolver->addItem($packB);
        $resolver->addItem($packC);
        $chain = $resolver->getDependenciesChainForInstallation();
    }

}