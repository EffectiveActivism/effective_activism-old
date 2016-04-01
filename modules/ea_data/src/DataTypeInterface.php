<?php

/**
 * @file
 * Contains \Drupal\ea_data\DataTypeInterface.
 */


namespace Drupal\ea_data;

/**
 * Provides an interface for defining Data entities.
 *
 * @ingroup ea_data
 */
interface DataTypeInterface {

  private $type;

  private $created;

  private $value;

  /**
   * Gets the Data type.
   *
   * @return string
   *   The Data type.
   */
  public function getType();

  /**
   * Gets the Data creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Data.
   */
  public function getCreatedTime();

  /**
   * Sets the Data creation timestamp.
   *
   * @param int $timestamp
   *   The Data creation timestamp.
   *
   * @return \Drupal\ea_data\DataInterface
   *   The called Data object.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the data value.
   * 
   * @return \Drupal\ea_data\DataValue
   *   The value of the data object.
   */
  public function getValue();

  /**
   * Sets the data value.
   * 
   * @param \Drupal\ea_data\DataValue
   *   A data value.
   * 
   * @return \Drupal\ea_data\DataTypeInterface
   *   The called Data object.
   */
  public function setValue($value);
}
