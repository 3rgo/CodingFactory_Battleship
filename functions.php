<?php

// Utility functions for output
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


// Board output
function printBoards($userBoard = [], $computerBoard = [], $extraText = []){
    $uBoard = printSingleBoard($userBoard, "PLAYER", false);
    $eBoard = printSingleBoard($computerBoard, "ENEMY", true);

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

function printSingleBoard($boardData, $title, $hideShips){
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

// Utility function to get the list of coordinates where there is a ship
function getShips($boardData){
    return array_reduce(
        array_map("placementToCoordinates", $boardData['ships']),
        function($carry, $item){ return array_merge($carry, $item); },
        []
    );
}

// Utility function for coordinates and placements
function areCoordinatesValid($coord){
    list($x, $y) = coordinatesToIndex($coord);
    return $x >= 0 && $x < HORIZONTAL_SIZE
        && $y >= 0 && $y < VERTICAL_SIZE;
}

function coordinatesToIndex($coord){
    preg_match("/^([A-Z]+)([0-9]+)$/",strtoupper($coord), $matches);
    list(, $x, $y) = $matches;
    $x = ord($x) - ord('A');
    $y = $y - 1;
    return [$x, $y];
}

function indexToCoordinates($x, $y){
    return chr(ord("A") + $x) . ($y + 1);
}

function placementToCoordinates($placement){
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

function isPlacementValid($placement, $shipSize, $boardData){
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

function isStrikeValid($coordinates, $history){
    if(!areCoordinatesValid($coordinates)){
        return [false, "Invalid coordinates"];
    }
    if(in_array($coordinates, $history)){
        return [false, "Strike already attempted"];
    }
    return [true, ""];
}


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

function randomStrike($history){
    do {
        $coordinates = indexToCoordinates(rand(0, HORIZONTAL_SIZE-1), rand(0, VERTICAL_SIZE-1));
        list($isStrikeValid, $error) = isStrikeValid($coordinates, $history);
    } while (!$isStrikeValid);
    return $coordinates;
}