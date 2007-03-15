--TEST--
check reflection of jISimpleCompiler interface
--SKIPIF--
<?php if (!extension_loaded("jelix")) print "skip"; ?>
--FILE--
<?php 
Reflection::export(new ReflectionClass('jISimpleCompiler'));
?>
--EXPECT--
Interface [ <internal:jelix> interface jISimpleCompiler ] {

  - Constants [0] {
  }

  - Static properties [0] {
  }

  - Static methods [0] {
  }

  - Properties [0] {
  }

  - Methods [1] {
    Method [ <internal> abstract public method compile ] {

      - Parameters [1] {
        Parameter #0 [ <required> $aSelector ]
      }
    }
  }
}

