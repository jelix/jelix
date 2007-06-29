--TEST--
check reflection of jICoordPlugin interface
--SKIPIF--
<?php if (!extension_loaded("jelix")) print "skip"; ?>
--FILE--
<?php 
Reflection::export(new ReflectionClass('jICoordPlugin'));
?>
--EXPECT--
Interface [ <internal:jelix> interface jICoordPlugin ] {

  - Constants [0] {
  }

  - Static properties [0] {
  }

  - Static methods [0] {
  }

  - Properties [0] {
  }

  - Methods [3] {
    Method [ <internal> abstract public method beforeAction ] {

      - Parameters [1] {
        Parameter #0 [ <required> $params ]
      }
    }

    Method [ <internal> abstract public method beforeOutput ] {
    }

    Method [ <internal> abstract public method afterProcess ] {
    }
  }
}

