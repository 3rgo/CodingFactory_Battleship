<?php

function cls(){
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        system('cls');
    } else {
        system('clear');
    }
}

function println($str){
    echo $str . PHP_EOL;
}

function printMenu(){
    println("************ Battleship ******************");
    println("1 - New Game");
    println("2 - Quit");
    println("************ Battleship ******************");
    println("Enter your choice (1-2) :");
}