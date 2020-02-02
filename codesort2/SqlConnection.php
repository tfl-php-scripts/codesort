<?php
/*
 * File: SqlConnection.php
 * Author: Jay Pipes
 * Updated: 2007-02-15 by Jenny Ferenc
 * Link: http://www.jpipes.com/index.php?/archives/99-MySQL-Connection-Management-in-PHP-How-Not-To-Do-Things.html
 *
 * SqlConnection Class for MySQL databases
 * 
 * Encapsulates a simple API for database related activities.
 * 
 * @access      public
 */

class SqlConnection
{
    public $_host = '';
    public $_user = '';
    public $_pass = '';
    public $_name = '';
    public $port = 3306;
    public $_Cnn;
    public $_Results = [];
    public $_Res = false;
    public $_NumQueries = 0;

    /**
     * SqlConnection private constructor
     */
    private function __construct()
    {

    }

    /**
     * Get the single instance of SqlConnection object.
     *
     * @param string  (optional) Host name (Server name)
     * @param string  (optional) User Name
     * @param string  (optional) User Password
     * @param string  (optional) Database Name
     *
     * @return self
     */
    public static function self()
    {
        /** @var self $instance */
        static $instance;

        if (!isset($instance)) {
            $object = __CLASS__;
            $instance = new $object;

            if (func_num_args() === 4) {
                $host = func_get_arg(0);
                $user = func_get_arg(1);
                $pass = func_get_arg(2);
                $name = func_get_arg(3);
                $instance->setConnectionParams($host, $user, $pass, $name);
            } else {
                $instance = new $object;
            }
        }

        return $instance;
    }

    /**
     * Set db connection parameters.
     *
     * @param $host
     * @param $user
     * @param $pass
     * @param $name
     * @return  void
     */
    private function setConnectionParams($host, $user, $pass, $name)
    {
        $this->_host = $host;
        $this->_user = $user;
        $this->_pass = $pass;
        $this->_name = $name;
    }

    /**
     * @return mysqli_result
     */
    public function getLastResult()
    {
        return end($this->_Results);
    }

    /**
     * Attempt to connect the resource based on supplied parameters.
     *
     * @return  boolean
     */
    private function connect()
    {
        if (func_num_args() === 4) {

            // A different database has been requested other than the
            // standard global config settings

            $this->setConnectionParams(func_get_arg(1), func_get_arg(2), func_get_arg(3), func_get_arg(4));
        }

        /**
         * Short circuit out when already
         * connected.  To reconnect, pass
         * args again
         */
        if (is_resource($this->_Cnn) && func_num_args() !== 4) {
            return true;
        }

        if (!$this->_Cnn = mysqli_connect($this->_host, $this->_user, $this->_pass, '', $this->port)) {
            trigger_error('Could not connect to database server.', E_USER_ERROR);
            echo mysqli_error($this->_Cnn);
            return false;
        }

        if (!mysqli_select_db($this->_Cnn, $this->_name)) {
            trigger_error('Could not connect to specified database on server.', E_USER_ERROR);
            return false;
        }

        //$this->Execute("SET NAMES utf8");
        return true;
    }

    /**
     * Executes the supplied SQL statement and returns
     * the result of the call.
     *
     * @param string  SQL to execute
     * @param mixed  (optional) Error message (string) to trigger on failure, null to suppress any error
     * @return  bool
     */
    public function execute($sqlQuery, $err_msg = 'Could not execute query.')
    {
        /* Auto-connect to database */
        if (!$this->_Cnn) {
            $this->connect();
        }

        $this->_NumQueries++;

        if (!$this->_Res = mysqli_query($this->_Cnn, $sqlQuery)) {
            if ($err_msg !== null) {
                trigger_error($err_msg, E_USER_WARNING);
                echo mysqli_error($this->_Cnn);
            }
            return false;
        }

        if ($this->_Res !== false) {
            $this->_Results[] = $this->_Res;
        }

        return true;
    }

    /**
     * Reads into an array the current
     * record in the result.
     *
     * @return  mixed
     */
    public function readRecord()
    {
        if (!$this->getLastResult()) {
            return false;
        }

        return mysqli_fetch_assoc($this->getLastResult());
    }

    /**
     * Returns an single record array from the
     * current result resource.
     * Returns empty array if no retrieval
     *
     * Frees result resource.
     *
     * Optionally, you can supply a SQL
     * string to short-cut a call to
     * SqlConnection::Execute
     *
     * @param string  (optional) SQL to execute
     * @return  mixed
     */
    public function getRecord()
    {
        // Look for a SQL string supplied
        if (func_num_args() === 1) {
            $this->execute(func_get_arg(0));
        }

        if (!$this->getLastResult()) {
            return array();
        }

        $return = mysqli_fetch_assoc($this->getLastResult());
        $this->freeResult();
        return $return;
    }

    /**
     * Returns first data point from
     * current result resource
     * or false if no retrieval
     *
     * Frees result resource.
     *
     * Optionally, you can supply a SQL
     * string to short-cut a call to
     * SqlConnection::Execute
     *
     * @param string  (optional) SQL to execute
     * @return  mixed
     */
    public function getFirstCell()
    {
        // Look for a SQL string supplied
        if (func_num_args() === 1) {
            $this->execute(func_get_arg(0));
        }

        if (!$this->getLastResult()) {
            return false;
        }

        $row = mysqli_fetch_row($this->getLastResult());
        $this->freeResult();
        return $row[0];
    }

    /**
     * Returns last inserted auto-id
     *
     * @return  mixed
     */
    public function getLastSequence()
    {
        return mysqli_insert_id($this->_Cnn);
    }

    /**
     * Returns number of rows in resultset
     *
     * @return  int
     */
    public function getNumRows()
    {
        $lastResult = $this->getLastResult();
        if ($lastResult !== null) {
            return mysqli_num_rows($lastResult);
        }

        return 0;
    }

    /**
     * Returns number of rows affected by DML statement
     *
     * @return  int
     */
    public function getAffectedRows()
    {
        return mysqli_affected_rows($this->_Cnn);
    }

    /**
     * Free result set
     *
     * @return void
     */
    public function freeResult()
    {
        if ($this->getLastResult() !== null) {
            mysqli_free_result(array_pop($this->_Results));
        }
    }

    /**
     * Real escape or else adds slashes for insert into DB
     *
     * @param string  String to escape
     * @return  mixed
     */
    public function escape($value)
    {
        /* Auto-connect to database */
        if (!$this->_Cnn) {
            $this->connect();
        }

        //check if this function exists
        if (function_exists('mysqli_real_escape_string')) {
            $value = mysqli_real_escape_string($this->_Cnn, $value);
        } //for PHP version < 4.3.0 use addslashes
        else {
            $value = addslashes($value);
        }

        $value = trim($value);

        return $value;
    }
}
