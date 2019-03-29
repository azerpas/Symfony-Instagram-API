<?php

namespace App\Command;

use App\Service\DBRequest;

class MainCommand{
    public function __construct($user){
        $this->user = $user;
    }
    /**
     * @method: check if user have defined time slots
     * if so fetching them from BD and checking if currently in time slot
     * @return: Boolean
     */
    public function isTime(DBRequest $db){
        $slots = $db->getSlots($this->user);
        if($slots == null){ //|| $slots == {}){

        }
        return true;
    }

    /**
     * @method: fetch user list to Interact with
     * @param: $number - number of accounts to retrieve
     * @return: $userList - fetched user list
     */
    public function usersToInteract(){
        // use DBRequest
        $userList = [];
        return $userList;
    }

    protected function execute(){
        // isTime
        // usersToInteract
        // do while
        //  new process(like -> follow)
        // if search active
        //  new process(search)
        return true;
    }
}