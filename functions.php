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

function isPlacementValid($placement, $shipSize){
    global $config, $state;
    if(!preg_match("/^[A-Z]{1}[0-9]+\-[A-Z]{1}[0-9]+$/",$placement)){
        return [false, "Invalid placement format"];
    }
    list($start, $end) = explode("-", $placement);
    list($x1, $y1) = coordinatesToIndex($start);
    list($x2, $y2) = coordinatesToIndex($end);
    if($x1!=$x2 && $y1!=$y2){
        return [false, "Diagonal placement impossible"];
    }
    if(($givenSize = (abs($x1-$x2)+abs($y1-$y2))+1) != $shipSize){
        return [false, "Given size ($givenSize) does not match the ship's ($shipSize)"];
    }
    $coordinates = placementToCoordinates($placement);
    $ownShips = array_reduce(
        $state['player']['ships'],
        function($carry, $item){ return array_merge($carry, $item); },
        []
    );
    echo(json_encode($ownShips). ' | ' . json_encode($coordinates));
    $intersect = array_intersect($ownShips, $coordinates);
    if(count($intersect) > 0){
        $intCoord = indexToCoordinates($intersect[0]);
        return [false, "There is already a ship in $intCoord"];
    }
    return [true, ""];
}

function placementToCoordinates($placement){
    var_dump($placement);
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

function indexToCoordinates($x, $y){
    return chr(ord("A") + $x) . ($y + 1);
}

function coordinatesToIndex($coord){
    list($x, $y) = str_split(strtoupper($coord));
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

    $ownShips = array_reduce(
        array_map("placementToCoordinates", $state['player']['ships']),
        function($carry, $item){ return array_merge($carry, $item); },
        []
    );

    for($y = 0; $y < $config['verticalSize']; $y++){
        $row = [str_pad($y+1, $rowIndexSize, " ", STR_PAD_LEFT), "|"];
        for($x = 0; $x < $config['horizontalSize']; $x++){
            if(in_array([$x, $y], $ownShips)){
                $cell = $config['cells']['ship'];
            }
            else {
                $cell = $config['cells']['empty'];
            }
            $row[] = $cell;
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