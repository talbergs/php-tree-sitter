#!/bin/bash

ROOT=$(cd $(dirname $0)/..;pwd)
VERSION=$1

[[ -z $VERSION ]] && echo 'Give version ($1)' && exit 3

for lock in $ROOT/grammars/*/composer.json $ROOT/binding/composer.json
do
    echo editing $lock
    echo "$(jq '.version = "'$VERSION'"' $lock )" > $lock
done
