--TEST--
check reflection of jIMultiFileCompiler interface
--SKIPIF--
<?php if (!extension_loaded("jelix")) print "skip"; ?>
--FILE--
<?php 
Reflection::export(new ReflectionClass('jIMultiFileCompiler'));
?>
--EXPECT--
Interface [ <internal:jelix> interface jIMultiFileCompiler ] {

  - Constants [0] {
  }

  - Static properties [0] {
  }

  - Static methods [0] {
  }

  - Properties [0] {
  }

  - Methods [2] {
    Method [ <internal:jelix> abstract public method compileItem ] {

      - Parameters [2] {
        Parameter #0 [ <required> $sourceFile ]
        Parameter #1 [ <required> $module ]
      }
    }

    Method [ <internal:jelix> abstract public method endCompile ] {

      - Parameters [1] {
        Parameter #0 [ <required> $cachefile ]
      }
    }
  }
}

