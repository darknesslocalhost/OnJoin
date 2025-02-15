<?php

namespace darknesspl\OnJoin;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TF;

class OnJoinCommand extends Command {

    private OnJoin $plugin;

    public function __construct(OnJoin $plugin) {
        parent::__construct("onjoin", "Manage OnJoin settings", null, ["oj"]);
        $this->setPermission("onjoin.config");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if (!$this->testPermissionSilent($sender)) {
            $sender->sendMessage(TF::RED . "You don't have permission to use this command.");
            return;
        }

        if (!isset($args[0])) {
            $sender->sendMessage("§cType /onjoin help to see the list of commands.");
            return;
        }

        switch ($args[0]) {
            case "help":
                $this->showHelp($sender);
                break;

            case "joinmessage":
                $this->setJoinMessage($sender, $args);
                break;

            case "quitmessage":
                $this->setQuitMessage($sender, $args);
                break;

            case "settype":
                $this->setMessageType($sender, $args);
                break;

            case "reload":
                $this->reloadConfig($sender);
                break;

            default:
                $sender->sendMessage(TF::RED . "Unknown subcommand. Type /onjoin help for more information.");
                break;
        }
    }

    private function showHelp(CommandSender $sender): void {
        $sender->sendMessage(
            TF::RED . "/onjoin help > " . TF::YELLOW . "List all available commands.\n" .
            TF::RED . "/onjoin joinmessage <text> > " . TF::YELLOW . "Set a new Join message.\n" .
            TF::RED . "/onjoin quitmessage <text> > " . TF::YELLOW . "Set a new Quit message.\n" .
            TF::RED . "/onjoin settype <message/tip> > " . TF::YELLOW . "Change the type of Join/Quit messages.\n" .
            TF::RED . "/onjoin reload > " . TF::YELLOW . "Reload the plugin configuration."
        );
    }

    private function setJoinMessage(CommandSender $sender, array $args): void {
        array_shift($args);
        if (empty($args)) {
            $sender->sendMessage(TF::RED . "Please provide a message.");
            return;
        }
        $joinMessage = implode(" ", $args);
        $this->plugin->getConfig()->set("joinMessage", $joinMessage);
        $this->plugin->getConfig()->save();
        $this->plugin->configdata["joinMessage"] = $joinMessage;

        $preview = str_replace("{PLAYER}", $sender instanceof Player ? $sender->getName() : "Player", $joinMessage);
        $sender->sendMessage(TF::GREEN . "Join message has been updated to:\n" . TF::YELLOW . $preview);
    }

    private function setQuitMessage(CommandSender $sender, array $args): void {
        array_shift($args);
        if (empty($args)) {
            $sender->sendMessage(TF::RED . "Please provide a message.");
            return;
        }
        $quitMessage = implode(" ", $args);
        $this->plugin->getConfig()->set("quitMessage", $quitMessage);
        $this->plugin->getConfig()->save();
        $this->plugin->configdata["quitMessage"] = $quitMessage;

        $preview = str_replace("{PLAYER}", $sender instanceof Player ? $sender->getName() : "Player", $quitMessage);
        $sender->sendMessage(TF::GREEN . "Quit message has been updated to:\n" . TF::YELLOW . $preview);
    }

    private function setMessageType(CommandSender $sender, array $args): void {
        if (!isset($args[1])) {
            $sender->sendMessage(TF::RED . 'Invalid input. Use "/onjoin settype <message/tip>".');
            return;
        }
        if (!in_array($args[1], ["message", "tip"], true)) {
            $sender->sendMessage(TF::RED . 'Invalid input. The value should be "message" or "tip".');
            return;
        }
        $this->plugin->getConfig()->set("type", $args[1]);
        $this->plugin->getConfig()->save();
        $this->plugin->configdata["type"] = $args[1];
        $sender->sendMessage(TF::GREEN . "Join/Quit message type has been changed to: " . TF::YELLOW . $args[1]);
    }

    private function reloadConfig(CommandSender $sender): void {
        $this->plugin->getConfig()->reload();
        $this->plugin->configdata = $this->plugin->getConfig()->getAll();
        $sender->sendMessage(TF::GREEN . "Configuration reloaded successfully.");
    }
}
