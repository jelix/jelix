#!/bin/bash


SOURCE_DIR="$1"
SOURCE_FILE="$2"
TARGET_DIR="$3"
TARGET_FILE="$4"
if [ ! -d lib/Jelix/ ]; then
    echo "You must execute this script at the root of the repository"
    exit 1
fi
    
if [ ! -d lib/Jelix/$TARGET_DIR/ ]; then
    mkdir -p lib/Jelix/$TARGET_DIR/
fi

git mv lib/jelix-legacy/$SOURCE_DIR/$SOURCE_FILE lib/Jelix/$TARGET_DIR/$TARGET_FILE
php build/moveclasses/moveClass.php $SOURCE_DIR $SOURCE_FILE $TARGET_DIR $TARGET_FILE
