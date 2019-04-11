<?php

namespace Drupal\epsilon_harmony;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a Contact entity.
 *
 * We have this interface so we can join the other interfaces it extends.
 *
 * @ingroup content_entity_example
 */
interface EpslionLogInterface extends ContentEntityInterface, EntityOwnerInterface {

}
