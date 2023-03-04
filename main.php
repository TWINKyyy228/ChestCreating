<?php
namespace VirusA;

use pocketmine\entity\Entity;
use pocketmine\Player;
use \pocketmine\event\player\PlayerInteractEvent; 
use \pocketmine\event\player\PlayerDropItemEvent; 
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\event\Listener;
use pocketmine\level\Position;
use pocketmine\event\inventory\InventoryCloseEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\entity\Human;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\NamedTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\scheduler\CallbackTask;
use pocketmine\tile\Tile;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\event\TranslationContainer;
use pocketmine\level\Location;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\InteractPacket;
use pocketmine\network\mcpe\protocol\types\InventoryNetworkIds;
use pocketmine\network\mcpe\protocol\protocolInfo;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\BlockEntityDataPacket;
use pocketmine\network\mcpe\protocol\ContainerClosePacket;
use pocketmine\network\mcpe\protocol\ContainerSetSlotPacket;
use pocketmine\network\mcpe\protocol\INVENTORY_ACTION_PACKET;
use pocketmine\network\mcpe\protocol\ContainerSetContentPacket;

class main extends PluginBase implements Listener{

	public $chest = array();
	public $id = array(); 
    public $updates = array(); 

		public function onEnable(){
		if(!is_dir($this->getDataFolder())) @mkdir($this->getDataFolder());
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
				$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(array(
				$this,
				"Update"
				)), 2);
		}
		
			public function openchest($player, $type, $name = "Сундук"){
	            $nick = strtolower($player->getName());
				if(isset($this->chest[$nick])) return false;
				if($type == "double"){
			    $pk = new UpdateBlockPacket; //Создаём пакет обновления блока
				$pk->x = (int)round($player->x);   
				$pk->y = (int)round($player->y) - (int)3;    //Указываем кординаты (на 3 блока ниже чем игрок)
				$pk->z = (int)round($player->z);
				$pk->blockId = 54;         //Айди блока (сундук)
				$pk->blockData = 5;        //Дамаг блока, в данном случае решает в какую сторону будет повёрнут сундук
				$player->dataPacket($pk);  //Отправляем пакет игроку
				
			//Второй сундук
				$pk = new UpdateBlockPacket;  //ТОЖЕ САМОЕ (Только на 1 блок дальше по кординате z)
				$pk->x = (int)round($player->x);
				$pk->y = (int)round($player->y) - (int)3;
				$pk->z = (int)round($player->z) + (int)1; 
				$pk->blockId = 54;
				$pk->blockData = 5;
				$player->dataPacket($pk); 
	
	
	         //создаём nbt тег для первого сундука
			 
				$nbt = new CompoundTag("", [
				new StringTag("id", Tile::CHEST),  //тип 
				new StringTag("CustomName", "$name"),   // Имя которое будет присвоено сундуку
				new IntTag("x", (int)round($player->x)),      //Кординаты первого сундука
				new IntTag("y", (int)round($player->y) - (int)3),
				new IntTag("z", (int)round($player->z))
				]);
				
				
					$tile1 = Tile::createTile("Chest", $player->getLevel(), $nbt); //Создаём тайл типа "Сундук" в мире игрока с тегами $nbt
		
		
		     //создаём nbt тег для второго сундука
			 
					$nbt = new CompoundTag("", [        //Всё тоже самое
					new StringTag("id", Tile::CHEST),
					new StringTag("CustomName", "$name"),
					new IntTag("x", (int)round($player->x)),
					new IntTag("y", (int)round($player->y) - (int)3),
					new IntTag("z", (int)round($player->z) + (int)1)
				]);
				
				
				    $tile2 = Tile::createTile("Chest", $player->getLevel(), $nbt);
		
		
		
					$tile1->pairWith($tile2);  //Соединяем два сундука в один большой
					$tile2->pairWith($tile1);  //Типо назначаем их парой друг друга :D хз как объяснить что такое тайл но по сути $tile = $chest	
                    $this->updates[$nick] = time() - 0; //Добавляю игрока в массив для таймера 
					$this->chest[$nick]["process"] = "open";
					for($i = 0; $i < 54; $i++){
						$this->setContent($player, Item::get(0, 0, 0), $i);
					}
					
				} elseif($type == "one"){
					 $pk = new UpdateBlockPacket; //Создаём пакет обновления блока
				$pk->x = (int)round($player->x);   
				$pk->y = (int)round($player->y) - (int)3;    //Указываем кординаты (на 3 блока ниже чем игрок)
				$pk->z = (int)round($player->z);
				$pk->blockId = 54;         //Айди блока (сундук)
				$pk->blockData = 5;        //Дамаг блока, в данном случае решает в какую сторону будет повёрнут сундук
				$player->dataPacket($pk);  //Отправляем пакет игроку
				
						$nbt = new CompoundTag("", [
				new StringTag("id", Tile::CHEST),  //тип 
				new StringTag("CustomName", "$name"),   // Имя которое будет присвоено сундуку
				new IntTag("x", (int)round($player->x)),      //Кординаты первого сундука
				new IntTag("y", (int)round($player->y) - (int)3),
				new IntTag("z", (int)round($player->z))
				]);
				
				
				$tile1 = Tile::createTile("Chest", $player->getLevel(), $nbt); //Создаём тайл типа "Сундук" в мире игрока с тегами $nbt
				$this->updates[$nick] = time() - 0; //Добавляю игрока в массив для таймера 
				$this->chest[$nick]["process"] = "open";
				for($i = 0; $i < 27; $i++){
						$this->setContent($player, Item::get(0, 0, 0), $i);
					}
				}
			    $this->chest[$nick]["x"] = (int)round($player->x);   
				$this->chest[$nick]["y"] = (int)round($player->y) - (int)3;  
				$this->chest[$nick]["z"] = (int)round($player->z);
	  }     
	  	public function Update(){ 
		  foreach($this->updates as $nick => $value){ //Получаем всех игроков которых мы записали для таймера
			  
			     $player = $this->getServer()->getPlayer($nick); 
			  	 $x = (int)round($player->x);
				 $y = (int)round($player->y)-(int)3;   
				 $z = (int)round($player->z);
						  
							   if($this->chest[$nick]["process"] == "close" and (time() - $this->updates[$nick]) > 0){
							   	$block = Server::getInstance()->getDefaultLevel()->getBlock(new Vector3($x, $y, $z));
		
									$pk = new UpdateBlockPacket;
									$pk->x = $this->chest[$nick]["x"];
									$pk->y = $this->chest[$nick]["y"];
									$pk->z = $this->chest[$nick]["z"];
									$pk->blockId = $block->getId();
									$pk->blockData = 0;
									$player->dataPacket($pk);
									
									
									
									$block = Server::getInstance()->getDefaultLevel()->getBlock(new Vector3($x, $y, $z + 1));
								
									$pk = new UpdateBlockPacket;
									$pk->x = $this->chest[$nick]["x"];
									$pk->y = $this->chest[$nick]["y"];
									$pk->z = $this->chest[$nick]["z"] + 1;
									$pk->blockId = $block->getId();
									$pk->blockData = 0;
									$player->dataPacket($pk);
							        unset($this->updates[$nick]);
									unset($this->chest[$nick]);
							   return;
							   }
							 if($this->chest[$nick]["process"] == "open" and (time() - $this->updates[$nick]) > 0){
						   $pk = new ContainerOpenPacket; //Создаём пакет открывающий контейнер(инвентарь)
						   $pk->windowid = 10;  //ид окна указываем 10 - тобишь Сундук
						   $pk->type = InventoryNetworkIds::CONTAINER; //Тип контейнер
						   $pk->x = (int)round($player->x);         //Кординаты сундука который открываем
						   $pk->y = (int)round($player->y) - (int)3;
						   $pk->z = (int)round($player->z);
						   
						   $player->dataPacket($pk); //Отправляем пакет
						     
						   unset($this->updates[$nick]); 
						   if(isset($this->scontent[$nick])){
							   $c = count($this->scontent[$nick]);
							   $n = 0;
							   foreach($this->scontent[$nick] as $key => $value){
								   $n++;
								   $fix = $this->scontent[$key][$nick];
								   $this->setContent($player,$value,$key, $fix);
								   if($n == $c){ unset($this->scontent[$nick]); break;}
								   
							   }
						   }
					}
					}
		  }
		    public function PacketReceive(DataPacketReceiveEvent $event){ //Функция эвент получения пакетов сервером от клиента
		   $player = $event->getPlayer();
		   $nick = strtolower($player->getName());
		   if($event->getPacket() instanceof ContainerClosePacket){
			  if(isset($this->chest[$nick])){
			  $this->closechest($player);
			  }
		   }
		     if($event->getPacket() instanceof ContainerSetSlotPacket){
			  $pk = $event->getPacket(); 
		 	 if(!isset($this->chest[$nick])) return false;
			  $item = $pk->item;
			  $slot = $pk->slot;
		
			  if(isset($this->fix[$nick][$slot])){
	
				  if($this->fix[$nick][$slot] == true){
				
					 $this->setContent($player, $this->content[$nick][$slot], $slot, true);
				  }
			  }
			 }
			}
			
			public function setContent($player, $item, $slot, $fix = false){
				    $nick = strtolower($player->getName());
					if(isset($this->updates[$nick])){
						$this->scontent[$nick][$slot] = $item;
						$this->scontent[$slot][$nick] = $fix;
					return;
					}
					$pk = new ContainerSetSlotPacket; //Создаём пакет установки контента в сундук
					$pk->windowid = 10;
					//$pk->targetEid = -1;
					$pk->slot = $slot;
					$pk->item = $item;
					$player->dataPacket($pk);
					$this->content[$nick][$slot] = $item;
					$this->fix[$nick][$slot] = $fix;
					
			}		
			
		  	  public function closechest($player){  
			   $nick = strtolower($player->getName());
               $this->updates[$nick] = time() - 0; 
		         if(isset($this->chest[$nick])){
				 $this->chest[$nick]["process"] = "close";
		          $pk = new ContainerClosePacket();
				  $pk->windowid = 10;           //Отправляем пакет закрытия сундука
				  $player->dataPacket($pk);
				
		 }
	
  
		  }
		  public function onTransaction(InventoryTransactionEvent $event){
			  $player = $event->getTransaction()->getPlayer();
			  $nick = strtolower($player->getName());          
		  if(isset($this->chest[$nick]) or isset($this->updates[$nick])){
			  $event->setCancelled(true);
			 
		  }
			  
		  }
		  public function drop(PlayerDropItemEvent $event){
			  $player = $event->getPlayer();
			  $nick = strtolower($player->getName()); 
			  if(isset($this->chest[$nick])) $event->setCancelled(true);
			  if(isset($this->updates[$nick])) $event->setCancelled(true);
			  
		  }
			
		}

?>