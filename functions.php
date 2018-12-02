<?php

/**
 * Clears screen
 * @return void
 */
function cls(){
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        system('cls');
    } else {
        system('clear');
    }
}

/**
 * Outputs string with end of line character
 * @param $str String to output
 * @return void
 */
function println(string $str){
    echo $str . PHP_EOL;
}

/**
 * Displays the two board side by side, with optional text
 * @param $playerBoard User board data (ship placement and enemy moves)
 * @param $computerBoard Enemy board data (ship placement and player moves)
 * @param $extraText Extra text to display below the boards
 * @return void
 */
function printBoards(array $playerBoard = [], array $computerBoard = [], array $extraText = []){
    $uBoard = computeBoardOutput($playerBoard, "PLAYER", false);
    $eBoard = computeBoardOutput($computerBoard, "ENEMY", true);

    $output = array_map(function ($b1, $b2){
        return $b1 . "  *  " . $b2;
    }, $uBoard, $eBoard);
    array_map("println", $output);
    if(!empty($extraText)){
        $rowSize = strlen($output[0]);
        println(str_repeat(" ", $rowSize));
        println(str_repeat("*", $rowSize));
        println(str_repeat(" ", $rowSize));
        array_map("println", $extraText);
    }
}

/**
 * Computes the output for a single board
 * @param $boardData Board data (ship placement and enemy moves)
 * @param $title Board title (displayed above)
 * @param $hideShips Whether to show or hide ships
 * @return array Board display output
 */
function computeBoardOutput(array $boardData, string $title, bool $hideShips){
    $rowIndexSize = strlen(strval(VERTICAL_SIZE));
    $board = [
        [str_repeat(" ", $rowIndexSize), "|"],
        [str_pad("-", $rowIndexSize, " ", STR_PAD_LEFT), "-"]
    ];
    for($x = 0; $x < HORIZONTAL_SIZE; $x++){
        $board[0][] = chr(ord("A") + $x);
        $board[1][] = "-";
    }
    $board[0][] = "|";
    $board[1][] = "|";

    $ships = getShips($boardData);

    foreach(range(0, VERTICAL_SIZE-1) as $y){
        $row = [str_pad($y+1, $rowIndexSize, " ", STR_PAD_LEFT), "|"];
        foreach(range(0, HORIZONTAL_SIZE-1) as $x){
            $cellCoords = indexToCoordinates($x, $y);
            if(in_array($cellCoords, $boardData['enemyMoves']) && in_array($cellCoords, $ships)){
                $cell = CELL_HIT;
            }
            elseif(in_array($cellCoords, $ships)){
                $cell = $hideShips ? CELL_EMPTY : CELL_SHIP;
            }
            elseif(in_array($cellCoords, $boardData['enemyMoves'])){
                $cell = CELL_MISS;
            }
            else {
                $cell = CELL_EMPTY;
            }
            $row[] = $cell;
        }
        $row[] = "|";
        $board[] = $row;
    }
    $board[] = $board[1];
    $board = array_map(function($r){
        return implode ("  ", $r);
    }, $board);
    $rowSize = strlen($board[0]);
    $title = str_pad($title, $rowSize, " ", STR_PAD_BOTH);
    array_unshift($board, $title, str_repeat(" ", $rowSize));
    return $board;
}

/**
 * Return the lists of coordinates where there is a ship
 * @param $boardData Board data (ship placement and enemy moves)
 * @return array Array of individual coordinate where a ship is present
 */
function getShips(array $boardData){
    return array_reduce(
        array_map("placementToCoordinates", $boardData['ships']),
        function($carry, $item){ return array_merge($carry, $item); },
        []
    );
}

/**
 * Checks whether a coordinate string is valid
 * @param $coord Coordinate
 * @return boolean Boolean indicating validity
 */
function areCoordinatesValid(string $coord){
    list($x, $y) = coordinatesToIndex($coord);
    return $x >= 0 && $x < HORIZONTAL_SIZE
        && $y >= 0 && $y < VERTICAL_SIZE;
}

/**
 * Converts a coordinate to X/Y indexes
 * @param $coord Coordinate
 * @return array Array of X/Y indexes
 */
function coordinatesToIndex(string $coord){
    preg_match("/^([A-Z]+)([0-9]+)$/",strtoupper($coord), $matches);
    list(, $x, $y) = $matches;
    $x = ord($x) - ord('A');
    $y = $y - 1;
    return [$x, $y];
}

/**
 * Converts X/Y indexes to coordinates
 * @param $x Horizontal index
 * @param $y Vertical index
 * @return string Coordinate
 */
function indexToCoordinates(int $x, int $y){
    return chr(ord("A") + $x) . ($y + 1);
}

/**
 * Converts a placement string to an array of coordinates
 * @param $placement Placement string
 * @return array Array of coordinates
 */
function placementToCoordinates(string $placement){
    list($start, $end) = explode("-", $placement);
    list($x1, $y1) = coordinatesToIndex($start);
    list($x2, $y2) = coordinatesToIndex($end);

    if($x1 != $x2 && $y1 == $y2){
        return array_map(function($x) use ($y1) { return indexToCoordinates($x, $y1); }, range($x1, $x2));
    }
    if($x1 == $x2 && $y1 != $y2){
        return array_map(function($y) use ($x1) { return indexToCoordinates($x1, $y); }, range($y1, $y2));
    }
}

/**
 * Checks whether a placement string is valid based on ship size and board data
 * @param $placement Placement string (e.g. A1-A5)
 * @param $shipSize Ship size
 * @param $boardData Board data (ship placement and enemy moves)
 * @return array Array containing a status boolean and an error message
 */
function isPlacementValid(string $placement, int $shipSize, array $boardData){
    if(!preg_match("/^[A-Z]{1}[0-9]+\-[A-Z]{1}[0-9]+$/",$placement)){
        return [false, "Invalid placement format"];
    }
    list($start, $end) = explode("-", $placement);
    if(!areCoordinatesValid($start) || !areCoordinatesValid($end)){
        return [false, "Invalid coordinates"];
    }
    list($x1, $y1) = coordinatesToIndex($start);
    list($x2, $y2) = coordinatesToIndex($end);
    if($x1!=$x2 && $y1!=$y2){
        return [false, "Diagonal placement impossible"];
    }
    if(($givenSize = (abs($x1-$x2)+abs($y1-$y2))+1) != $shipSize){
        return [false, "Given size ($givenSize) does not match the ship's ($shipSize)"];
    }
    $coordinates = placementToCoordinates($placement);
    $ships = getShips($boardData);
    $intersect = array_intersect($ships, $coordinates);
    if(count($intersect) > 0){
        // $intCoord = indexToCoordinates($intersect[0]);
        return [false, "There is already a ship in {$intersect[0]}"];
    }
    return [true, ""];
}

/**
 * Checks whether strike coordinates are valid based on history
 * @param $coordinates Coordinates from user input
 * @param $history Strike history
 * @return array Array containing a status boolean and an error message
 */
function isStrikeValid(string $coordinates, array $history){
    if(!areCoordinatesValid($coordinates)){
        return [false, "Invalid coordinates"];
    }
    if(in_array($coordinates, $history)){
        return [false, "Strike already attempted"];
    }
    return [true, ""];
}

/**
 * Generates a random ship placement based on size and board data
 * @param $shipSize Ship size
 * @param $boardData Board data (ship placement and enemy moves)
 * @return string Placement string
 */
function randomShipPlacement($shipSize, $boardData){
    do {
        // Generate random placement
        $startCoord = indexToCoordinates(rand(0, HORIZONTAL_SIZE-1), rand(0, VERTICAL_SIZE-1));
        $startIndex = coordinatesToIndex($startCoord);
        $endIndex = $startIndex;
        $direction = [[1, 0],[0, 1]][rand(0,1)];
        for($i = 1; $i < $shipSize; $i++){
            $endIndex[0] += $direction[0];
            $endIndex[1] += $direction[1];
        }
        $endCoord = indexToCoordinates($endIndex[0], $endIndex[1]);
        $placement = "$startCoord-$endCoord";
        list($isPlacementValid, $error) = isPlacementValid($placement, $shipSize, $boardData);
    } while(!$isPlacementValid);
    return $placement;
}

/**
 * Generates a random coordinates for AI strike, based on strike history (to prevent duplicates)
 * @param $history History of AI strikes
 * @return string Strike coordinates
 */
function randomStrike(array $history){
    do {
        $coordinates = indexToCoordinates(rand(0, HORIZONTAL_SIZE-1), rand(0, VERTICAL_SIZE-1));
        list($isStrikeValid, $error) = isStrikeValid($coordinates, $history);
    } while (!$isStrikeValid);
    return $coordinates;
}