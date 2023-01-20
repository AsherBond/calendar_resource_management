<?php

declare(strict_types=1);
/**
 * @copyright Copyright (c) 2020 Georg Ehrke
 *
 * @author Georg Ehrke <georg-nextcloud@ehrke.email>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */
namespace OCA\CalendarResourceManagement\Connector\Room;

use OCA\CalendarResourceManagement\Db;
use OCP\Calendar\IMetadataProvider;
use OCP\Calendar\Room\IBackend;
use OCP\Calendar\Room\IRoom;
use OCP\Calendar\Room\IRoomMetadata;

/**
 * Class Resource
 *
 * @package OCA\CalendarResourceManagement\Connector\Room
 */
class Room implements IRoom, IMetadataProvider {
	/** @var Db\RoomModel */
	protected $entity;

	/** @var Db\StoryModel */
	protected $storyEntity;

	/** @var Db\BuildingModel */
	protected $buildingEntity;

	/** @var array */
	private $restrictions;

	/** @var Backend */
	private $backend;

	/**
	 * Room constructor.
	 *
	 * @param Db\RoomModel $entity
	 * @param Db\StoryModel $storyEntity
	 * @param Db\BuildingModel $buildingEntity
	 * @param array $restrictions
	 * @param Backend $backend
	 */
	public function __construct(Db\RoomModel $entity,
								Db\StoryModel $storyEntity,
								Db\BuildingModel $buildingEntity,
								array $restrictions,
								Backend $backend) {
		$this->entity = $entity;
		$this->storyEntity = $storyEntity;
		$this->buildingEntity = $buildingEntity;
		$this->restrictions = $restrictions;
		$this->backend = $backend;
	}

	/**
	 * @return IBackend
	 */
	public function getBackend(): IBackend {
		return $this->backend;
	}

	/**
	 * @return string
	 */
	public function getDisplayName(): string {
		return $this->entity->getDisplayName();
	}

	/**
	 * @return string
	 */
	public function getEMail(): string {
		return $this->entity->getEmail();
	}

	/**
	 * @return array
	 */
	public function getGroupRestrictions(): array {
		return $this->restrictions;
	}

	/**
	 * @return string
	 */
	public function getId(): string {
		return $this->entity->getUid();
	}

	/**
	 * @return array
	 */
	public function getAllAvailableMetadataKeys(): array {
		$keys = [];

		if ($this->entity->getRoomType()) {
			$keys[] = IRoomMetadata::ROOM_TYPE;
		}
		if ($this->entity->getCapacity()) {
			$keys[] = IRoomMetadata::CAPACITY;
		}
		if ($this->entity->getRoomNumber()) {
			$keys[] = IRoomMetadata::BUILDING_ROOM_NUMBER;
		}
		$keys[] = IRoomMetadata::BUILDING_ADDRESS;
		$keys[] = IRoomMetadata::BUILDING_STORY;
		if ($this->getFeatures() !== '') {
			$keys[] = IRoomMetadata::FEATURES;
		}

		return $keys;
	}

	/**
	 * @param string $key
	 * @return string|null
	 */
	public function getMetadataForKey(string $key): ?string {
		switch ($key) {
			case IRoomMetadata::ROOM_TYPE:
				return $this->entity->getRoomType();

			case IRoomMetadata::CAPACITY:
				return (string) $this->entity->getCapacity();

			case  IRoomMetadata::BUILDING_ROOM_NUMBER:
				return $this->entity->getRoomNumber();

			case IRoomMetadata::BUILDING_ADDRESS:
				return $this->buildingEntity->getAddress();

			case IRoomMetadata::BUILDING_STORY:
				return $this->storyEntity->getDisplayName();

			case IRoomMetadata::FEATURES:
				return $this->getFeatures();

			default:
				return null;
		}
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public function hasMetadataForKey(string $key): bool {
		return \in_array($key, $this->getAllAvailableMetadataKeys(), true);
	}

	/**
	 * @return string
	 */
	private function getFeatures():string {
		$features = [];

		if ($this->entity->getHasPhone()) {
			$features[] = 'PHONE';
		}
		if ($this->entity->getHasVideoConferencing()) {
			$features[] = 'VIDEO-CONFERENCING';
		}
		if ($this->entity->getHasTv()) {
			$features[] = 'TV';
		}
		if ($this->entity->getHasProjector()) {
			$features[] = 'PROJECTOR';
		}
		if ($this->entity->getHasWhiteboard()) {
			$features[] = 'WHITEBOARD';
		}
		if ($this->entity->getIsWheelchairAccessible()) {
			$features[] = 'WHEELCHAIR-ACCESSIBLE';
		}

		return implode(',', $features);
	}
}
