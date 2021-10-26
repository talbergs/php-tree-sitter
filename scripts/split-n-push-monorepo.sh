#!/bin/bash

ROOT=$(cd $(dirname $0)/..;pwd)

RELEASE=$(cat $ROOT/binding/composer.json | jq --raw-output '.version')
echo $RELEASE

declare -A repos
repos[git@github.com:talbergs/tree-sitter.git]=$ROOT/binding 
for grammar in $ROOT/grammars/*
do
    grammar=$(basename $grammar)
    repos[git@github.com:talbergs/tree-sitter-$grammar.git]=$ROOT/grammars/$grammar
done

for key in "${!repos[@]}"
do
    echo From: ${repos[$key]}
    echo To: $key
    (
        rm -rf /tmp/_
        mkdir /tmp/_
        cd /tmp/_
        git clone --depth 1 $key .
        rm -rf *
        rsync -r --exclude 'vendor' ${repos[$key]}/* .
        ls -la
        git add .
        git commit -m "Release $RELEASE"
        git tag $RELEASE
        git push
        git push --tags
    )
done
