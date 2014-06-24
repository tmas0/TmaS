<?php

// This file is part of TmaS
//
// TmaS is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TmaS is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TmaS. If not, see <http://www.gnu.org/licenses/>.

/**
 * An abstract DBMS.
 *
 * @author     Toni Mas
 * @package    core
 * @subpackage dbms
 * @copyright 2014 onwards Toni Mas <antoni.mas@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

abstract class dbms {
	/**
	 * Define a master topology.
	 */
	const MASTER = 'master';

	/**
	 * Define a slave topology.
	 */
	const SLAVE = 'slave';

	/**
	 * @var string The hostname or IP.
	 */
	protected $dbhost;

	/**
	 * @var string The real hostname or IP. If the dbhost contain a virtual IP (for clustering).
	 */
    protected $real_dbhost;

    /**
     * @var int The DB port.
     */
    protected $dbport;

    /**
     * @var string The DB user.
     */
    protected $dbuser;

    /**
     * @var string Password of dbuser.
     */
    protected $dbpass;

    /**
     * @var string Database name.
     */
    protected $dbname;

    /**
     * @var string DBMS version.
     */
    protected $version;

    /**
     * @var string Uptime of the DBMS.
     */
    protected $uptime;

    /**
     * @var string Master or Slave.
     */
    private $topology;

    /**
     * @var array Parameters.
     */
    protected $params;


    /**
     * Constructor.
     *
     * @param const $topology MASTER or SLAVE.
     * @param string $dbhost The dbhost.
     * @param string $dbport The dbport.
     * @param string $dbname The database name.
     * @param string $dbuser The dbuser.
     * @param string $dbpass The password for dbuser.
     */
    public function __construct($topology, $dbhost, $dbport, $dbname, $dbuser, $dbpass) {
        if ($topology === self::MASTER or $topology === self::SLAVE) {
            $this->topology = $topology;
        } else {
            $this->topology = self::MASTER; // By default.
        }

        $this->set_host($dbhost);
        $this->set_port($dbport);
        $this->set_dbname($dbname);
        $this->set_user($dbuser);
        $this->set_password($dbpass);
    }

    /**
     * Connect to DBMS and get/set some parameters and info.
     */
    abstract protected function connect();

    /**
     * Get DB host.
     *
     * @return string dbhost.
     */
    public function get_host() {
    	return $this->dbhost;
    }

    /**
     * Set DB host.
     *
     * @param string The DB host.
     */
    public function set_host($dbhost) {
    	if (!empty($dbhost) and is_string($dbhost)) {
            if ( ip2long($dbhost) === false ) {
                $dbhost = gethostbyname($dbhost);
            }
            $this->dbhost = $dbhost; // Set only IP.
        }
    }

    /**
     * Get DB port.
     *
     * @return string Port.
     */
    public function get_port() {
    	return $this->dbport;
    }

    /**
     * Set DB Port.
     *
     * @param int The DB port.
     */
    public function set_port($dbport) {
    	if (!empty($dbport) and is_integer($dbport) and strlen($dbport) == 4) {
        	$this->dbport = $dbport;
        }
    }

    /**
     * Get DB name.
     *
     * @return string DB name.
     */
    public function get_dbname() {
    	return $this->dbname;
    }

    /**
     * Set DB name.
     *
     * @param string The DB name.
     */
    public function set_dbname($dbname) {
    	if (!empty($dbname) and is_string($dbname)) {
        	$this->dbname = trim($dbname);
        }
    }

    /**
     * Get DB user.
     *
     * @return string DB user.
     */
    public function get_user() {
    	return $this->dbuser;
    }

    /**
     * Set DB user.
     *
     * @param string The DB user.
     */
    public function set_user($dbuser) {
    	if (!empty($dbuser) and is_string($dbuser)) {
        	$this->dbuser = $dbuser;
        }
    }

    /**
     * Set DB password.
     *
     * @param string The DB password.
     */
    public function set_password($dbpass) {
    	if (!empty($dbpass) and is_string($dbpass)) {
        	$this->dbpass = $dbpass;
        }
    }

    /**
     * Get DBMS version.
     *
     * @return string Version.
     */
    public function version() {
    	return $this->version;
    }

    /**
     * Get DBMS Up time.
     *
     * @return string Uptime.
     */
    public function uptime() {
    	return $this->uptime;
    }

    /**
     * Get topology.
     *
     * @return string Topology.
     */
    public function topology() {
    	return $this->topology;
    }

    /**
	 * Set Pgpool params.
	 *
	 * @param array DBMS params.
	 */
	public function set_params($params) {
		if (is_array($params)) {
			$this->params = $params;
		} else {
			$this->params = (array)$params;
		}
	}

	/**
	 * Return all DBMS params.
	 *
	 * @return array All params.
	 */
	public function get_params() {
		if (!empty($this->params)) {
			return $this->params;
		}
	}

	/**
	 * Return specific value.
	 *
	 * @param string Key
	 * @return string Value or false if key not exists.
	 */
	public function get_param($key) {
		if (!empty($this->params)) {
            if (array_key_exists($key, $this->params)) {
                return $this->params[$key];
            }
        }
        return false;
	}

    /**
     * Get records, one and/or more.
     *
     * @param string $sql SQL.
     * @param array $params Parameters of this sql.
     * @param int $limitfrom
     * @param int $limitnum Limit of records.
     * @return array Result.
     */
    abstract public function get_records($sql, array $params=null, $limitfrom=0, $limitnum=0);

    /**
     * Restructure the SQL params.
     *
     * @param string $sql SQL
     * @param array Params.
     * @return array Restructured params.
     */
    public function fix_sql_params($sql, array $params=null) {
        $params = (array)$params;

        foreach ($params as $key => $value) {
            $params[$key] = is_bool($value) ? (int)$value : $value;
        }

        $q_count     = substr_count($sql, '?');

        $count = 0;
        if ($q_count) {
            $type = SQL_PARAMS_QM;
            $count = $q_count;

        }

        if (!$count) {
            return array($sql, array());
        }

        if ($count > count($params)) {
            throw new app_exception('Invalid params');
        }

        if ($count == count($params)) {
            return array($sql, array_values($params));
        }
    }

    /**
     * Telnet to specific port.
     *
     * @return bool Connect or not.
     */
    public function telnet () {
        // Allow the script to hang around waiting 2 seconds for connections.
        set_time_limit(2);

        // Turn on implicit output flushing so we see what we're getting as it comes in.
        ob_implicit_flush();

        if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
            return false;
        }

        if (socket_bind($sock, $this->dbhost, $this->dbport) === false) {
            return false;
        }
        return true;

        if (socket_listen($sock, 5) === false) {
            return false;
        }

        do {
            if (($msgsock = socket_accept($sock)) === false) {
                return false;
                break;
            }
            /* Send instructions. */
            $msg = "\nWelcome to the PHP Test Server. \n" .
                "To quit, type 'quit'. To shut down the server type 'shutdown'.\n";
            socket_write($msgsock, $msg, strlen($msg));

            do {
                if (false === ($buf = socket_read($msgsock, 2048, PHP_NORMAL_READ))) {
                    return false;
                    break 2;
                }
                if (!$buf = trim($buf)) {
                    continue;
                }
                if ($buf == 'quit') {
                    break;
                }
                if ($buf == 'shutdown') {
                    socket_close($msgsock);
                    break 2;
                }
                $talkback = "PHP: You said '$buf'.\n";
                socket_write($msgsock, $talkback, strlen($talkback));
                echo "$buf\n";
            } while (true);
            socket_close($msgsock);
        } while (true);

        socket_close($sock);
        return true;
    }

    public function out() {
    	print_r($this);
    }
}
