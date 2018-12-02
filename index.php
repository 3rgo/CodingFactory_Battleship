<?php

require_once './functions.php';

// Config
define('HORIZONTAL_SIZE', 10);
define('VERTICAL_SIZE', 10);
define('SHIPS', [
    "Carrier" => 5,
    "Battleship" => 4,
    "Cruiser" => 3,
    "Submarine" => 3,
    "Destroyer" => 2,
]);
define('CELL_EMPTY', ' ');
define('CELL_MISS', 'O');
define('CELL_SHIP', 'S');
define('CELL_HIT', 'X');

// Game state object
$state = [
    "turn" => 0,
    "player" => [
        "ships" => [],
        "enemyMoves" => []
    ],
    "computer" => [
        "ships" => [],
        "enemyMoves" => []
    ]
];

// Main loop
while(true){
    cls();
    if($state['turn'] === 0){
        $ships = SHIPS;
        // Player's ships placement
        while(!empty($ships)) {
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
                list($isPlacementValid, $error) = isPlacementValid($placement, $nextShipSize, $state['player']);
                if(!empty($error)){ println("Error : ".$error); }
            } while(!$isPlacementValid);

            $state['player']['ships'][] = $placement;
            unset($ships[$nextShipName]);
        }
        // Setup AI ships
        $ships = array_values(SHIPS);
        while(!empty($ships)){
            $shipSize = array_shift($ships);
            $placement = randomShipPlacement($shipSize, $state['computer']);
            $state['computer']['ships'][] = $placement;
        }
        // Start game
        $state['turn'] = 1;
    } else {
        $enemyShips = getShips($state['computer']);
        $playerShips = getShips($state['player']);
        do {
            // Print boards
            cls();
            printBoards($state['player'], $state['computer'], ["Turn #{$state['turn']}"]);

            // Ask user for move
            do {
                println("Input the coordinates of your next strike :");
                $coordinates = trim(fgets(STDIN));
                list($isStrikeValid, $error) = isStrikeValid($coordinates, $state['computer']['enemyMoves']);
            } while(!$isStrikeValid);

            // Display result
            if(in_array($coordinates, $enemyShips)){
                println("HIT !");
            } else {
                println("MISS !");
            }
            $state['computer']['enemyMoves'][] = $coordinates;

            // Check if player won
            if(count(array_diff($enemyShips, $state['computer']['enemyMoves'])) === 0){
                println("VICTORY !!!!");
                break 2;
            }

            // Enemy move
            $enemyStrike = randomStrike($state['player']['enemyMoves']);
            println("Enemy striked at coordinates $enemyStrike : " . (in_array($enemyStrike, $playerShips) ? "HIT !" : "MISS !"));
            $state['player']['enemyMoves'][] = $enemyStrike;

            // Check if computer won
            if(count(array_diff($playerShips, $state['player']['enemyMoves'])) === 0){
                println("DEFEAT...");
                break 2;
            }

            // Require user input to prevent screen from being erased before user could read
            println("Press Enter to go to next turn");
            fgets(STDIN);

            // Increment turn
            $state['turn']++;
        } while(true);
    }
}