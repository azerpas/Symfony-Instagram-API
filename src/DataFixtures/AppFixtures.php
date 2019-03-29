<?php

namespace App\DataFixtures;

use App\Entity\Account;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $usernames = ["BlackenFantasy",
            "Boyacheck",
            "Cajoance",
            "ChronoTop",
            "Clownerve",
            "CookyJame",
            "Dicared",
            "Dragicsn",
            "Estoyser",
            "ExclusiveMellow",
            "Freebug",
            "Froggianes",
            "Gettympyl",
            "GreatTwilight",
            "Hempaderre",
            "Hollady",
            "Informeria",
            "Jamergroba",
            "KenjiCrescent",
            "LyfeInlove",
            "PuffGossip",
            "QuayleShoes",
            "Rottner",
            "Scaptuka",
            "Sloothe",
            "Telendt",
            "Tinnysiskn",
            "Toolsberg",
            "TrickedHell",
            "Upperalex"];
        for($i = 0;$i<$usernames;$i++){
            $account = new Account();
            $account->setUsername($usernames[$i]);
            $account->setPassword(mt_rand(1000000,10000000));
            $account->setBlacklist(unserialize('{}'));
            $account->setSearchSettings(unserialize('{}'));
            $account->setSettings(unserialize('{}'));
            $manager->persist($account);
        }
        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }
}
