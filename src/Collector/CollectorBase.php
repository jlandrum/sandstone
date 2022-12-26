<?php

/**
 * A collector's goal is to, given a path, return a value for 
 * a given object. It is entirely up to the collector to know
 * how to parse the object and derive the data, as well as is
 * responsible for access checks.
 */
abstract class CollectorBase {
  public abstract function getValue(string $id, string $path);
}