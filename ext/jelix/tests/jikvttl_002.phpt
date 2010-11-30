--TEST--
check reflection of jIKVttl interface
--SKIPIF--
<?php if (!extension_loaded("jelix")) print "skip"; ?>
--FILE--
<?php 
Reflection::export(new ReflectionClass('jIKVttl'));
?>
--EXPECT--
Interface [ <internal:jelix> interface jIKVttl ] {

  - Constants [0] {
  }

  - Static properties [0] {
  }

  - Static methods [0] {
  }

  - Properties [0] {
  }

  - Methods [2] {
    Method [ <internal:jelix> abstract public method setWithTtl ] {

      - Parameters [3] {
        Parameter #0 [ <required> $key ]
        Parameter #1 [ <required> $value ]
        Parameter #2 [ <required> $ttl ]
      }
    }

    Method [ <internal:jelix> abstract public method garbage ] {
    }
  }
}

