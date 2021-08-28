<?php

namespace bemang\Database\Table;

abstract class Entity
{
    private $id;

    final public function getAttribuesAsArray(): array
    {
        $attributes = get_object_vars($this);
        unset($attributes['id']);
        foreach ($attributes as $name => $value) {
            $reflexion = new \ReflectionProperty($this->getEntityClassName(), $name);
            $declaringClassName = $reflexion->getDeclaringClass()->getName();
            $arrayNames = explode('\\', $declaringClassName);
            if ($arrayNames[count($arrayNames) - 2] != 'Entities') {
                unset($attributes[$name]);
            }
        }
        $attributes['id'] = $this->getId();
        return $attributes;
    }

    final public function getEntityClassName()
    {
        return get_class($this);
    }

    final public function getId()
    {
        return $this->id;
    }

    final public function setId(int $id)
    {
        $this->id = $id;
    }

    final public function emptyId()
    {
        $this->id = null;
    }
}
