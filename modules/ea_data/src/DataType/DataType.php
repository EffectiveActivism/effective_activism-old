<?php

/**
 * @file
 * Contains \Drupal\ea_data\DataType\DataType.
 */

class DataType implements DataTypeInterface {

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return $this->type;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->created;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime(int $timestamp) {
    $this->created = $timestamp;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getValue() {
    return $this->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setValue($value) {
    $this->value = $value;
    return $this;
  }

}
