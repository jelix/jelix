--TEST--
check reflection of jIAuthDriver interface
--SKIPIF--
<?php if (!extension_loaded("jelix")) print "skip"; ?>
--FILE--
<?php 
Reflection::export(new ReflectionClass('jIAuthDriver'));
?>
--EXPECT--
Interface [ <internal:jelix> interface jIAuthDriver ] {

  - Constants [0] {
  }

  - Static properties [0] {
  }

  - Static methods [0] {
  }

  - Properties [0] {
  }

  - Methods [9] {
    Method [ <internal:jelix, ctor> abstract public method __construct ] {

      - Parameters [1] {
        Parameter #0 [ <required> $params ]
      }
    }

    Method [ <internal:jelix> abstract public method createUserObject ] {

      - Parameters [2] {
        Parameter #0 [ <required> $login ]
        Parameter #1 [ <required> $password ]
      }
    }

    Method [ <internal:jelix> abstract public method saveNewUser ] {

      - Parameters [1] {
        Parameter #0 [ <required> $user ]
      }
    }

    Method [ <internal:jelix> abstract public method removeUser ] {

      - Parameters [1] {
        Parameter #0 [ <required> $login ]
      }
    }

    Method [ <internal:jelix> abstract public method updateUser ] {

      - Parameters [1] {
        Parameter #0 [ <required> $user ]
      }
    }

    Method [ <internal:jelix> abstract public method getUser ] {

      - Parameters [1] {
        Parameter #0 [ <required> $login ]
      }
    }

    Method [ <internal:jelix> abstract public method getUserList ] {

      - Parameters [1] {
        Parameter #0 [ <required> $pattern ]
      }
    }

    Method [ <internal:jelix> abstract public method changePassword ] {

      - Parameters [2] {
        Parameter #0 [ <required> $login ]
        Parameter #1 [ <required> $password ]
      }
    }

    Method [ <internal:jelix> abstract public method verifyPassword ] {

      - Parameters [2] {
        Parameter #0 [ <required> $login ]
        Parameter #1 [ <required> $password ]
      }
    }
  }
}

