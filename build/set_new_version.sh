#!/bin/bash


SCRIPTDIR=$(dirname $0)

VERSION=$1
if [ "$VERSION" == "" ]; then
    echo "Error: new version is missing"
    exit 1
fi

NEXTVERSION=$2
if [ "$NEXTVERSION" == "" ]; then
    echo "Error: next version is missing"
    exit 1
fi



VER_MAJOR=""
VER_MIDDLE=""
VER_MINOR=""
VER_FIX=""

for i in $(echo $VERSION | tr "." "\n")
do
    if [ "$VER_MAJOR" == "" ]
    then
        VER_MAJOR=$i
    else
        if [ "$VER_MIDDLE" == "" ]
        then
            VER_MIDDLE=$i
        else
            if [ "$VER_MINOR" == "" ]
            then
                VER_MINOR=$i
            else
                if [ "$VER_FIX" == "" ]
                then
                    VER_FIX=$i
                else
                    echo "Too much version separators"
                    exit 1
                fi
            fi
        fi
    fi
  # process
done

if [ "$VER_MAJOR" == "" ]
then
    echo "Error: bad version number?"
    exit 1
fi

if [ "$VER_MIDDLE" == "" ]
then
    VER_MIDDLE="0"
fi
if [ "$VER_MINOR" == "" ]
then
    VER_MINOR="0"
fi


BRANCH_VERSION="$VER_MAJOR.$VER_MIDDLE.x"
if [ "$VER_FIX" == "" ]
then
    TAG_NAME="RELEASE_JELIX_${VER_MAJOR}_${VER_MIDDLE}_${VER_MINOR}"
else
    TAG_NAME="RELEASE_JELIX_${VER_MAJOR}_${VER_MIDDLE}_${VER_MINOR}_${VER_FIX}"
fi

echo "$VERSION" > $SCRIPTDIR/../lib/jelix/VERSION
echo "$VERSION" > $SCRIPTDIR/../testapp/VERSION

git add $SCRIPTDIR/../lib/jelix/VERSION
git add $SCRIPTDIR/../testapp/VERSION
git commit -m "Release Jelix $VERSION"
git tag "$TAG_NAME"

echo "$NEXTVERSION" > $SCRIPTDIR/../lib/jelix/VERSION
echo "$NEXTVERSION" > $SCRIPTDIR/../testapp/VERSION

git add $SCRIPTDIR/../lib/jelix/VERSION
git add $SCRIPTDIR/../testapp/VERSION
git commit -m "Bumped version to $NEXTVERSION"

