<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
 */

declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol;

use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use Ramsey\Uuid\UuidInterface;
use function count;

class CraftingEventPacket extends DataPacket implements ServerboundPacket{
	public const NETWORK_ID = ProtocolInfo::CRAFTING_EVENT_PACKET;

	public int $windowId;
	public int $windowType;
	public UuidInterface $recipeUUID;
	/** @var ItemStackWrapper[] */
	public array $input = [];
	/** @var ItemStackWrapper[] */
	public array $output = [];

	/**
	 * @generate-create-func
	 * @param ItemStackWrapper[] $input
	 * @param ItemStackWrapper[] $output
	 */
	public static function create(int $windowId, int $windowType, UuidInterface $recipeUUID, array $input, array $output) : self{
		$result = new self;
		$result->windowId = $windowId;
		$result->windowType = $windowType;
		$result->recipeUUID = $recipeUUID;
		$result->input = $input;
		$result->output = $output;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void{
		$this->windowId = $in->getByte();
		$this->windowType = $in->getVarInt();
		$this->recipeUUID = $in->getUUID();

		$size = $in->getUnsignedVarInt();
		if($in->getProtocolId() >= ProtocolInfo::PROTOCOL_1_16_220){
			for($i = 0; $i < $size and $i < 128; ++$i){
				$this->input[] = ItemStackWrapper::read($in);
			}
		}else{
			for($i = 0; $i < $size and $i < 128; ++$i){
				$this->input[] = ItemStackWrapper::legacy($in->getItemStackWithoutStackId());
			}
		}

		$size = $in->getUnsignedVarInt();
		if($in->getProtocolId() >= ProtocolInfo::PROTOCOL_1_16_220){
			for($i = 0; $i < $size and $i < 128; ++$i){
				$this->output[] = ItemStackWrapper::read($in);
			}
		}else{
			for($i = 0; $i < $size and $i < 128; ++$i){
				$this->output[] = ItemStackWrapper::legacy($in->getItemStackWithoutStackId());
			}
		}
	}

	protected function encodePayload(PacketSerializer $out) : void{
		$out->putByte($this->windowId);
		$out->putVarInt($this->windowType);
		$out->putUUID($this->recipeUUID);

		$out->putUnsignedVarInt(count($this->input));
		if($out->getProtocolId() >= ProtocolInfo::PROTOCOL_1_16_220){
			foreach($this->input as $item){
				$item->write($out);
			}
		}else{
			foreach($this->input as $item){
				$out->putItemStackWithoutStackId($item->getItemStack());
			}
		}

		$out->putUnsignedVarInt(count($this->output));
		if($out->getProtocolId() >= ProtocolInfo::PROTOCOL_1_16_220){
			foreach($this->output as $item){
				$item->write($out);
			}
		}else{
			foreach($this->output as $item){
				$out->putItemStackWithoutStackId($item->getItemStack());
			}
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleCraftingEvent($this);
	}
}
