
The project is in a phase where we migrate the code to beautiful namespaced classes.

To do it, a tool has been made, build/moveclasses/move.sh, to execute in a command line.

It will:
- rename and move the file to lib/JelixFramework
- create a class with the old name into lib/JelixFramework/Legacy that inherits from the new class
- update mapping.json and newclassname.json files so the autoloader will be able to load
  old classes.
- update manifests files into build/manifests/

It won't change sources files directly. So

1) run move.sh
2) commit
3) made changes in moved files and in callers into lib/JelixFramework/
4) commit


Ex:

build/moveclasses/move.sh utils/ jIniFile.class.php IniFile/ Manager.php
