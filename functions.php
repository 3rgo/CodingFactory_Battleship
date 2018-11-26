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

function areCoordinatesValid($coord){
    global $config;
    list($x, $y) = coordinatesToIndex($coord);
    return $x >= 0 && $x < $config['horizontalSize']
        && $y >= 0 && $y < $config['verticalSize'];
}

function indexToCoordinates($x, $y){
    return chr(ord("A") + $x) . ($y + 1);
}

function coordinatesToIndex($coord){
    list($x, $y) = explode('', strtoupper($coord));
    $x = ord($x) - ord('A');
    $y = $y - 1;
    return [$x, $y];
}

function printBoard($extraText = []){
    global $config, $state;
    $rowIndexSize = strlen(strval($config['verticalSize']));
    $board = [
        [str_repeat(" ", $rowIndexSize), "|"],
        [str_pad("-", $rowIndexSize, " ", STR_PAD_LEFT), "-"]
    ];

    for($x = 0; $x < $config['horizontalSize']; $x++){
        $board[0][] = chr(ord("A") + $x);
        $board[1][] = "-";
    }
    $board[0][] = "|";
    $board[1][] = "|";

    for($y = 0; $y < $config['verticalSize']; $y++){
        $row = [str_pad($y+1, $rowIndexSize, " ", STR_PAD_LEFT), "|"];
        for($x = 0; $x < $config['horizontalSize']; $x++){
            $row[] = $config['cells']['empty'];
        }
        $row[] = "|";
        $board[] = $row;
    }
    $board[] = $board[1];

    foreach($board as $board_row){
        println(implode("  ", $board_row));
    }
    if(!empty($extraText)){
        println("******************");
        foreach($extraText as $row){
            println($row);
        }
    }
}