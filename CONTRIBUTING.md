How to contribute
=================

If you want to provide a patch on jelix

- Fill an issue on Github https://github.com/jelix/jelix/.
- fork the jelix/jelix repository on github
- creates a new branch from the branch that you want to patch
- commit in this new branch. The commit message should contain `Refs #number` with
  `number` is the issue number on github.
- you can verify that all tests passe by running testapp with the docker environment.
  See testapp/README.md
- do a pull request on github, to merge into the target branch
- chances to see your patch accepted, are high if your code follow the PSR-2 coding style
  (even if currently not all the code follows this coding style :-)) and if it contains
  unit tests when it does make sens.


**Please**, to fix issues on stable versions, do it on their corresponding branches,
not master! So **do pull requests** on stable branches!
