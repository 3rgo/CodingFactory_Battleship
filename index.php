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
    "step" => 1,
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
        do {
            $nbShips = count($ships);
            $sideText = ["You have $nbShips ships to place on a {$config['horizontalSize']}x{$config['verticalSize']} board :"];
            foreach($ships as $ship => $size){
                $sideText[] = "\t- {$ship} : {$size} slots";
            }
            $nextShipName = array_keys($ships)[0];
            $nextShipSize = array_values($ships)[0];
            printBoard($sideText);
            do {
                println("Input the coordinates of the $nextShipName (e.g. A1-A$nextShipSize) :");
                $placement = trim(fgets(STDIN));
                list($isPlacementValid, $error) = isPlacementValid($placement, $nextShipSize);
                if(!empty($error)){ println("Error : ".$error); }
            } while(!$isPlacementValid);

            $state['player']['ships'][] = placementToCoordinates($placement);
            unset($ships[$nextShipName]);

        } while(!empty($ships));
        break;
    }
}