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

namespace pocketmine\network\mcpe\protocol\types\inventory;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\types\GetTypeIdFromConstTrait;

class UseItemTransactionData extends TransactionData{
	use GetTypeIdFromConstTrait;

	public const ID = InventoryTransactionPacket::TYPE_USE_ITEM;

	public const ACTION_CLICK_BLOCK = 0;
	public const ACTION_CLICK_AIR = 1;
	public const ACTION_BREAK_BLOCK = 2;

	private int $actionType;
	private BlockPosition $blockPosition;
	private int $face;
	private int $hotbarSlot;
	private ItemStackWrapper $itemInHand;
	private Vector3 $playerPosition;
	private Vector3 $clickPosition;
	private int $blockRuntimeId;

	public function getActionType() : int{
		return $this->actionType;
	}

	public function getBlockPosition() : BlockPosition{
		return $this->blockPosition;
	}

	public function getFace() : int{
		return $this->face;
	}

	public function getHotbarSlot() : int{
		return $this->hotbarSlot;
	}

	public function getItemInHand() : ItemStackWrapper{
		return $this->itemInHand;
	}

	public function getPlayerPosition() : Vector3{
		return $this->playerPosition;
	}

	public function getClickPosition() : Vector3{
		return $this->clickPosition;
	}

	public function getBlockRuntimeId() : int{
		return $this->blockRuntimeId;
	}

	protected function decodeData(PacketSerializer $stream) : void{
		$this->actionType = $stream->getUnsignedVarInt();
		$this->blockPosition = $stream->getBlockPosition();
		$this->face = $stream->getVarInt();
		$this->hotbarSlot = $stream->getVarInt();
		if($stream->getProtocolId() >= ProtocolInfo::PROTOCOL_1_16_220){
			$this->itemInHand = ItemStackWrapper::read($stream);
		}else{
			$this->itemInHand = ItemStackWrapper::legacy($stream->getItemStackWithoutStackId());
		}
		$this->playerPosition = $stream->getVector3();
		$this->clickPosition = $stream->getVector3();
		$this->blockRuntimeId = $stream->getUnsignedVarInt();
	}

	protected function encodeData(PacketSerializer $stream) : void{
		$stream->putUnsignedVarInt($this->actionType);
		$stream->putBlockPosition($this->blockPosition);
		$stream->putVarInt($this->face);
		$stream->putVarInt($this->hotbarSlot);
		if($stream->getProtocolId() >= ProtocolInfo::PROTOCOL_1_16_220){
			$this->itemInHand->write($stream);
		}else{
			$stream->putItemStackWithoutStackId($this->itemInHand->getItemStack());
		}
		$stream->putVector3($this->playerPosition);
		$stream->putVector3($this->clickPosition);
		$stream->putUnsignedVarInt($this->blockRuntimeId);
	}

	/**
	 * @param NetworkInventoryAction[] $actions
	 */
	public static function new(array $actions, int $actionType, BlockPosition $blockPosition, int $face, int $hotbarSlot, ItemStackWrapper $itemInHand, Vector3 $playerPosition, Vector3 $clickPosition, int $blockRuntimeId) : self{
		$result = new self;
		$result->actions = $actions;
		$result->actionType = $actionType;
		$result->blockPosition = $blockPosition;
		$result->face = $face;
		$result->hotbarSlot = $hotbarSlot;
		$result->itemInHand = $itemInHand;
		$result->playerPosition = $playerPosition;
		$result->clickPosition = $clickPosition;
		$result->blockRuntimeId = $blockRuntimeId;
		return $result;
	}
}
