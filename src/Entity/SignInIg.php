<?php
// src/Entity/SignInIg.php
namespace App\Entity;

class SignInIg
{
    protected $username;
    protected $password;

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($usr)
    {
        $this->username = $usr;
    }
    public function getPassword(){
        return $this->password;
    }
    public function setPassword($pwd){
        $this->password = $pwd;
    }
}