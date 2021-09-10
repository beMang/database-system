<?php

namespace bemang\Database\Table;

/**
 * Représente une entité/ligne de la base de donnée
 * Par facilité, dans la bdd, chaque table aura un id
 */
abstract class Entity
{
    private $id;

    /**
     * Permet de récupérer les attributs de l'entité sous forme de tableau
     *
     * @return array
     */
    final public function getAttribuesAsArray(): array
    {
        $attributes = get_object_vars($this);
        unset($attributes['id']);
        foreach ($attributes as $name => $value) {
            $reflexion = new \ReflectionProperty($this->getEntityClassName(), $name);
            $declaringClassName = $reflexion->getDeclaringClass()->getName();
            $arrayNames = explode('\\', $declaringClassName);
            if ($arrayNames[count($arrayNames) - 2] != 'Entities') { //Comportement inconnu
                unset($attributes[$name]);
            }
        }
        $attributes['id'] = $this->getId();
        return $attributes;
    }

    /**
     * Récupère le nom de la classe de l'entité
     *
     * @return string
     */
    final public function getEntityClassName(): string
    {
        return get_class($this);
    }

    /**
     * Récupère l'ID de l'entité
     *
     * @return int
     */
    final public function getId(): int
    {
        if (!is_int($this->id)) {
            $this->id = intval($this->id);
        }
        return $this->id;
    }

    /**
     * Modifie l'ID
     *
     * @param integer $id
     * @return void
     */
    final public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * Vide l'ID de l'entité
     *
     * @return void
     */
    final public function emptyId()
    {
        $this->id = null;
    }
}
