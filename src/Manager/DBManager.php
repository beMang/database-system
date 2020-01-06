<?php

namespace bemang\Database\Manager;

use bemang\ConfigInterface;
use bemang\Database\Exceptions\DBManagerException;

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
                if (
                    is_array($databaseInfos)
                    && isset($databaseInfos[0])
                    && isset($databaseInfos[1])
                    && isset($databaseInfos[2])
                ) {
                    $this->addDatabase(
                        $databaseName,
                        $databaseInfos[0],
                        $databaseInfos[1],
                        $databaseInfos[2]
                    );
                } else {
                    throw new DBManagerException("La bdd $databaseName est mal configurée", 1);
                }
            }
        } else {
            throw new DBManagerException('Le champ databases n\'existe pas dans cette configuration');
        }
        self::$selfInstance = $this;
    }

    public static function getInstance(): DBManager
    {
        if (is_null(self::$selfInstance)) {
            throw new DBManagerException('Le manager doit d\'abord être configuré avant d\'être utilisé');
        } else {
            return self::$selfInstance;
        }
    }

    /**
     * Configure le manager
     *
     * @param ConfigInterface $config
     * @return bool
     */
    public static function config(ConfigInterface $config): bool
    {
        $instance = new DBManager($config);
        return !is_null(DBManager::getInstance());
    }
    
    /**
     * Ajoute une bdd au manager
     *
     * bdd en utf8 et affichage des exceptions
     *
     * @return bool
     */
    public function addDatabase(
        string $name,
        $hostAndDb = 'mysql:host=localhost;dbname=test',
        $user = 'root',
        $passwd = ''
    ): bool {
        if (!empty($name)) {
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
                throw new DBManagerException('La base de donnée existe déjà.');
            }
        } else {
            throw new DBManagerException('L\'identifiant de la db ne peut pas être vide');
        }
    }


    /**
     * Récupère une bdd
     *
     * @return \PDO
     */
    public function getDatabase(string $name): \PDO
    {
        if (isset($this->pdoInstances[$name])) {
                return $this->pdoInstances[$name];
        } else {
            throw new DBManagerException('La base de donnée est inexistante');
        }
    }

    /**
     * Vérifie si une bdd existe
     *
     * @return bool
     */
    public function dataBaseExist($name): bool
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

    public function sql($sqlQuery, $db = 'default', array $params = null, $mode = \PDO::FETCH_OBJ)
    {
        $db = $this->getDatabase($db);
        $query = $db->prepare($sqlQuery);
        try {
            if (!$params) {
                $query->execute();
            } else {
                $query->execute($params);
            }
        } catch (\PDOException $e) {
            throw new DBManagerException($e->getMessage());
        }
        $query->setFetchMode($mode);
        return $query->fetchAll();
    }

    protected function setConfig(ConfigInterface $config)
    {
        $this->configInstance = $config;
    }

    /**
     * Récupère la configuration utilisée par le manager
     *
     * @return ConfigInterface
     */
    public function getConfig()
    {
        return $this->configInstance;
    }
}
