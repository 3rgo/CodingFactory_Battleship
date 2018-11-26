<?php

require_once './functions.php';


$config = [
    "horizontalSize" => 10,
    "verticalSize" => 10,
    "cells" => [
        "empty" => "·",
        "ship" => "⮹",
        "hit" => "⌧",
        "miss" => "⊙"
    ],
    "ships" => [
        "Carrier" => 5,
        "Battleship" => 4,
        "Cruiser" => 3,
        "Submarine" => 3,
        "Destroyer" => 3,
    ]
];

// Game state object :
//  Steps :
//      0 - Menu
//      1 - Game setup
//      2 - Game
//  Player/Computer :
//      Ships : List of ships with their chosen coordinates
//      Moves : History of moves and results
$state = [
    "step" => 0,
    "player" => [
        "ships" => [],
        "moves" => []
    ],
    "computer" => [
        "ships" => [],
        "moves" => []
    ]
];


// Main loop
while(true){
    cls();
    if($state['step'] === 0){
        // Print the menu
        printMenu();

        // Read user choice
        $choice = trim( fgets(STDIN) );

        switch($choice){
            // Start a new game
            case 1 :
                $state['step'] = 1;
                break;
            // Exit game
            case 2 :
                println("Goodbye");
                break 2;
        }
    }
    else if($state['step'] === 1) {
        $ships = $config['ships'];
        $nbShips = count($config['ships']);
        $sideText = ["You have $nbShips ships to place on a {$config['horizontalSize']}x{$config['verticalSize']} board :"];
        foreach($config['ships'] as $ship => $size){
            $sideText[] = "\t- {$ship} : {$size} slots";
        }
        printBoard($sideText);
        break;
    }
}