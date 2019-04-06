<?php

namespace App\Command;

use App\Service\DBRequest;

class MainCommand{
    public function __construct(DBRequest $dbRequest, User $user){
        $this->user = $user;
        $this->db = $dbRequest;
    }
    /**
     * @method: check if user have defined time slots
     * if so fetching them from BD and checking if currently in time slot
     * @return: Boolean
     */
    public function isTime($slots){
        if($slots == null){ //|| $slots == {}){
        }
        else{
            $dt = new \DateTime();
            // need timezone change
            $dt = $dt->format("H");
            $slots = json_decode($slots);
            for($i = 0; $i < count($slots) ; $i++){
                // if this slot is set to true
                // AND
                // time equal current time -> return true
                if($slots[$i] == true && $i == intval($dt)){
                    return true;
                }
            }
            return false;
        }
        return false;
    }

    /**
     * @method: fetch user list to Interact with
     * @param: $number - number of accounts to retrieve
     * @return: $userList - fetched user list
     */
    public function accountsToInteract(){
        return $this->db->getAllAccounts($this->user);
    }

    protected function execute(){
        $slots = $this->db->getSlots($this->user);
        // accountsToInteract
        $accounts = accountsToInteract();
        foreach($accounts as $account) {
            while (isTime($slots)) {
                $processSearch = new Process('php bin/console search:tag '.$account->getUsername.' '.$account->getPassword());
                $processSearch->setWorkingDirectory(getcwd());
                $processSearch->setWorkingDirectory("../");
                //$process->setWorkingDirectory($kernel->getProjectDir());
                $processSearch->start(function ($type, $buffer) {
                    if (Process::ERR === $type) {
                        echo 'ERR > '.$buffer;
                        return new Response("Cannot connect to Instagram, please check your params");
                    } else {
                        echo 'OUT > '.$buffer.'<br>';
                    }
                });
                $processLikeAndFollowUsers = new Process('php bin/console app:likeAndFollowUsers '.$account->getUsername.' '.$account->getPassword());
                $processLikeAndFollowUsers->setWorkingDirectory(getcwd());
                $processLikeAndFollowUsers->setWorkingDirectory("../");
                //$process->setWorkingDirectory($kernel->getProjectDir());
                $processLikeAndFollowUsers->start(function ($type, $buffer) {
                    if (Process::ERR === $type) {
                        echo 'ERR > '.$buffer;
                        return new Response("Cannot connect to Instagram, please check your params");
                    } else {
                        echo 'OUT > '.$buffer.'<br>';
                    }
                });
            }
        }
        // do while
        //  new process(like -> follow)
        // if search active
        //  new process(search)
        return true;
    }
}