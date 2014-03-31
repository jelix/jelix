#!/bin/bash


SOURCE_DIR="$1"
SOURCE_FILE="$2"
TARGET_DIR="$3"
TARGET_FILE="$4"

usage ()
{
    echo "move.sh sourcedir sourcefilename targetdir targetfilename"
    echo "     sourcedir should be relative to lib/jelix-legacy"
    echo "     targetdir should be relative to lib/Jelix"
    exit 1
}

if [ "$SOURCE_DIR" == "" -o  "$SOURCE_FILE" == "" -o "$TARGET_DIR" == "" -o "$TARGET_FILE" == "" ]; then
    usage
    exit 1
fi

if [ ! -d lib/Jelix/ ]; then
    echo "You must execute this script at the root of the repository"
    exit 1
fi

if [ ! -f lib/jelix-legacy/$SOURCE_DIR/$SOURCE_FILE ]; then
    echo "Source file does not exist"
    usage
    exit 1
fi
    
if [ ! -d lib/Jelix/$TARGET_DIR/ ]; then
    mkdir -p lib/Jelix/$TARGET_DIR/
fi

git mv lib/jelix-legacy/$SOURCE_DIR/$SOURCE_FILE lib/Jelix/$TARGET_DIR/$TARGET_FILE
php build/moveclasses/moveClass.php $SOURCE_DIR $SOURCE_FILE $TARGET_DIR $TARGET_FILE
git add lib/Jelix/Legacy/$SOURCE_DIR
#git commit -am "Move $SOURCE_FILE to lib/Jelix/$TARGET_DIR/$TARGET_FILE"
