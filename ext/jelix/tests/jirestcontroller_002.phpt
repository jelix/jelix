--TEST--
check reflection of jIRestController interface
--SKIPIF--
<?php if (!extension_loaded("jelix")) print "skip"; ?>
--FILE--
<?php 
Reflection::export(new ReflectionClass('jIRestController'));
?>
--EXPECT--
Interface [ <internal:jelix> interface jIRestController ] {

  - Constants [0] {
  }

  - Static properties [0] {
  }

  - Static methods [0] {
  }

  - Properties [0] {
  }

  - Methods [4] {
    Method [ <internal> abstract public method get ] {
    }

    Method [ <internal> abstract public method post ] {
    }

    Method [ <internal> abstract public method put ] {
    }

    Method [ <internal> abstract public method delete ] {
    }
  }
}

