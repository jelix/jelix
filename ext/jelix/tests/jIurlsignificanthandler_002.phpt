--TEST--
check reflection of jIUrlSignificantHandler interface
--SKIPIF--
<?php if (!extension_loaded("jelix")) print "skip"; ?>
--FILE--
<?php 
Reflection::export(new ReflectionClass('jIUrlSignificantHandler'));
?>
--EXPECT--
Interface [ <internal:jelix> interface jIUrlSignificantHandler ] {

  - Constants [0] {
  }

  - Static properties [0] {
  }

  - Static methods [0] {
  }

  - Properties [0] {
  }

  - Methods [2] {
    Method [ <internal:jelix> abstract public method parse ] {

      - Parameters [1] {
        Parameter #0 [ <required> $url ]
      }
    }

    Method [ <internal:jelix> abstract public method create ] {

      - Parameters [2] {
        Parameter #0 [ <required> $urlact ]
        Parameter #1 [ <required> $url ]
      }
    }
  }
}

