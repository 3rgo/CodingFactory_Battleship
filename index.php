<?php

include_once('./functions.php');


$gameHasStarted = false;

// Main loop
while(true){
    cls();
    if(!$gameHasStarted){
        // Print the menu
        printMenu();

        // Read user choice
        $choice = trim( fgets(STDIN) );

        switch($choice){
            // Start a new game
            case 1 :
                $gameHasStarted = true;
                break;
            // Exit game
            case 2 :
                println("Goodbye");
                break 2;
        }
    }
    else {
        println("New game");
        break;
    }
}