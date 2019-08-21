<?php

namespace bemang\Database;

use bemang\ConfigInterface;

class DBManager
{
    protected $pdoInstances = [];
    protected $configInstance;
    protected static $selfInstance = null;

    public function __construct(ConfigInterface $config)
    {
        $this->setConfig($config);
        if ($this->getConfig()->has('databases') === true) {
            foreach ($this->getConfig()->get('databases') as $databaseName => $databaseInfos) {
                if (is_array($databaseInfos) 
                && isset($databaseInfos[0])
                && isset($databaseInfos[1])
                && isset($databaseInfos[2])) {
                    $this->addDatabase(
                        $databaseName,
                        $databaseInfos[0],
                        $databaseInfos[1],
                        $databaseInfos[2]
                    );
                } else {
                    throw new \Exception("La bdd $databaseName est mal configurée", 1);
                }
            }
            //ATTENTION : vérifier les champs de databases
        } else {
            throw new \Exception('Le champ databases n\'existe pas dans cette configuration');
        }
        self::$selfInstance = $this;
    }

    public static function getInstance() :DBManager
    {
        if (is_null(self::$selfInstance)) {
            throw new \Exception('Le manager doit d\'abord être configuré avant d\'être utilisé'); //TODO : exception database
        } else {
            return self::$selfInstance;
        }
    }

    /**
     * Permet de configurer le manager
     *
     * @param ConfigInterface $config
     * @return bool
     */
    public static function config(ConfigInterface $config) :bool
    {
        $instance = new DBManager($config);
        return !is_null(DBManager::getInstance());
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

    protected function setConfig(ConfigInterface $config)
    {
        $this->configInstance = $config;
    }

    public function getConfig()
    {
        return $this->configInstance;
    }
}
