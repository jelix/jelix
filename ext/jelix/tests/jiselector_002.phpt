--TEST--
check reflection of jISelector interface
--SKIPIF--
<?php if (!extension_loaded("jelix")) print "skip"; ?>
--FILE--
<?php 
Reflection::export(new ReflectionClass('jISelector'));
?>
--EXPECT--
Interface [ <internal:jelix> interface jISelector ] {

  - Constants [0] {
  }

  - Static properties [0] {
  }

  - Static methods [0] {
  }

  - Properties [0] {
  }

  - Methods [5] {
    Method [ <internal> abstract public method getPath ] {
    }

    Method [ <internal> abstract public method getCompiledFilePath ] {
    }

    Method [ <internal> abstract public method getCompiler ] {
    }

    Method [ <internal> abstract public method useMultiSourceCompiler ] {
    }

    Method [ <internal> abstract public method toString ] {

      - Parameters [1] {
        Parameter #0 [ <optional> $full ]
      }
    }
  }
}

