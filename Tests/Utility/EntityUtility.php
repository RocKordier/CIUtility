<?php

namespace EHDev\Utility\CIUtility\Tests\Utility;

use PHPUnit\Framework\TestCase;

/**
 * Class EntityUtility
 *
 * @package EHDev\Utility\CIUtility\Tests\Utility
 */
class EntityUtility extends TestCase
{
    /**
     * Inspect Entity for correct setters and getters
     *
     * @param $entityName
     * @param $data
     *
     * @return mixed
     */
    public function inspectEntity($entityName, $data)
    {
        $class        = new \ReflectionClass($entityName);
        $objectGlobal = new $entityName();

        foreach ($data as $key => $value) {
            $object       = new $entityName();
            $getterPrefix = $this->getGetterPrefix($value);
            $setterPrefix = $this->getSetterPrefix($value);
            $value        = $this->getValue($value);

            /** Check if getter and setter can be invoke */
            $this->assertTrue($class->hasProperty($key), $key.' is not a valid property');
            $this->assertTrue($class->hasMethod($getterPrefix.ucfirst($key)), $key.' is missing a getter');
            $this->assertTrue($class->hasMethod($setterPrefix.ucfirst($key)), $key.' is missing a setter');

            /** Get the setter method and invoke it with the value in our data array */
            $setter = $class->getMethod($setterPrefix.ucfirst($key));
            $setter->invoke($object, $value);
            $setter->invoke($objectGlobal, $value);

            /** Get the getter method and invoke it */
            $getter   = $class->getMethod($getterPrefix.ucfirst($key));
            $objValue = $getter->invoke($object);
            $this->assertEquals($value, $objValue, 'Getter or Setter has modified the data.');
        }
        foreach ($data as $key => $value) {
            $getterPrefix = $this->getGetterPrefix($value);
            $value        = $this->getValue($value);
            $getter       = $class->getMethod($getterPrefix.ucfirst($key));
            $this->assertEquals($value, $getter->invoke($objectGlobal), 'A Setter has Modified another Value');
        }

        return $objectGlobal;
    }

    /**
     * Set a protected property by reflection
     *
     * @param        $instance
     * @param        $value
     * @param string $propertyName
     */
    public function setProperty($instance, $value, $propertyName = 'id')
    {
        $reflectionClass    = new \ReflectionClass(get_class($instance));
        $reflectionProperty = $reflectionClass->getProperty($propertyName);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($instance, $value);
    }

    /**
     * @param $array
     *
     * @return mixed|string
     */
    private function getGetterPrefix($array)
    {
        if (is_array($array) && array_key_exists('getterPrefix', $array)) {
            return $array['getterPrefix'];
        }

        if ($this->getValue(is_bool($array))) {
            return 'is';
        }

        return 'get';
    }

    /**
     * @param $array
     *
     * @return mixed|string
     */
    private function getSetterPrefix($array)
    {
        if (is_array($array) && array_key_exists('setterPrefix', $array)) {
            return $array['setterPrefix'];
        }

        return 'set';
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    private function getValue($value)
    {
        if (is_array($value)) {
            return $value['value'];
        }

        return $value;
    }
}
