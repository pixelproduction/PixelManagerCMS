<?php

/**
 * PixelManager CMS (Community Edition)
 * Copyright (C) 2016 PixelProduction (http://www.pixelproduction.de)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *   Die Klasse enthält alle Datenbank Methoden für das CMS
 */

final class Db
{
    private static $instance = null;
    private static $pdo = null;
    private static $table_prefix = null;

    private function __construct()
    {
    }

    /**
     *   Singleton
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    private function __clone()
    {
    }

    /**
     *   Erstellt ein PDO Objekt
     *
     * @return PDO pdo
     */
    public static function getPDO()
    {
        if (self::$pdo == null) {

            $config = Config::getArray();
            if (!isset($config["database"])) {
                Helpers::fatalError("No database configuration found.");
            }

            $host = '';
            if (isset($config['database']['host'])) {
                $host = 'host=' . $config['database']['host'] . ';';
            }

            $port = '';
            if (isset($config['database']['port'])) {
                $port = 'port=' . $config['database']['port'] . ';';
            }

            $socket = '';
            if (isset($config['database']['socket'])) {
                $socket = 'unix_socket=' . $config['database']['socket'] . ';';
            }

            $dsn = 'mysql:dbname=' . $config["database"]["name"] . ';' . $host . $port . $socket . 'charset=utf8;';

            try {
                $pdo = new PDO(
                    $dsn,
                    $config["database"]["user"],
                    $config["database"]["password"],
                    array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
                );
            } catch (PDOException $e) {
                Helpers::fatalError("Could no connect to the database: " . $e->getMessage());
            }

            self::$pdo = $pdo;
        }
        return (self::$pdo);
    }

    /**
     *   Tabellen prefix f�r die Datenbank tabellen
     *
     * @retrun string $table_prefix
     */
    public static function tablePrefix()
    {
        if (self::$table_prefix == null) {
            self::$table_prefix = Config::get()->database->tablePrefix;
        }
        return (self::$table_prefix);
    }

    /**
     *   Select methode f�r die Datenbank [TODO: Bitte �berpr�fen]
     *
     * @param UTF8String $select
     * @param array      $parameters
     * @param bool       $onlyFirstRow
     * @param        PDO ::FETCH_ASSOC $fetch_style
     *
     * @return UTF8String/int/objekt Datenbank ausgabe
     */
    protected static function select($select, $parameters, $onlyFirstRow, $fetch_style = PDO::FETCH_ASSOC)
    {
        $db = self::getPDO();
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = str_replace('[prefix]', self::tablePrefix(), $select);
        try {
            $statement = $db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $statement->execute($parameters);
            $result = false;
            if ($statement->rowCount() > 0) {
                $result = array();
                if ($onlyFirstRow) {
                    $result = $statement->fetch($fetch_style);
                } else {
                    for ($i = 0; $i < $statement->rowCount(); $i++) {
                        $result[] = $statement->fetch($fetch_style);
                    }
                }
            }
            return ($result);
        } catch (PDOException $e) {
            helpers::debugError('Database error: ' . $e->getMessage());
            return (false);
        }
    }

    /**
     * @see select()
     */
    public static function get($select, $parameters = array(), $fetch_style = PDO::FETCH_ASSOC)
    {
        return (self::select($select, $parameters, false, $fetch_style));
    }

    /**
     * @see select()
     */
    public static function getFirst($select, $parameters = array(), $fetch_style = PDO::FETCH_ASSOC)
    {
        return (self::select($select, $parameters, true, $fetch_style));
    }

    /**
     *   Insert und Update methode f�r die Datenbank
     *
     * @param UTF8String $table
     * @param        $data
     * @param UTF8String $action
     * @param UTF8String $where
     * @param array      $where_parameters
     *
     * @result bool
     */
    protected static function insertOrUpdate(
        $table,
        $data,
        $action = "INSERT INTO",
        $where = "",
        $where_parameters = array()
    ) {
        $db = self::getPDO();
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = $action . " `" . self::tablePrefix() . $table . "`";
        $parameters = array();
        if (is_array($data)) {
            if (count($data) > 0) {
                $sql .= " SET ";
                $data_sql = "";
                $counter = 0;
                foreach ($data as $key => $value) {
                    if ($data_sql != "") {
                        $data_sql .= ", ";
                    }
                    // Zur Sicherheit, damit auf keinen Fall ein Platzhaltername doppelt vorkommt
                    // und sie auch nicht mit $where_parameters in Konflikt kommen k�nnen,
                    // eine zuf�llige ID generieren
                    $parameter_uniqe_id = md5(uniqid(rand(1, 99999), true) . $counter);
                    $counter++;
                    $data_sql .= "`" . $key . "` = :" . $parameter_uniqe_id;
                    $parameters[":" . $parameter_uniqe_id] = $value;
                }
                $sql .= $data_sql;
            }
        }
        if ($where != "") {
            $sql .= " WHERE " . $where;
            foreach ($where_parameters as $key => $value) {
                $parameters[$key] = $value;
            }
        }
        try {
            $statement = $db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $statement->execute($parameters);
            if ($action == "INSERT INTO") {
                return ($db->lastInsertId());
            } else {
                return (true);
            }
        } catch (PDOException $e) {
            helpers::debugError('Database error: ' . $e->getMessage());
            return (false);
        }
    }

    /**
     * @see insertOrUpdate()
     */
    public static function insert($table, $data)
    {
        return (self::insertOrUpdate($table, $data, "INSERT INTO", "", array()));
    }

    /**
     * @see insertOrUpdate()
     */
    public static function update($table, $data, $where, $where_parameters = array())
    {
        return (self::insertOrUpdate($table, $data, "UPDATE", $where, $where_parameters));
    }

    /**
     *   L�scht eintr�ge aus der Datenbank
     *
     * @param UTF8String $table
     * @param UTF8String $where
     * @param array      $where_parameters
     *
     * @result bool
     */
    public static function delete($table, $where, $where_parameters = array())
    {
        // Nur wenn in $where etwas drin steht, sonst wird die ganze Tabelle gel�scht :-(
        if ($where != "") {
            $db = self::getPDO();
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            try {
                $sql = "DELETE FROM `" . self::tablePrefix() . $table . "` WHERE " . $where;
                $statement = $db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
                $statement->execute($where_parameters);
                return (true);
            } catch (PDOException $e) {
                helpers::debugError('Database error: ' . $e->getMessage());
                return (false);
            }
        } else {
            return (false);
        }
    }

}
