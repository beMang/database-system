<?php

namespace bemang\Database;

use bemang\ConfigInterface;

class DBManager
{
    protected $pdoInstances = [];
    protected $configInstance;
    static protected $selfInstance = null;

    public function __construct(ConfigInterface $config = null)
    {
        if (!is_null($config)) {
            $this->setConfig($config);
            if ($this->getConfig()->has('database.host') === true &&
                $this->getConfig()->has('database.user') === true &&
                $this->getConfig()->has('database.password') === true ) {
                    $this->addDatabase(
                        'default',
                        $this->getConfig()->get('database.host'),
                        $this->getConfig()->get('database.user'),
                        $this->getConfig()->get('database.password')
                    );
            }
            self::$selfInstance = $this;
        }
    }

    public static function getInstance(ConfigInterface $config = null)
    {
        if (is_null(self::$selfInstance)) {
            if (!is_null($config)) {
                self::$selfInstance = new DBManager($config);
            } else {
                throw new \Exception('Aucune configuration donnée'); //TODO : exception database
            }
        } else {
            return self::$selfInstance;
        }
    }

    public function addDatabase($name, $hostAndDb = 'mysql:host=localhost;dbname=test', $user = 'root', $passwd = '') :bool
    {
        if (is_string($name) && !empty($name)) {
            if ($this->dataBaseExist($name) == false) {
                $pdoInstance = new \PDO(
                    $hostAndDb,
                    $user,
                    $passwd,
                    [\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"]
                );
                $pdoInstance->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION); //Affichage des erreurs
                $this->pdoInstances [$name] = $pdoInstance;
                return true;
            } else {
                throw new \RuntimeException('La base de donnée existe déjà.');
            }
        } else {
            throw new \InvalidArgumentException('L\'identifiant de la db doit être une chaine de caractères non-vides');
        }
    }

    public function getDatabase($name) :\PDO
    {
        if (is_string($name)) {
            if (isset($this->pdoInstances[$name])) {
                return $this->pdoInstances[$name];
            } else {
                throw new \RuntimeException('La base de donnée est inexistante');
            }
        } else {
            throw new \InvalidArgumentException('L\'identifiant doit être une chaine de caractères');
        }
    }

    public function dataBaseExist($name) :bool
    {
        if (is_string($name)) {
            if (isset($this->pdoInstances[$name])) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function sql($sqlQuery, $params = false, $db = 'default')
    {
        $db = $this->getDatabase($db);
        $query = $db->prepare($sqlQuery);
        try {
            if ($params == false) {
                $query->execute();
            } else {
                $query->execute($params);
            }
        } catch (\PDOException $e) {
            throw new \Exception('Error sql');
        }
        $query->setFetchMode(\PDO::FETCH_OBJ);
        return $query->fetchAll();
    }

    public function setConfig(ConfigInterface $config)
    {
        $this->configInstance = $config;
    }

    public function getConfig()
    {
        return $this->configInstance;
    }
}
