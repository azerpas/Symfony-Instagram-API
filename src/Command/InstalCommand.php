<?php

namespace App\Command;

use InstagramAPI\Response\Model\FriendshipStatus;
use InstagramAPI\Response\Model\UnpredictableKeys\FriendshipStatusUnpredictableContainer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class InstalCommand extends Command
{
    protected static $defaultName = 'instaChecker';

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('username', InputArgument::REQUIRED, 'Instagram Username')
            ->addArgument('password', InputArgument::REQUIRED, 'Instagram Password')
            ->addOption('only', null, InputOption::VALUE_REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io            = new SymfonyStyle($input, $output);
        $lastFollowers = file('./last_followers', FILE_IGNORE_NEW_LINES);
        $only = $input->getOption('only');
        $ig = $this->getInstagramInstance($input->getArgument('username'), $input->getArgument('password'));

        $currentFollowers = $this->getCurrentFollowers($ig);
        $subscriptions    = $this->getCurrentSubscriptions($ig);
        if ($only === false || $only === 'stats') {
            $unfollowers      = $this->getUnfollowers($lastFollowers, $currentFollowers);
            if (count($unfollowers)) $this->postSlackNotification("Unfollowers (" . count($unfollowers) . "):\n" . implode(", ", $unfollowers));

            $newfollowers     = $this->getNewfollowers($lastFollowers, $currentFollowers);
            if (count($newfollowers)) $this->postSlackNotification("New followers (" . count($newfollowers) . "):\n" . implode(", ", $newfollowers));

            file_put_contents('./last_followers', implode(PHP_EOL, $currentFollowers));
        }
        if ($only === false || $only === 'unfollow') {
            try {
                $toUnfollows = $this->unfollowNotFollowers($ig, $subscriptions, $currentFollowers);
            } catch (\Exception $exception) {
                dump($exception);
                $toUnfollows = null;
            }
            if ($toUnfollows === null || count($toUnfollows)) {
                if (!$toUnfollows) $msg = "Unfollowed: Something bad happened";
                else $msg = "Unfollowed (" . count($toUnfollows) . "):\n" . implode(", ", $toUnfollows) . "\n";
                $this->postSlackNotification($msg);
            }
        }
    }

    private function getInstagramInstance($username, $password)
    {
        /////// CONFIG ///////
        $debug          = false;
        $truncatedDebug = false;
        //////////////////////

        $ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);
        try {
            $ig->login($username, $password);

            return $ig;
        } catch (\Exception $e) {
            throw new \Exception('Something went wrong: ' . $e->getMessage());
        }
    }

    private function postSlackNotification($data)
    {
        if (strlen($data)) {
            $ch    = curl_init();
            $array = [
                CURLOPT_URL            => "https://hooks.slack.com/services/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",
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => json_encode(["text" => $data]),
                CURLOPT_HTTPHEADER     => [
                    "Content-Type" => "application/json",
                ],
            ];
            curl_setopt_array($ch, $array);
            $output = curl_exec($ch);
            curl_close($ch);
        }
    }

    private function getUnfollowers($lastFollowers, $currentFollowers)
    {
        echo "Get unfollowers\n";
        $unfollowers = [];
        foreach ($lastFollowers as $lastFollower) {
            if (!in_array($lastFollower, $currentFollowers)) {
                $unfollowers[] = $lastFollower;
            }
        }

        return $unfollowers;
    }

    private function getNewfollowers($lastFollowers, $currentFollowers)
    {
        echo "Get new followers\n";
        $newfollowers = [];
        foreach ($currentFollowers as $currentFollower) {
            if (!in_array($currentFollower, $lastFollowers)) {
                $newfollowers[] = $currentFollower;
            }
        }

        return $newfollowers;
    }

    private function getCurrentFollowers($ig)
    {
        echo "Get current followers\n";

        $uuid  = \InstagramAPI\Signatures::generateUUID();
        $maxId = null;
        $array = [];

        do {
            $response = $ig->people->getSelfFollowers($uuid, null, $maxId);

            foreach ($response->getUsers() as $item) {
                $array[] = $item->getUsername();
            }

            $maxId = $response->getNextMaxId();
            if ($maxId) {
                echo "Sleeping for 5s...\n";
                sleep(5);
            }
        } while ($maxId !== null);

        return $array;
    }

    private function getCurrentSubscriptions($ig)
    {
        echo "Get people to unfollow\n";
        $uuid  = \InstagramAPI\Signatures::generateUUID();
        $maxId = null;
        $array = [];

        do {
            /** @var $response */
            $response = $ig->people->getSelfFollowing($uuid, null, $maxId);

            foreach ($response->getUsers() as $item) {
                $array[$item->getPk()] = $item->getUsername();
                //if ($item->getUsername() === "balouterreneuve") {
                //    dump($ig->people->getFriendship($item->getPk()));
                //}
            }

            $maxId = $response->getNextMaxId();
            if ($maxId) {
                echo "Sleeping for 5s...\n";
                sleep(5);
            }
        } while ($maxId !== null);

        return $array;
    }

    private function unfollowNotFollowers($ig, $subscriptions, $followers)
    {
        $toUnfollow = array_diff($subscriptions, $followers);

        echo "Unfollowing " . count($toUnfollow) . " people over " . count($subscriptions) . "...\n";
        $i = 0;
        foreach ($toUnfollow as $pk => $username) {
            /** @var FriendshipStatus $response */
            $response = $ig->people->unfollow($pk)->getFriendshipStatus();
            if ($response->isFollowing()) {
                throw new \Exception('unfollow failed');
            }
            echo ".";
            sleep(rand(12, 22));

            if (++$i % 199 === 0) {
                echo "\nAvoid throttling by Instagram, waiting 1 hour...\n";
                sleep(3601);
                echo "Restarting process\n";
            }
        }

        return $toUnfollow;
    }
}
