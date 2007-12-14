--TEST--
check reflection of jIUrlEngine interface
--SKIPIF--
<?php if (!extension_loaded("jelix")) print "skip"; ?>
--FILE--
<?php 
Reflection::export(new ReflectionClass('jIUrlEngine'));
?>
--EXPECT--
Interface [ <internal:jelix> interface jIUrlEngine ] {

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

      - Parameters [3] {
        Parameter #0 [ <required> $scriptNamePath ]
        Parameter #1 [ <required> $pathinfo ]
        Parameter #2 [ <required> $params ]
      }
    }

    Method [ <internal:jelix> abstract public method create ] {

      - Parameters [1] {
        Parameter #0 [ <required> $urlact ]
      }
    }
  }
}
