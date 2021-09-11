<?php

namespace bemang\Database\Manager;

use bemang\ConfigInterface;
use bemang\Database\Builder\Query;
use bemang\Database\Exceptions\DBManagerException;

/**
 * Permet la gestion de multiple base de donnée
 */
class DBManager
{
    protected array $pdoInstances = [];
    protected ConfigInterface $configInstance;
    protected static ?DBManager $selfInstance = null;

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

    /**
     * Récupère une instance (globale) du manager
     *
     * @return DBManager
     */
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
     * Ajouter une base de donnée au manager
     *
     * @param string $name Le nom de la base de donnée
     * @param string $hostAndDb
     * @return boolean
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
     * Récupère une bdd du manager
     *
     * @param string $name
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
     * Vérifie si une base de donnée existe
     *
     * @param string $name Le nom à vérifier
     * @return boolean
     */
    public function dataBaseExist(string $name): bool
    {
        if (isset($this->pdoInstances[$name])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Supprime une base de donnée du manager
     *
     * @param string $name La base de donnée à supprimer
     * @return boolean
     */
    public function removeDatabase(string $name): bool
    {
        if ($this->dataBaseExist($name)) {
            unset($this->pdoInstances[$name]);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Execute une requête sql sur une base de donnée
     *
     * @param string $sqlQuery La requête sql
     * @param string $db La base de donnée sur laquelle la requête s'effectuera
     * @param array|null $params Les valeurs éventuelles
     * @return \PDOStatement
     */
    public function sql(string $sqlQuery, string $db, array $params = null): \PDOStatement
    {
        $db = $this->getDatabase($db);
        $query = $db->prepare($sqlQuery);
        if (!$params) {
            $query->execute();
        } else {
            $query->execute($params);
        }
        return $query;
    }

    /**
     * Modifie la configuration utilisée par le manager
     *
     * @param ConfigInterface $config
     * @return void
     */
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

    /**
     * Réinitialise le manager
     *
     * @return boolean
     */
    public function reset(): bool
    {
        self::$selfInstance = null;
        return is_null(self::$selfInstance);
    }

    public function getBuilder(): Query
    {
        return new Query();
    }
}
