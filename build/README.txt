jBuildTools:
=============

These are scripts 

- to generate final source file from source file which needs a preprocessing step.
- to generate package to distribute sources
- to create "makefile" like in PHP


preprocess.php
   This is a tool to preprocess source file. It generates source file from other source file which
   contain preprocessing instruction. So you can generate source file according to parameters 
   (environment variables).
   see http://developer.jelix.org/wiki/en/preprocessor

   usage :
     php preprocess.php source_file target_file

mkdist.php
   Copy some source file from a directory to another, according to a "manifest" file. 
   So it can be used to generate packages.
   In the manifest, you write the list of files, and indicates where it should be copied,
   if a preprocessor should be applied etc..
   see http://developer.jelix.org/wiki/en/mkdist
   usage :
      php mkdist.php [-v] manifest_file.mn source_dir target_dir

mkmanifest.php
   generate a manifest file
   php mkmanifest.php [-v] source_dir [base_path] file.mn

jBuild.inc.php
   library to use in a script, to create a build file (a makefile like)