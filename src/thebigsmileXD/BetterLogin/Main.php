<?php

namespace thebigsmileXD\BetterLogin;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\utils\TextFormat;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;

class Main extends PluginBase implements Listener{
	public $loggedInPlayers = [];
	public $database = '';

	public function onEnable(){
		$this->makeSaveFiles();
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->connectSQL();
	}

	private function makeSaveFiles(){
		$this->saveDefaultConfig();
		$this->reloadConfig();
		$this->saveResource("config.yml", false);
		$this->getConfig()->setNested("sql", array("host" => "localhost","user" => "root","password" => "bqkAWQ4MC","database" => "_289918_2","port" => 3306));
		$this->getConfig()->save();
	}

	/* input handling */
	public function onCommand(CommandSender $sender, Command $command, $label, array $args){
		if($sender instanceof Player || $sender instanceof ConsoleCommandSender){
			switch($command->getName()){
				case "getip":
					{
						if($sender->hasPermission("betterlogin.getip")){
							if(count($args) > 0){
								if(count($args) === 1){
									$players = array($args[0]);
								}
								else{
									$players = split($args, " ");
								}
							}
							return $this->getIP($players, $sender);
						}
						else{
							$sender->sendMessage($this->getTranslation("no-permission"));
							return true;
						}
					}
				case "getemail":
					{
						if($sender->hasPermission("betterlogin.getemail")){
							if(count($args) > 0){
								if(count($args) === 1){
									$players = array($args[0]);
								}
								else{
									$players = split($args, " ");
								}
							}
							return $this->getEmail($players, $sender);
						}
						else{
							$sender->sendMessage($this->getTranslation("no-permission"));
							return true;
						}
						return false;
					}
				case "delete":
					{
						if($sender->hasPermission("betterlogin.delete")){
							if(count($args) === 1){
								return $this->delete($args[0], true, $sender);
							}
							else{
								$sender->sendMessage($this->getTranslation("invalid-arguments"));
							}
						}
						else{
							$sender->sendMessage($this->getTranslation("no-permission"));
							return true;
						}
						return false;
					}
				case "setgroupcolor":
					{
						if($sender->hasPermission("betterlogin.setgroupcolor")){
							if(count($args) == 2 && $args[1] instanceof TextFormat){
								$this->setGroupColor($args[0], $args[1], $sender);
							}
							else{
								$sender->sendMessage($this->getTranslation("invalid-arguments"));
							}
							return true;
						}
						else{
							$sender->sendMessage($this->getTranslation("no-permission"));
							return true;
						}
						return false;
					}
				case "nameban":
					{
						if($sender->hasPermission("betterlogin.nameban")){
							if(count($args) === 1){
								return $this->nameBan($args[0], $sender);
							}
							else{
								$sender->sendMessage($this->getTranslation("invalid-arguments"));
							}
						}
						else{
							$sender->sendMessage($this->getTranslation("no-permission"));
							return true;
						}
						return false;
					}
				case "tempban":
					{
						if($sender->hasPermission("betterlogin.tempban")){
							if(count($args) === 1){
								return $this->tempBan($args[0], null, $sender);
							}
							elseif(count($args) === 2){
								return $this->tempBan($args[0], $args[1], $sender);
							}
							else{
								$sender->sendMessage($this->getTranslation("invalid-arguments"));
							}
						}
						else{
							$sender->sendMessage($this->getTranslation("no-permission"));
							return true;
						}
						return false;
					}
				case "about":
					{
						if($sender->hasPermission("betterlogin.about")){
							if(count($args) === 1){
								return $this->about($args[0], $sender);
							}
							else{
								$sender->sendMessage($this->getTranslation("invalid-arguments"));
							}
						}
						else{
							$sender->sendMessage($this->getTranslation("no-permission"));
							return true;
						}
						return false;
					}
				case "ipban":
					{
						if($sender->hasPermission("betterlogin.ipban")){
							if(count($args) === 1){
								return $this->IPBan($args[0], $sender);
							}
							else{
								$sender->sendMessage($this->getTranslation("invalid-arguments"));
							}
						}
						else{
							$sender->sendMessage($this->getTranslation("no-permission"));
							return true;
						}
						return false;
					}
				case "tempipban":
					{
						if($sender->hasPermission("betterlogin.tempipban")){
							if(count($args) === 1){
								return $this->tempIPBan($args[0], null, $sender);
							}
							elseif(count($args) === 2){
								return $this->tempIPBan($args[0], $args[1], $sender);
							}
							else{
								$sender->sendMessage($this->getTranslation("invalid-arguments"));
							}
						}
						else{
							$sender->sendMessage($this->getTranslation("no-permission"));
							return true;
						}
						return false;
					}
				case "removeban":
					{
						if($sender->hasPermission("betterlogin.removeban")){
							if(count($args) === 1){
								return $this->removeBan($args[0], $sender);
							}
							else{
								$sender->sendMessage($this->getTranslation("invalid-arguments"));
							}
						}
						else{
							$sender->sendMessage($this->getTranslation("no-permission"));
							return true;
						}
						return false;
					}
				default:
					{
						
						if($sender instanceof Player){
							switch($command->getName()){
								case "login":
									{
										if($sender->hasPermission("betterlogin.login")){
											if(count($args) === 1){
												return $this->login($sender, $args[0], true);
											}
											else
												return false;
										}
										else{
											$sender->sendMessage($this->getTranslation("no-permission"));
											return true;
										}
									}
								case "signup":
									{
										if($sender->hasPermission("betterlogin.signup")){
											if(count($args) === 2){
												return $this->signup($sender, $args[0], $args[1]);
											}
											else
												return false;
										}
										else{
											$sender->sendMessage($this->getTranslation("no-permission"));
											return true;
										}
									}
								case "changeemail":
									{
										if($sender->hasPermission("betterlogin.changeemail")){
											return $this->changeEmail($sender, true);
										}
										else{
											$sender->sendMessage($this->getTranslation("no-permission"));
											return true;
										}
									}
								case "changepassword":
									{
										if($sender->hasPermission("betterlogin.changepassword")){
											return $this->changePassword($sender, trim($args[0]), true);
										}
										else{
											$sender->sendMessage($this->getTranslation("no-permission"));
											return true;
										}
									}
								default:
									return false;
							}
						}
						elseif($sender instanceof ConsoleCommandSender){
							return false;
						}
					}
			}
		}
		else{
			return false;
		}
	}

	public function onChat(PlayerChatEvent $event){
		$sender = $event->getPlayer();
		$message = $event->getMessage();
		if(/*($this->isLoggedIn($sender) && $this->getConfig()->getNested("settings.protect-chat") === "true") || */(!$this->isLoggedIn($sender)/* && $this->getConfig()->getNested("settings.use-chat-login") === "true"*/)){
			// test if password send
			if(trim($message) === "team") $this->login($sender, trim($message));
			else $sender->sendMessage($this->getTranslation("incorrect-password"));
			$event->setMessage("");
			$event->setCancelled();
			return true;
		}
	}

	public function isLoggedIn(Player $player){
		if(in_array($player->getName(), $this->loggedInPlayers)){
			#if(array_key_exists($player->getName(), $this->loggedInPlayers)){
				return true;
			#}
		}
		return false;
	}

	/* functions */
	public function getTranslation($string){
		return $string;
	}

	public function login(Player $player, $password){
		if($password === "team"){
			$player->sendMessage($this->getTranslation("logged-in"));
			$this->loggedInPlayers[] = $player->getName();
		}
		else{
			$player->sendMessage($this->getTranslation("incorrect-password"));
		}
		return true;
	}

	public function signup(Player $player, $email, $password){
		if(!empty($this->database)){
			$statement = $this->database->prepare("SELECT `name` FROM `mcpe_users` WHERE `username` = ?");
			$statement->bind_param('s', $player->getName());
			$statement->execute();
			$statement->bind_result($returned_name);
			$results = count($statement->fetch());
			$statement->free_result();
			if($results !== 0){
				$player->sendMessage($this->getTranslation("already-registered"));
				return;
			}
			else{
				$statement = $this->database->prepare("INSERT INTO `mcpe_users` VALUES ?,?,?,?,?,?,?,?,?,?,?");
				$statement->bind_param('s', $player->getName());
				$statement->execute();
				$statement->bind_result($returned_name);
				$results = count($statement->fetch());
				$statement->free_result();
			}
			return true;
		}
		return false;
	}

	public function getIP($players){
		return true;
	}

	public function changeEmail($player, $isPlayer = false){
		return true;
	}

	public function changePassword($player, $password, $isPlayer = false){
		return true;
	}

	public function connectSQL(){
		$host = $this->getConfig()->getNested("sql.host");
		$user = $this->getConfig()->getNested("sql.user");
		$password = $this->getConfig()->getNested("sql.password");
		$database = $this->getConfig()->getNested("sql.database");
		$port = $this->getConfig()->getNested("sql.port");
		$port = empty($port)?3306:$port;
		$db = new \mysqli($host, $user, $password, $database, $port);
		if($db->connect_errno > 0){
			$this->getLogger()->critical('Unable to connect to database [' . $db->connect_error . ']');
			return false;
		}
		else{
			$this->database = $db;
			$this->getLogger()->info('Successfully connected to database [' . $database . ']');
		}
		$request = "CREATE TABLE `mcpe_players` ( `id` INT NOT NULL AUTO_INCREMENT , `name` VARCHAR(30) NOT NULL , `displayname` VARCHAR(255) NULL , `email` VARCHAR(255) NOT NULL , `password` VARCHAR(255) NOT NULL , `registered_since` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , `last_login` TIMESTAMP NULL , `last_logout` TIMESTAMP NULL , `online` TINYINT NOT NULL DEFAULT '0' , `last_ip` VARCHAR(15) NULL , `confirmed` TINYINT NULL , PRIMARY KEY (`id`), UNIQUE (`name`), UNIQUE (`email`)) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;";
		if(!$result = $this->database->query($request)){
			$this->getLogger()->critical('There was an error running the query [' . $this->database->error . ']');
		}
		else{
			$this->getLogger()->notice('Successfully created database "mcpe_players"');
		}
		/*
		 * $sql = <<<SQL
		 * SELECT *
		 * FROM `users`
		 * WHERE `live` = 1
		 * SQL;
		 *
		 * if(!$result = $db->query($sql)){
		 * die('There was an error running the query [' . $db->error . ']');
		 * }
		 *
		 * while($row = $result->fetch_assoc()){
		 * echo $row['username'] . '<br />';
		 * }
		 * echo 'Total rows updated: ' . $db->affected_rows;
		 * $result->free();
		 * $db->real_escape_string('This is an unescaped "string"');
		 * $db->close();
		 * $statment = $db->prepare("SELECT `name` FROM `users` WHERE `username` = ?");
		 * $name = 'Bob';
		 * $statement->bind_param('s', $name);
		 * $statement->execute();
		 * $statement->bind_result($returned_name);
		 * while($statement->fetch()){
		 * echo $returned_name . '<br />';
		 * }
		 * $statement->free_result();
		 */
	}

	/* eventhandler */
	
	// Chat event already handled above
	public function onMove(PlayerMoveEvent $event){
		if(!$this->isLoggedIn($event->getPlayer())) $event->setCancelled();
		return;
	}

	public function onInteract(PlayerInteractEvent $event){
		if(!$this->isLoggedIn($event->getPlayer())) $event->setCancelled();
		return;
	}

	public function onBreak(BlockBreakEvent $event){
		if(!$this->isLoggedIn($event->getPlayer())) $event->setCancelled();
		return;
	}

	public function onPlace(BlockPlaceEvent $event){
		if(!$this->isLoggedIn($event->getPlayer())) $event->setCancelled();
		return;
	}

	public function onItemConsume(PlayerItemConsumeEvent $event){
		if(!$this->isLoggedIn($event->getPlayer())) $event->setCancelled();
		return;
	}

	public function onQuit(PlayerQuitEvent $event){
		$this->loggedInPlayers[$event->getPlayer()->getName()] = null;
		unset($this->loggedInPlayers[$event->getPlayer()->getName()]);
		return;
	}

	public function onPlayerCommand(PlayerCommandPreprocessEvent $event){
		if(!$this->isLoggedIn($event->getPlayer())){
			$message = $event->getMessage();
			if($message{0} === "/"){ // Command
				$event->setCancelled(true);
				$command = substr($message, 1);
				$args = explode(" ", $command);
				if($args[0] === "signup" or $args[0] === "login" /* or $args[0] === "help"*/){
					$this->getServer()->dispatchCommand($event->getPlayer(), $command);
				}
				else{
					$this->sendAuthenticateMessage($event->getPlayer());
				}
			}
		}
	}
}