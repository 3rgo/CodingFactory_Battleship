<?php

require_once './functions.php';

define('HORIZONTAL_SIZE', 10);
define('VERTICAL_SIZE', 10);
define('SHIPS', [
    "Carrier" => 5,
    "Battleship" => 4,
    "Cruiser" => 3,
    "Submarine" => 3,
    "Destroyer" => 3,
]);
define('CELL_EMPTY', ' ');
define('CELL_MISS', 'O');
define('CELL_SHIP', 'S');
define('CELL_HIT', 'X');

// Game state object :
//  Steps :
//      0 - Menu
//      1 - Game setup
//      2 - Game
//  Player/Computer :
//      Ships : List of ships with their placement (e.g. A1-A5)
//      Moves : History of enemy moves
$state = [
    "turn" => 0,
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
    if($state['turn'] === 0){
        $ships = SHIPS;
        do {
            $nbShips = count($ships);
            $sideText = ["You have $nbShips ships to place on a ".HORIZONTAL_SIZE."x".VERTICAL_SIZE." board :"];
            foreach($ships as $ship => $size){
                $sideText[] = "\t- {$ship} : {$size} slots";
            }
            $nextShipName = array_keys($ships)[0];
            $nextShipSize = array_values($ships)[0];
            printBoards($state['player'], $state['computer'], $sideText);
            do {
                println("Input the coordinates of the $nextShipName (e.g. A1-A$nextShipSize) :");
                $placement = trim(fgets(STDIN));
                list($isPlacementValid, $error) = isPlacementValid($placement, $nextShipSize);
                if(!empty($error)){ println("Error : ".$error); }
            } while(!$isPlacementValid);

            $state['player']['ships'][] = $placement;
            unset($ships[$nextShipName]);

        } while(!empty($ships));
        break;
    } else {

    }
}