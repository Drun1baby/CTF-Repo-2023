#!/bin/bash

# please figure out the real flag.

if [ $# != 1 ]; then
    echo "Use: bash $0 aliyunctf{00000000000000000000000000000000}"
    exit 0
fi
FLAG=$1

if [ -f "flagchecker.js" ]; then
    # build this challenge

    # make sure $FLAG is the real flag
    cmd1=`./node flagchecker.js $FLAG`
    if [ $cmd1 != "Right!" ];then
        echo "Exit"
        exit 0
    fi

    # generate bytecode of flagchecker.js
    ./node --print-bytecode flagchecker.js $FLAG > flagchecker_bytecode.txt

    # compile flagchecker.js to flagchecker.jsc, for you to verify your flag.
    ./node ./runner.js $FLAG
    tar czvf jsbytecodechall1.tar.gz ./node ./runner.js ./flagchecker.jsc ./run.sh flagchecker_bytecode.txt
else
    # check your flag
    ./node ./runner.js $FLAG
fi