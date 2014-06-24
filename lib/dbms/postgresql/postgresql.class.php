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
 * Specific implementation for PostgreSQL DBMS.
 *
 * @author     Toni Mas
 * @package    core
 * @subpackage dbms
 * @copyright  2014 onwards Toni Mas <antoni.mas@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir.'/dbms/dbms.class.php');

class postgresql extends dbms {

    /**
     * @var int DB port.
     */
    protected $dbport = 5432;

    /**
     * @var object PostgreSQL manager.
     */
    protected $pgsql = null;

    /**
     * Constructor.
     *
     * @param const $topology MASTER or SLAVE.
     * @param string $dbhost The dbhost.
     * @param string $dbport The dbport.
     * @param string $dbname The database name.
     * @param string $dbuser The dbuser.
     * @param string $dbpass The password for dbuser.
     * @param bool $connect Connect to the DBMS.
     */
    public function __construct($topology = dbms::MASTER, $dbhost = 'localhost', $dbport = 5432, $dbname = 'postgres', $dbuser = 'postgres', $dbpass, $connect = false) {
        parent::__construct($topology, $dbhost, $dbport, $dbname, $dbuser, $dbpass);

        // Connect to DBMS.
        if ($connect) {
            $this->connect();
        }
    }

    /**
     * Destructor.
     */
    public function __destruct() {
        pg_close($this->pgsql);
    }

    /**
     * Connect to DB.
     * Set and get all DBMS parameters.
     */
    public function connect() {
        // Connection string.
        $connection = "host='$this->dbhost' port='$this->dbport' user='$this->dbuser' password='$this->dbpass' dbname='$this->dbname' connect_timeout='1'";
        echo $connection;exit;
        while (! $this->pgsql = pg_connect($connection) ) {
            echo 'connectando';
        }

        // DBMS version.
        $this->version = pg_version($this->pgsql)['server'];

        // Uptime DBMS.
        $sql = 'select date_trunc(\'minute\', current_timestamp - pg_postmaster_start_time()) as uptime';
        $uptime = $this->get_records($sql);
        $uptime = current($uptime);
        $this->uptime = $this->format_uptime($uptime->uptime);

        // Get all PostgreSQL params.
        $sql = 'show all';
        $pgparams = $this->psql($sql);
        $this->set_params($this->clean($pgparams));
    }

    /**
     * Test connection to DB.
     *
     * @return bool Connect or not to DB.
     */
    public function test_connection() {
        return $this->telnet();
    }

    /**
     * If the PostgreSQL service use an generic IP service, this function return
     * the real IP of this.
     *
     * @return string Hostname or IP.
     */
    public function get_real_dbhost() {
        $sql = 'select inet_server_addr()';
        $dbhost = $this->get_records($sql);
        $dbhost = current($dbhost);
        $this->real_dbhost = $dbhost->inet_server_addr;
        return $this->real_dbhost;
    }

    /**
     * Return the IP of service.
     *
     * @return array IP and port.
     */
    public function get_serviceip() {
        $conn = array();
        $conn['dbhost'] = $this->dbhost;
        $conn['dbport'] = $this->dbport;
        return $conn;
    }

    /**
     * Format uptime result to human readable.
     *
     * @param string Uptime.
     * @return string Formatted string.
     */
    private function format_uptime($time) {
        if (!empty($time) ) {
            $result = '';
            $parts = explode(' ', $time);
            // Have a days and hours+minutes.
            if (count($parts) == 2) {
                $result .= $parts[0] .'d ';
                $times = explode(':', $parts[1]);
                // 3 elements hours:minutes:seconds. Ignore last part.
                $result .= $times[0].'h '.$times[1].'m';
                return $result;
            }
        }

        // FIXME: call exception.
        return $time;
    }

    public function insert($table, $params) {
        if (!is_array($params)) {
            $params = (array)$params;
        }

        $fields = implode(',', array_keys($params));
        $values = array();
        $i = 1;
        foreach ($params as $value) {
            $values[] = "\$".$i++;
        }
        $values = implode(',', $values);
        $sql = "INSERT INTO $table ($fields) VALUES ($values)";

        $result = pg_query_params($this->pgsql_local, $sql, $params);

        pg_free_result($result);
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
    public function get_records($sql, array $params=null, $limitfrom=0, $limitnum=0) {
        $limitfrom = (int)$limitfrom;
        $limitnum  = (int)$limitnum;
        $limitfrom = ($limitfrom < 0) ? 0 : $limitfrom;
        $limitnum  = ($limitnum < 0)  ? 0 : $limitnum;
        if ($limitfrom or $limitnum) {
            if ($limitnum < 1) {
                $limitnum = "ALL";
            } else if (PHP_INT_MAX - $limitnum < $limitfrom) {
                // This is a workaround for weird max int problem.
                $limitnum = "ALL";
            }
            $sql .= " LIMIT $limitnum OFFSET $limitfrom";
        }

        list($sql, $params) = $this->fix_sql_params($sql, $params);
        $result = pg_query_params($this->pgsql, $sql, $params);
        
        $numrows = pg_num_fields($result);

        $rows = pg_fetch_all($result);
        pg_free_result($result);

        $return = array();
        if ($rows) {
            foreach ($rows as $row) {
                $id = reset($row);
                if (isset($return[$id])) {
                    $colname = key($row);
                }
                $return[$id] = (object)$row;
            }
        }

        return $return;
    }

    /**
     * Direct psql client prompt.
     *
     * @param string SQL
     * @return string Result.
     */
    public function psql($sql) {
        if (!empty($sql) and is_string($sql) ) {
            $cmd = 'psql -h '.$this->dbhost.' -p '.$this->dbport.' -U '.$this->dbuser.' -t -c "'.$sql.'"';
            unset($out);
            exec($cmd, $out);
            return $out;
        }
        return false;
    }

    /**
     * PgPool exist.
     *
     * @return object PgPool class, if it's exist.
     */
    public function pgpool_exist() {

        // Para comprobar si existe PgPool, con la connexión mediante pg de PHP no basta,
        // ya que el comando show pool_status únicamente se ejecuta cuando se connecta
        // mediante el cliente psql. REVISAR EN UN FUTURO.

        $sql = 'show pool_status;';
        $out = $this->psql($sql);

        if (is_array($out) and count($out) > 0) {
            $pgpool = new pgpool();
            $pgpool->set_params($this->clean($out));
            return $pgpool;
        }
        return false;
    }

    /**
     * Clean and format psql output.
     *
     * @return array
     */
    private function clean($params) {
        $cleaned = array();
        foreach ($params as $param) {
            $part = explode('|', $param);
            $key = (string)trim($part[0]);
            $value = '';
            if (array_key_exists(1, $part)) {
                $value = trim($part[1]);
            }
            $cleaned[$key] = $value;
        }
        return $cleaned;
    }

    /**
     * Have a replication topology.
     * Streamming replication/ ...
     *
     * @return string Hostname o IP.
     */
    public function have_slave() {
        $sql = 'SELECT * FROM pg_stat_replication';
        $slave = $this->get_records($sql);
        if (!empty($slave)) {
            // The get_records return array, in this case, only one record. Get this.
            $params = current($slave);
            if (isset($params->client_addr)) {
                return $params->client_addr;
            }
        }
        return false; // Only master node.
    }

    /**
     * Same host?
     *
     * @return bool True is $host is same $this->real_dbhost.
     */
    private function soy_yo($host) {
        // $host must be IP value.
        if ( ip2long($host) === false ) {
            $host = gethostbyname($host);
        }
        if ($host == $this->real_dbhost) {
            return true;
        }
        return false;
    }

    /**
     * Return if this server is master.
     *
     * @return bool
     */
    public function is_master() {
        return ($this->topology() === dbms::MASTER) ? true : false;
    }
}
