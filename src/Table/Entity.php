<?php

namespace bemang\Database\Table;

abstract class Entity
{
    //TODO : mettre dans le générateur
    public function getClassName(): string
    {
        return __CLASS__;
    }
}
