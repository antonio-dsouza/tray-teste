<?php

namespace App\DTOs;

abstract class BaseDTO
{
    protected static function prepareData(array $data): array
    {
        return $data;
    }

    public static function fromArray(array $data): static
    {
        $data = static::prepareData($data);
        
        $reflection = new \ReflectionClass(static::class);
        $constructor = $reflection->getConstructor();
        
        if (!$constructor) {
            $class = static::class;
            return new $class();
        }
        
        $parameters = $constructor->getParameters();
        $args = [];
        
        foreach ($parameters as $parameter) {
            $name = $parameter->getName();
            $args[] = $data[$name] ?? null;
        }
        
        $class = static::class;
        return new $class(...$args);
    }

    public function toArray(): array
    {
        $reflection = new \ReflectionClass($this);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
        
        $array = [];
        foreach ($properties as $property) {
            $value = $property->getValue($this);
            
            if ($value instanceof \DateTimeInterface) {
                $value = $value->format('Y-m-d H:i:s');
            }
            
            $array[$property->getName()] = $value;
        }
        
        return $array;
    }
}