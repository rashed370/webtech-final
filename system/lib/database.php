<?php
/**
 * @author MD. RASHEDUL ISLAM
 * @package Bus Ticket Reservation System
 * @version v2.0
 * @see https://github.com/rashed370/webtech-final
 */

$connection = null;

/**
 * @return mysqli|null
 */
function connectDatabase()
{
    global $connection;

    //SERVER CONFIG
    $host = '127.0.0.1';
    $port = '3306';
    $sock = '';

    //DATABASE CONFIG
    $database = 'webtech_final';
    $username = 'root';
    $password = '';

    if($connection = mysqli_connect($host, $username, $password, $database, $port, $sock))
    {
        return $connection;
    }

    execConnectionError();
    die();
}

function disconnectDatabase()
{
    global $connection;

    if($connection)
    {
        mysqli_close($connection);
    }
}

function execConnectionError()
{
    ?>
    <h3>WE COULDN'T ESTABLISH DATABASE CONNECTION</h3>
    <?php
}

//
/**
 * @param $email
 * @return bool
 */
function verifyEmailAssigned($email)
{
    global $connection;

    $query = "SELECT COUNT(*) FROM ".BTRS_DB_PREFIX.BTRS_TB_USERS." WHERE `email` = ?";
    if($stmt = mysqli_prepare($connection, $query))
    {
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        if($response = mysqli_stmt_get_result($stmt))
        {
            if($row = mysqli_fetch_row($response))
            {
                return (isset($row[0]) && $row[0]>0);
            }
        }
    }
    return false;
}

// Fetch User By Email
/**
 * @param $id
 * @param int $valid
 * @return array|null
 */
function getUserById($id, $valid = -1)
{
    global $connection;

    $query = $valid>=0 ? "SELECT * FROM ".BTRS_DB_PREFIX.BTRS_TB_USERS." WHERE `id` = ? AND `validate` = ? ORDER BY `id` DESC":"SELECT * FROM ".BTRS_DB_PREFIX.BTRS_TB_USERS." WHERE `id` = ? ORDER BY `id` DESC";

    if($stmt = mysqli_prepare($connection, $query))
    {
        if($valid>=0)
        {
            mysqli_stmt_bind_param($stmt, 'ii', $id, $valid);
        }
        else
        {
            mysqli_stmt_bind_param($stmt, 'i', $id);
        }

        mysqli_stmt_execute($stmt);
        if($response = mysqli_stmt_get_result($stmt))
        {
            if($row = mysqli_fetch_assoc($response))
            {
                if(is_array($row) && !empty($row))
                {
                    return $row;
                }
            }
        }
    }

    return null;
}

/**
 * @param $email
 * @return array|null
 */
function getUserByEmail($email)
{
    global $connection;

    $query = "SELECT * FROM ".BTRS_DB_PREFIX.BTRS_TB_USERS." WHERE `email` = ? ORDER BY `id` DESC";
    if($stmt = mysqli_prepare($connection, $query))
    {
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        if($response = mysqli_stmt_get_result($stmt))
        {
            if($row = mysqli_fetch_assoc($response))
            {
                if(is_array($row) && !empty($row))
                {
                    return $row;
                }
            }
        }
    }

    return null;
}

// Fetch Session Data
/**
 * @param $token
 * @return array|null
 */
function getSession($token)
{
    global $connection;

    $query = "SELECT * FROM ".BTRS_DB_PREFIX.BTRS_TB_AUTHSESSION." WHERE `token` = ? ORDER BY `id` DESC LIMIT 0, 1";
    if($stmt = mysqli_prepare($connection, $query))
    {
        mysqli_stmt_bind_param($stmt, 's', $token);
        mysqli_stmt_execute($stmt);
        if($response = mysqli_stmt_get_result($stmt))
        {
            if($row = mysqli_fetch_assoc($response))
            {
                if(is_array($row) && !empty($row))
                {
                    return $row;
                }
            }
        }
    }

    return null;
}

// Add New Session Data
/**
 * @param $token
 * @param $userid
 * @return bool|int
 */
function pushSession($token, $userid)
{
    global $connection;

    $expire = date('Y-m-d H:i:s', time()+BTRS_SESSION_ALIVE);
    $query = "INSERT INTO ".BTRS_DB_PREFIX.BTRS_TB_AUTHSESSION."( `userid`, `token`, `expire` ) VALUES ( ?, ?, ? )";
    if($stmt = mysqli_prepare($connection, $query))
    {
        mysqli_stmt_bind_param($stmt, 'iss', $userid, $token, $expire);
        mysqli_stmt_execute($stmt);
        return mysqli_affected_rows($connection);
    }
    return false;
}


// Remove Session Data
/**
 * @param $token
 * @return bool|int
 */
function popSession($token)
{
    global $connection;

    $query = "DELETE FROM ".BTRS_DB_PREFIX.BTRS_TB_AUTHSESSION." WHERE `token` = ?";
    if($stmt = mysqli_prepare($connection, $query))
    {
        mysqli_stmt_bind_param($stmt, 's', $token);
        mysqli_stmt_execute($stmt);
        return mysqli_affected_rows($connection);
    }

    return false;
}

// Update Session Data
/**
 * @param $token
 * @return bool|int
 */
function modifySessionValidity($token)
{
    global $connection;

    $time = date('Y-m-d H:i:s', time()+BTRS_SESSION_ALIVE);
    $query = "UPDATE ".BTRS_DB_PREFIX.BTRS_TB_AUTHSESSION." SET `expire` = ? WHERE `token` = ?";
    if($stmt = mysqli_prepare($connection, $query))
    {
        mysqli_stmt_bind_param($stmt, 'ss', $time, $token);
        mysqli_stmt_execute($stmt);
        return mysqli_affected_rows($connection);
    }
    return false;
}

/**
 * @return bool|int
 */
function cleanExpiredSession()
{
    global $connection;

    $time = date('Y-m-d H:i:s', time());
    $query = "DELETE FROM ".BTRS_DB_PREFIX.BTRS_TB_AUTHSESSION." WHERE `expire` < ?";
    if($stmt = mysqli_prepare($connection, $query))
    {
        mysqli_stmt_bind_param($stmt, 's', $time);
        mysqli_stmt_execute($stmt);
        return mysqli_affected_rows($connection);
    }

    return false;
}

/**
 * @param array $data
 * @return bool|int|string
 */
function addUser(array $data)
{
    global $connection;

    $query = "INSERT INTO ".BTRS_DB_PREFIX.BTRS_TB_USERS."( `email`, `password`, `gender`, `role`, `validate`, `registered` ) VALUES ( ?, ?, ?, ?, ?, ? )";
    if($stmt = mysqli_prepare($connection, $query))
    {
        mysqli_stmt_bind_param(
                $stmt,
                'sssiis',
                $data['email'],
                $data['password'],
                $data['gender'],
                $data['role'],
                $data['validate'],
                $data['registered']
        );

        if(mysqli_stmt_execute($stmt))
        {
            if(mysqli_affected_rows($connection))
            {
                return mysqli_insert_id($connection);
            }
        }

    }
    return false;

}

/**
 * @param array $details
 * @return bool
 */
function addUserDetails(array $details)
{
    global $connection;

    $query = "INSERT INTO ".BTRS_DB_PREFIX.BTRS_TB_USERDETAILS."(`userid`, `type`, `data`) VALUES ( ?, ?, ? )";
    if($stmt = mysqli_prepare($connection, $query))
    {
        $insert = 0;
        foreach ($details as $detail)
        {
            mysqli_stmt_bind_param( $stmt, 'iss', $detail[0], $detail[1], $detail[2]);
            if(mysqli_stmt_execute($stmt))
            {
                if(mysqli_affected_rows($connection))
                {
                   $insert++;
                }
            }
        }
        return ($insert==count($details));
    }
    return false;
}

/**
 * @param $id
 * @return bool
 */
function isValidUser($id)
{
    global $connection;

    $query = "SELECT * FROM ".BTRS_DB_PREFIX.BTRS_TB_USERS." WHERE `id` = ? ORDER BY `id` DESC LIMIT 0, 1";
    if($stmt = mysqli_prepare($connection, $query))
    {
        mysqli_stmt_bind_param($stmt, 'i', $id);
        if(mysqli_stmt_execute($stmt))
        {
            if($response = mysqli_stmt_get_result($stmt))
            {
                if($row = mysqli_fetch_assoc($response))
                {
                    if(is_array($row) && !empty($row))
                    {
                        return $row['validate']==1;
                    }
                }
            }
        }
    }
    return false;
}

/**
 * @param $userid
 * @param $type
 * @param bool $flaq
 * @return bool|mixed|string
 */
function getUserDetails($userid, $type, $flaq=false)
{
    global $connection;

    $query = "SELECT * FROM ".BTRS_DB_PREFIX.BTRS_TB_USERDETAILS." WHERE `userid` = ? AND `type` = ? ORDER BY `id` DESC LIMIT 0, 1";
    if($stmt = mysqli_prepare($connection, $query))
    {
        mysqli_stmt_bind_param($stmt, 'is', $userid, $type);
        if(mysqli_stmt_execute($stmt))
        {
            if($response = mysqli_stmt_get_result($stmt))
            {
                if($row = mysqli_fetch_assoc($response))
                {
                    if(is_array($row) && !empty($row))
                    {
                        return $flaq ? true : $row['data'];
                    }
                }
            }
        }
    }
    return $flaq ? false : '';
}

/**
 * @param $userid
 * @param $type
 * @param $data
 * @return bool|int
 */
function updateUSerDetails($userid, $type, $data)
{
    if(getUserDetails($userid, $type, true))
    {
        global $connection;

        $query = "UPDATE ".BTRS_DB_PREFIX.BTRS_TB_USERDETAILS." SET `data` = ? WHERE `userid` = ? AND `type` = ?";

        if($stmt = mysqli_prepare($connection, $query))
        {
            mysqli_stmt_bind_param($stmt, 'ss', $time, $token);
            if(mysqli_stmt_execute($stmt))
            {
                return mysqli_affected_rows($connection);
            }
        }

        return false;
    }
    else return addUserDetails(array($userid, $type, $data));
}

function totalElement($table)
{
    global $connection;

    $query = "SELECT COUNT(*) FROM ".BTRS_DB_PREFIX.$table;

    if($result = mysqli_query($connection, $query))
    {
        if($row = mysqli_fetch_row($result))
        {
            return isset($row[0]) ? $row[0] : 0;
        }
    }
    return 0;
}

function totalUsersByRole($role, $valid = 1)
{
    global $connection;

    $query = "SELECT COUNT(*) FROM ".BTRS_DB_PREFIX.BTRS_TB_USERS." WHERE `role` = ? AND `validate` = ?";

    if($stmt = mysqli_prepare($connection, $query))
    {
        mysqli_stmt_bind_param($stmt, 'ii', $role, $valid);
        if(mysqli_stmt_execute($stmt))
        {
            if($response = mysqli_stmt_get_result($stmt))
            {
                if($row = mysqli_fetch_row($response))
                {
                    return (isset($row[0]) && $row[0]>0) ? $row[0] : 0;
                }
            }
        }

    }

    return 0;
}

function totalCounterStaff($manager)
{
    global $connection;

    $role = BTRS_ROLE_COUNTER_STAFF;
    $valid = 1;

    $query = "SELECT COUNT(*) FROM ".BTRS_DB_PREFIX.BTRS_TB_USERS." WHERE `id` IN ( SELECT DISTINCT `userid` FROM ".BTRS_DB_PREFIX.BTRS_TB_USERDETAILS." WHERE `type` = 'busCounter' AND `data` IN ( SELECT `id` FROM ".BTRS_DB_PREFIX.BTRS_TB_BUSCOUNTERS." WHERE `manager` = ?))";

    if($stmt = mysqli_prepare($connection, $query))
    {
        mysqli_stmt_bind_param($stmt, 'i', $manager);
        if(mysqli_stmt_execute($stmt))
        {
            if($response = mysqli_stmt_get_result($stmt))
            {
                if($row = mysqli_fetch_row($response))
                {
                    return (isset($row[0]) && $row[0]>0) ? $row[0] : 0;
                }
            }
        }

    }

    echo mysqli_error($connection);

    return 0;
}

function getCounterStaff($manager, $offset=0, $limit=0)
{
    global $connection;

    $role = BTRS_ROLE_COUNTER_STAFF;
    $valid = 1;

    $query = "SELECT * FROM ".BTRS_DB_PREFIX.BTRS_TB_USERS." WHERE `id` IN ( SELECT DISTINCT `userid` FROM ".BTRS_DB_PREFIX.BTRS_TB_USERDETAILS." WHERE `type` = 'busCounter' AND `data` IN ( SELECT `id` FROM ".BTRS_DB_PREFIX.BTRS_TB_BUSCOUNTERS." WHERE `manager` = ?)) ORDER BY `id` DESC ".( $limit>0 ? "LIMIT ?, ?" : "" );

    $users = array();

    if($stmt = mysqli_prepare($connection, $query))
    {

        if($limit>0)
        {
            mysqli_stmt_bind_param($stmt, 'iii', $manager, $offset, $limit);
        }
        else
        {
            mysqli_stmt_bind_param($stmt, 'i', $manager);
        }

        if(mysqli_stmt_execute($stmt))
        {
            if($response = mysqli_stmt_get_result($stmt))
            {
                if(mysqli_num_rows($response)>0)
                {
                    while ($row = mysqli_fetch_assoc($response))
                    {
                        $users[] = $row;
                    }
                }
            }
        }
    }

    return $users;
}

function getUsersByRole($role, $offset=0, $limit=0, $valid = 1)
{
    global $connection;

    $query = "SELECT * FROM " . BTRS_DB_PREFIX.BTRS_TB_USERS . " WHERE `role` = ? AND `validate` = ? ORDER BY `id` DESC ".( $limit>0 ? "LIMIT ?, ?" : "" );

    $users = array();

    if($stmt = mysqli_prepare($connection, $query))
    {

        if($limit>0)
        {
            mysqli_stmt_bind_param($stmt, 'iiii', $role, $valid, $offset, $limit);
        }
        else
        {
            mysqli_stmt_bind_param($stmt, 'ii', $role, $valid);
        }

        if(mysqli_stmt_execute($stmt))
        {
            if($response = mysqli_stmt_get_result($stmt))
            {
                if(mysqli_num_rows($response)>0)
                {
                    while ($row = mysqli_fetch_assoc($response))
                    {
                        $users[] = $row;
                    }
                }
            }
        }
    }

    return $users;
}

function validateUser($userid)
{
    global $connection;

    $query = "UPDATE ".BTRS_DB_PREFIX.BTRS_TB_USERS." SET `validate` = 1 WHERE `id` = ?";

    if($stmt = mysqli_prepare($connection, $query))
    {
        mysqli_stmt_bind_param($stmt, 'i', $userid);
        if(mysqli_stmt_execute($stmt))
        {
            return mysqli_affected_rows($connection);
        }
    }

    return false;
}

function totalBusCounters($manager=null)
{
    global $connection;

    $query = $manager!=null ? "SELECT COUNT(*) FROM ".BTRS_DB_PREFIX.BTRS_TB_BUSCOUNTERS." WHERE `manager` = ?" : "SELECT COUNT(*) FROM ".BTRS_DB_PREFIX.BTRS_TB_BUSCOUNTERS;

    if($stmt = mysqli_prepare($connection, $query))
    {
        if($manager!=null)
        {
            mysqli_stmt_bind_param($stmt, 'i', $manager);
        }

        if(mysqli_stmt_execute($stmt))
        {
            if($response = mysqli_stmt_get_result($stmt))
            {
                if($row = mysqli_fetch_row($response))
                {
                    return (isset($row[0]) && $row[0]>0) ? $row[0] : 0;
                }
            }
        }

    }

    return 0;
}

function getBusCounters($offset=0, $limit=0, $manager=null)
{
    global $connection;

    $query = "SELECT * FROM ".BTRS_DB_PREFIX.BTRS_TB_BUSCOUNTERS.($manager!=null ? " WHERE `manager` = ? ":"")." ORDER BY `id` DESC ".( $limit>0 ? " LIMIT ?, ?" : "" );
    $users = array();

    if($stmt = mysqli_prepare($connection, $query))
    {

        if($limit>0)
        {
            if($manager!=null)
            {
                mysqli_stmt_bind_param($stmt, 'iii', $manager, $offset, $limit);
            }
            else
            {
                mysqli_stmt_bind_param($stmt, 'ii', $offset, $limit);
            }

        }
        else
        {
            if($manager!=null)
            {
                mysqli_stmt_bind_param($stmt, 'i', $manager);
            }

        }

        if(mysqli_stmt_execute($stmt))
        {
            if($response = mysqli_stmt_get_result($stmt))
            {
                if(mysqli_num_rows($response)>0)
                {
                    while ($row = mysqli_fetch_assoc($response))
                    {
                        $users[] = $row;
                    }
                }
            }
        }
    }

    return $users;
}

function totalBuses($manager=null)
{
    global $connection;

    $query = $manager!=null ? "SELECT COUNT(*) FROM ".BTRS_DB_PREFIX.BTRS_TB_BUSES." WHERE `manager` = ?" : "SELECT COUNT(*) FROM ".BTRS_DB_PREFIX.BTRS_TB_BUSES;

    if($stmt = mysqli_prepare($connection, $query))
    {
        if($manager!=null)
        {
            mysqli_stmt_bind_param($stmt, 'i', $manager);
        }

        if(mysqli_stmt_execute($stmt))
        {
            if($response = mysqli_stmt_get_result($stmt))
            {
                if($row = mysqli_fetch_row($response))
                {
                    return (isset($row[0]) && $row[0]>0) ? $row[0] : 0;
                }
            }
        }

    }

    return 0;
}

function getBuses($offset=0, $limit=0, $manager=null)
{
    global $connection;

    $query = "SELECT * FROM ".BTRS_DB_PREFIX.BTRS_TB_BUSES.($manager!=null ? " WHERE `manager` = ? ":"")." ORDER BY `id` DESC ".( $limit>0 ? " LIMIT ?, ?" : "" );
    $users = array();

    if($stmt = mysqli_prepare($connection, $query))
    {

        if($limit>0)
        {
            if($manager!=null)
            {
                mysqli_stmt_bind_param($stmt, 'iii', $manager, $offset, $limit);
            }
            else
            {
                mysqli_stmt_bind_param($stmt, 'ii', $offset, $limit);
            }

        }
        else
        {
            if($manager!=null)
            {
                mysqli_stmt_bind_param($stmt, 'i', $manager);
            }

        }

        if(mysqli_stmt_execute($stmt))
        {
            if($response = mysqli_stmt_get_result($stmt))
            {
                if(mysqli_num_rows($response)>0)
                {
                    while ($row = mysqli_fetch_assoc($response))
                    {
                        $users[] = $row;
                    }
                }
            }
        }
    }

    return $users;
}

function totalBusSchedules($manager=null)
{
    global $connection;

    $query = $manager!=null ? "SELECT COUNT(*) FROM ".BTRS_DB_PREFIX.BTRS_TB_SCHEDULE." a INNER JOIN ".BTRS_DB_PREFIX.BTRS_TB_BUSES." b ON a.busid = b.id WHERE b.manager = ?" : "SELECT COUNT(*) FROM ".BTRS_DB_PREFIX.BTRS_TB_SCHEDULE;

    if($stmt = mysqli_prepare($connection, $query))
    {
        if($manager!=null)
        {
            mysqli_stmt_bind_param($stmt, 'i', $manager);
        }

        if(mysqli_stmt_execute($stmt))
        {
            if($response = mysqli_stmt_get_result($stmt))
            {
                if($row = mysqli_fetch_row($response))
                {
                    return (isset($row[0]) && $row[0]>0) ? $row[0] : 0;
                }
            }
        }

    }

    return 0;
}

function getBusSchedules($offset=0, $limit=0, $manager=null)
{
    global $connection;

    $query = "SELECT * FROM ".BTRS_DB_PREFIX.BTRS_TB_SCHEDULE." a INNER JOIN ".BTRS_DB_PREFIX.BTRS_TB_BUSES." b ON a.busid = b.id".($manager!=null ? " WHERE b.manager = ? ":"")." ORDER BY a.id DESC ".( $limit>0 ? " LIMIT ?, ?" : "" );
    $users = array();
    if($stmt = mysqli_prepare($connection, $query))
    {

        if($limit>0)
        {
            if($manager!=null)
            {
                mysqli_stmt_bind_param($stmt, 'iii', $manager, $offset, $limit);
            }
            else
            {
                mysqli_stmt_bind_param($stmt, 'ii', $offset, $limit);
            }

        }
        else
        {
            if($manager!=null)
            {
                mysqli_stmt_bind_param($stmt, 'i', $manager);
            }

        }

        if(mysqli_stmt_execute($stmt))
        {
            if($response = mysqli_stmt_get_result($stmt))
            {
                if(mysqli_num_rows($response)>0)
                {
                    while ($row = mysqli_fetch_assoc($response))
                    {
                        $users[] = $row;
                    }
                }
            }
        }
    }

    return $users;
}

function addBusCounter(array $data)
{
    global $connection;

    $query = "INSERT INTO ".BTRS_DB_PREFIX.BTRS_TB_BUSCOUNTERS."( `manager`, `name`, `location`, `type`, `description` ) VALUES ( ?, ?, ?, ?, ? )";
    if($stmt = mysqli_prepare($connection, $query))
    {
        mysqli_stmt_bind_param(
            $stmt,
            'issss',
            $data['manager'],
            $data['name'],
            $data['location'],
            $data['type'],
            $data['description']
        );

        if(mysqli_stmt_execute($stmt))
        {
            if(mysqli_affected_rows($connection))
            {
                return mysqli_insert_id($connection);
            }
        }

    }

    return false;
}

function getCounterById($counter)
{
    global $connection;

    $query = "SELECT * FROM ".BTRS_DB_PREFIX.BTRS_TB_BUSCOUNTERS." WHERE `id` = ? ORDER BY `id` DESC";

    if($stmt = mysqli_prepare($connection, $query))
    {

        mysqli_stmt_bind_param($stmt, 'i', $counter);

        mysqli_stmt_execute($stmt);
        if($response = mysqli_stmt_get_result($stmt))
        {
            if($row = mysqli_fetch_assoc($response))
            {
                if(is_array($row) && !empty($row))
                {
                    return $row;
                }
            }
        }
    }

    return null;
}


function addBus(array $data)
{
    global $connection;

    $query = "INSERT INTO ".BTRS_DB_PREFIX.BTRS_TB_BUSES."( `manager`, `name`, `registration`, `type`, `seats_row`, `seats_column`, `fill_last_row`, `description` ) VALUES ( ?, ?, ?, ?, ?, ?, ?, ? )";

    if($stmt = mysqli_prepare($connection, $query))
    {
        mysqli_stmt_bind_param(
            $stmt,
            'isssiiss',
            $data['manager'],
            $data['name'],
            $data['registration'],
            $data['type'],
            $data['seats_row'],
            $data['seats_column'],
            $data['fill_last_row'],
            $data['description']
        );

        if(mysqli_stmt_execute($stmt))
        {
            if(mysqli_affected_rows($connection))
            {
                return mysqli_insert_id($connection);
            }
        }

    }

    return false;
}

function getBusById($bus)
{
    global $connection;

    $query = "SELECT * FROM ".BTRS_DB_PREFIX.BTRS_TB_BUSES." WHERE `id` = ? ORDER BY `id` DESC";

    if($stmt = mysqli_prepare($connection, $query))
    {

        mysqli_stmt_bind_param($stmt, 'i', $bus);

        mysqli_stmt_execute($stmt);
        if($response = mysqli_stmt_get_result($stmt))
        {
            if($row = mysqli_fetch_assoc($response))
            {
                if(is_array($row) && !empty($row))
                {
                    return $row;
                }
            }
        }
    }

    return null;
}

function getBusByReg($reg)
{
    global $connection;

    $query = "SELECT * FROM ".BTRS_DB_PREFIX.BTRS_TB_BUSES." WHERE `registration` = ? ORDER BY `id` DESC";

    if($stmt = mysqli_prepare($connection, $query))
    {

        mysqli_stmt_bind_param($stmt, 's', $reg);

        mysqli_stmt_execute($stmt);
        if($response = mysqli_stmt_get_result($stmt))
        {
            if($row = mysqli_fetch_assoc($response))
            {
                if(is_array($row) && !empty($row))
                {
                    return $row;
                }
            }
        }
    }

    return null;
}

function getBusRoutes()
{
    global $connection;

    $query = "SELECT DISTINCT `route` FROM ".BTRS_DB_PREFIX.BTRS_TB_SCHEDULE;

    $routes = array();

    if($stmt = mysqli_prepare($connection, $query))
    {
        mysqli_stmt_execute($stmt);
        if($response = mysqli_stmt_get_result($stmt))
        {
            while($row = mysqli_fetch_assoc($response))
            {
                if(is_array($row) && !empty($row))
                {
                    $routes[] = $row['route'];
                }
            }
        }
    }

    return $routes;
}

function isConflictSchedule($bus, $departure, $route)
{
    global $connection;

    $query = "SELECT COUNT(*) FROM " . BTRS_DB_PREFIX.BTRS_TB_SCHEDULE . " WHERE `busid` = ? AND `route` = ? (`departure` = ? OR ( `departure` < ? AND `arrival` >= ? ))";

    if($stmt = mysqli_prepare($connection, $query))
    {
        mysqli_stmt_bind_param($stmt, 'issss', $bus, $route, $departure, $departure, $departure);

        if(mysqli_stmt_execute($stmt))
        {
            if($response = mysqli_stmt_get_result($stmt))
            {
                if($row = mysqli_fetch_row($response))
                {
                    return isset($row[0]) && $row[0]>0;
                }
            }
        }

    }

    return false;
}

function addSchedule(array $data)
{
    global $connection;

    $query = "INSERT INTO ".BTRS_DB_PREFIX.BTRS_TB_SCHEDULE."( `busid`, `departure`, `arrival`, `route`, `fare`, `boarding`, `created`, `description` ) VALUES ( ?, ?, ?, ?, ?, ?, ?, ? )";

    if($stmt = mysqli_prepare($connection, $query))
    {
        mysqli_stmt_bind_param(
            $stmt,
            'isssdiss',
            $data['busid'],
            $data['departure'],
            $data['arrival'],
            $data['route'],
            $data['fare'],
            $data['boarding'],
            $data['created'],
            $data['description']
        );

        if(mysqli_stmt_execute($stmt))
        {
            if(mysqli_affected_rows($connection))
            {
                return mysqli_insert_id($connection);
            }
        }

    }

    return false;
}

function getScheduleById($schedule)
{
    global $connection;

    $query = "SELECT * FROM ".BTRS_DB_PREFIX.BTRS_TB_SCHEDULE." WHERE `id` = ? ORDER BY `id` DESC";

    if($stmt = mysqli_prepare($connection, $query))
    {

        mysqli_stmt_bind_param($stmt, 'i', $schedule);

        mysqli_stmt_execute($stmt);
        if($response = mysqli_stmt_get_result($stmt))
        {
            if($row = mysqli_fetch_assoc($response))
            {
                if(is_array($row) && !empty($row))
                {
                    return $row;
                }
            }
        }
    }

    return null;
}

function getSchedule($from, $to, $departure, $manager=null)
{
    global $connection;

    $route = $from.' - '.$to;
    $departureFrom = $departure . " 00:00:00";
    $departureTo = $departure . " 23:59:59";
    $schedules = array();

    $query = "SELECT * FROM ".BTRS_DB_PREFIX.BTRS_TB_SCHEDULE." WHERE `route` = ?".($manager!=null?' AND `busid` IN (SELECT id FROM '.BTRS_DB_PREFIX.BTRS_TB_BUSES.' WHERE `manager` = ? )':'')." AND `departure` BETWEEN ? AND ? ORDER BY `departure` ASC";

    if($stmt = mysqli_prepare($connection, $query))
    {
        if($manager!=null)
        {
            mysqli_stmt_bind_param($stmt, 'siss', $route, $manager, $departureFrom, $departureTo);
        }
        else
        {
            mysqli_stmt_bind_param($stmt, 'sss', $route, $departureFrom, $departureTo);
        }

        mysqli_stmt_execute($stmt);
        if($response = mysqli_stmt_get_result($stmt))
        {

            while($row = mysqli_fetch_assoc($response))
            {
                if(is_array($row) && !empty($row))
                {
                    $schedules[] = $row;
                }
            }
        }
    }

    return $schedules;
}

function getBookedSeatsBySchedule($schedule)
{
    global $connection;

    $seats = array();

    $query = "SELECT a.* FROM ".BTRS_DB_PREFIX.BTRS_TB_BOOKEDSEATS." a INNER JOIN " .BTRS_DB_PREFIX.BTRS_TB_BOOKINGS." b ON a.booking = b.id ".
             "INNER JOIN ".BTRS_DB_PREFIX.BTRS_TB_SCHEDULE." c ON b.schedule = c.id ".
             "WHERE c.id = ? ORDER BY a.seat ASC";

    if($stmt = mysqli_prepare($connection, $query))
    {

        mysqli_stmt_bind_param($stmt, 'i', $schedule);


        mysqli_stmt_execute($stmt);
        if($response = mysqli_stmt_get_result($stmt))
        {

            while($row = mysqli_fetch_assoc($response))
            {
                if(is_array($row) && !empty($row))
                {
                    $seats[] = $row['seat'];
                }
            }
        }
    }

    echo mysqli_error($connection);

    return $seats;
}


function getValidCupon($code)
{
    global $connection;

    $date = date('Y-m-d H:i:s');

    $query = "SELECT * FROM ".BTRS_DB_PREFIX.BTRS_TB_DISCOUNT." WHERE `code` = ? AND `valid_from` < ? AND `valid_to` > ?";

    if($stmt = mysqli_prepare($connection, $query))
    {
        mysqli_stmt_bind_param($stmt, 'sss', $code, $date, $date);

        mysqli_stmt_execute($stmt);

        if($response = mysqli_stmt_get_result($stmt))
        {
            if($row = mysqli_fetch_assoc($response))
            {
                if(is_array($row) && !empty($row))
                {
                    return $row;
                }
            }
        }
    }

    return null;
}

function addDiscount(array $data)
{
    global $connection;

    $query = "INSERT INTO ".BTRS_DB_PREFIX.BTRS_TB_DISCOUNT."( `code`, `discount`, `max`, `valid_from`, `valid_to`, `created` ) VALUES ( ?, ?, ?, ?, ?, ? )";

    if($stmt = mysqli_prepare($connection, $query))
    {
        mysqli_stmt_bind_param(
            $stmt,
            'ssdsss',
            $data['code'],
            $data['discount'],
            $data['max'],
            $data['valid_from'],
            $data['valid_to'],
            $data['created']
        );

        if(mysqli_stmt_execute($stmt))
        {
            if(mysqli_affected_rows($connection))
            {
                return mysqli_insert_id($connection);
            }
        }

    }

    return false;
}

function totalDiscount()
{
    global $connection;

    $query = "SELECT COUNT(*) FROM ".BTRS_DB_PREFIX.BTRS_TB_DISCOUNT;

    if($stmt = mysqli_prepare($connection, $query))
    {
        if(mysqli_stmt_execute($stmt))
        {
            if($response = mysqli_stmt_get_result($stmt))
            {
                if($row = mysqli_fetch_row($response))
                {
                    return (isset($row[0]) && $row[0]>0) ? $row[0] : 0;
                }
            }
        }

    }

    return 0;
}

function getDiscount($offset=0, $limit=0)
{
    global $connection;

    $query = "SELECT * FROM ".BTRS_DB_PREFIX.BTRS_TB_DISCOUNT." ORDER BY `id` DESC ".( $limit>0 ? " LIMIT ?, ?" : "" );
    $users = array();

    if($stmt = mysqli_prepare($connection, $query))
    {

        if($limit>0)
        {
            mysqli_stmt_bind_param($stmt, 'ii', $offset, $limit);
        }

        if(mysqli_stmt_execute($stmt))
        {
            if($response = mysqli_stmt_get_result($stmt))
            {
                if(mysqli_num_rows($response)>0)
                {
                    while ($row = mysqli_fetch_assoc($response))
                    {
                        $users[] = $row;
                    }
                }
            }
        }
    }

    return $users;
}

function addPaymentMethod(array $data)
{

    global $connection;

    $query = "INSERT INTO ".BTRS_DB_PREFIX.BTRS_TB_PAYMETHOD."( `method`, `description`, `created` ) VALUES ( ?, ?, ? )";

    if($stmt = mysqli_prepare($connection, $query))
    {
        mysqli_stmt_bind_param(
            $stmt,
            'sss',
            $data['method'],
            $data['description'],
            $data['created']
        );

        if(mysqli_stmt_execute($stmt))
        {
            if(mysqli_affected_rows($connection))
            {
                return mysqli_insert_id($connection);
            }
        }

    }

    return false;
}

function totalPayMethod()
{
    global $connection;

    $query = "SELECT COUNT(*) FROM ".BTRS_DB_PREFIX.BTRS_TB_PAYMETHOD;

    if($stmt = mysqli_prepare($connection, $query))
    {
        if(mysqli_stmt_execute($stmt))
        {
            if($response = mysqli_stmt_get_result($stmt))
            {
                if($row = mysqli_fetch_row($response))
                {
                    return (isset($row[0]) && $row[0]>0) ? $row[0] : 0;
                }
            }
        }

    }

    return 0;
}

function getPayMethod($offset=0, $limit=0)
{
    global $connection;

    $query = "SELECT * FROM ".BTRS_DB_PREFIX.BTRS_TB_PAYMETHOD." ORDER BY `id` DESC ".( $limit>0 ? " LIMIT ?, ?" : "" );
    $users = array();

    if($stmt = mysqli_prepare($connection, $query))
    {

        if($limit>0)
        {
            mysqli_stmt_bind_param($stmt, 'ii', $offset, $limit);
        }

        if(mysqli_stmt_execute($stmt))
        {
            if($response = mysqli_stmt_get_result($stmt))
            {
                if(mysqli_num_rows($response)>0)
                {
                    while ($row = mysqli_fetch_assoc($response))
                    {
                        $users[] = $row;
                    }
                }
            }
        }
    }

    return $users;
}

function getPayMethodById($method)
{
    global $connection;

    $query = "SELECT * FROM ".BTRS_DB_PREFIX.BTRS_TB_PAYMETHOD." WHERE `id` = ? ORDER BY `id` DESC";

    if($stmt = mysqli_prepare($connection, $query))
    {

        mysqli_stmt_bind_param($stmt, 'i', $method);

        mysqli_stmt_execute($stmt);
        if($response = mysqli_stmt_get_result($stmt))
        {
            if($row = mysqli_fetch_assoc($response))
            {
                if(is_array($row) && !empty($row))
                {
                    return $row;
                }
            }
        }
    }

    return null;
}

function addBooking(array $data)
{
    global $connection;

    $query = "INSERT INTO ".BTRS_DB_PREFIX.BTRS_TB_BOOKINGS."( `schedule`, `total_fare`, `status`, `temp`, `booked` ) VALUES ( ?, ?, ?, ?, ? )";

    if($stmt = mysqli_prepare($connection, $query))
    {
        mysqli_stmt_bind_param(
            $stmt,
            'idsss',
            $data['schedule'],
            $data['total_fare'],
            $data['status'],
            $data['temp'],
            $data['booked']
        );

        if(mysqli_stmt_execute($stmt))
        {
            if(mysqli_affected_rows($connection))
            {
                return mysqli_insert_id($connection);
            }
        }

    }

    return false;
}

function addSeatBooking($booking, $seat)
{
    global $connection;

    $query = "INSERT INTO ".BTRS_DB_PREFIX.BTRS_TB_BOOKEDSEATS."( `booking`, `seat` ) VALUES ( ?, ? )";

    if($stmt = mysqli_prepare($connection, $query))
    {
        mysqli_stmt_bind_param(
            $stmt,
            'is',
            $booking,
            $seat
        );

        if(mysqli_stmt_execute($stmt))
        {
            if(mysqli_affected_rows($connection))
            {
                return mysqli_insert_id($connection);
            }
        }

    }

    return false;
}

function getBookingById($id)
{
    global $connection;

    $query = "SELECT * FROM ".BTRS_DB_PREFIX.BTRS_TB_BOOKINGS." WHERE `id` = ? ORDER BY `id` DESC";

    if($stmt = mysqli_prepare($connection, $query))
    {

        mysqli_stmt_bind_param($stmt, 'i', $id);

        mysqli_stmt_execute($stmt);
        if($response = mysqli_stmt_get_result($stmt))
        {
            if($row = mysqli_fetch_assoc($response))
            {
                if(is_array($row) && !empty($row))
                {
                    return $row;
                }
            }
        }
    }

    return null;
}

function getBookedSeatsByBooking($booking)
{
    global $connection;

    $query = "SELECT * FROM ".BTRS_DB_PREFIX.BTRS_TB_BOOKEDSEATS." WHERE `booking` = ?";

    $seats = array();

    if($stmt = mysqli_prepare($connection, $query))
    {
        mysqli_stmt_bind_param($stmt, 'i', $booking);
        mysqli_stmt_execute($stmt);
        if($response = mysqli_stmt_get_result($stmt))
        {
            while($row = mysqli_fetch_assoc($response))
            {
                if(is_array($row) && !empty($row))
                {
                    $seats[] = $row['seat'];
                }
            }
        }
    }

    return $seats;
}

function getTransactionByTrxidMethod($trxid, $method)
{
    global $connection;

    $query = "SELECT * FROM ".BTRS_DB_PREFIX.BTRS_TB_TRANSACTIONS." WHERE `trxid` = ? AND `method` = ? ORDER BY `id` DESC";

    if($stmt = mysqli_prepare($connection, $query))
    {

        mysqli_stmt_bind_param($stmt, 'si', $trxid, $method);

        mysqli_stmt_execute($stmt);
        if($response = mysqli_stmt_get_result($stmt))
        {
            if($row = mysqli_fetch_assoc($response))
            {
                if(is_array($row) && !empty($row))
                {
                    return $row;
                }
            }
        }
    }

    return null;
}

function addTransaction(array $data)
{
    global $connection;

    $query = "INSERT INTO ".BTRS_DB_PREFIX.BTRS_TB_TRANSACTIONS."( `trxid`, `method`, `amount`, `promo`, `created` ) VALUES ( ?, ?, ?, ?, ? )";

    if($stmt = mysqli_prepare($connection, $query))
    {
        mysqli_stmt_bind_param(
            $stmt,
            'sidis',
            $data['trxid'],
            $data['method'],
            $data['amount'],
            $data['promo'],
            $data['created']
        );

        if(mysqli_stmt_execute($stmt))
        {
            if(mysqli_affected_rows($connection))
            {
                return mysqli_insert_id($connection);
            }
        }

    }


    return false;
}

function completeBooking($bookingid, array $data)
{
    global $connection;

    $time = date('Y-m-d H:i:s');
    $status = 'booked';
    $query = "UPDATE ".BTRS_DB_PREFIX.BTRS_TB_BOOKINGS." SET `name` = ?, `email` = ?, `mobile` = ?, `status` = ?, `booked` = ? WHERE `id` = ?";
    if($stmt = mysqli_prepare($connection, $query))
    {
        mysqli_stmt_bind_param($stmt, 'sssssi', $data['name'], $data['email'], $data['mobile'], $status, $time, $bookingid);
        mysqli_stmt_execute($stmt);

        return mysqli_affected_rows($connection);
    }
    return false;
}

function removeExpiredSeats()
{
    global $connection;

    $time = date('Y-m-d H:i:s');

    $query = "DELETE FROM ".BTRS_DB_PREFIX.BTRS_TB_BOOKEDSEATS." WHERE `booking` IN ".
             "( SELECT `id` FROM ".BTRS_DB_PREFIX.BTRS_TB_BOOKINGS." WHERE `status` = 'temporary' AND `temp` < ? )";
    if($stmt = mysqli_prepare($connection, $query))
    {
        mysqli_stmt_bind_param($stmt, 's', $time);
        mysqli_stmt_execute($stmt);
        return mysqli_affected_rows($connection);
    } 
}

function removeExpiredBooking()
{
    removeExpiredSeats();
    
    global $connection;

    $time = date('Y-m-d H:i:s');

    $query = "DELETE FROM ".BTRS_DB_PREFIX.BTRS_TB_BOOKINGS." WHERE `status` = 'temporary' AND `temp` < ?";
    if($stmt = mysqli_prepare($connection, $query))
    {
        mysqli_stmt_bind_param($stmt, 's', $token);
        mysqli_stmt_execute($stmt);
        return mysqli_affected_rows($connection);
    }
}

function totalBookedTickets($manager=null)
{
    global $connection;

    $query = $manager!=null ? "SELECT COUNT(*) FROM ".BTRS_DB_PREFIX.BTRS_TB_BOOKINGS : "";

    if($stmt = mysqli_prepare($connection, $query))
    {
        if($manager!=null)
        {
            mysqli_stmt_bind_param($stmt, 'i', $manager);
        }

        if(mysqli_stmt_execute($stmt))
        {
            if($response = mysqli_stmt_get_result($stmt))
            {
                if($row = mysqli_fetch_row($response))
                {
                    return (isset($row[0]) && $row[0]>0) ? $row[0] : 0;
                }
            }
        }

    }

    return 0;
}

function getBookedTickes($offset=0, $limit=0, $manager=null)
{
    global $connection;

    $query = "SELECT * FROM ".BTRS_DB_PREFIX.BTRS_TB_SCHEDULE." a INNER JOIN ".BTRS_DB_PREFIX.BTRS_TB_BUSES." b ON a.busid = b.id".($manager!=null ? " WHERE b.manager = ? ":"")." ORDER BY a.id DESC ".( $limit>0 ? " LIMIT ?, ?" : "" );
    $users = array();
    if($stmt = mysqli_prepare($connection, $query))
    {

        if($limit>0)
        {
            if($manager!=null)
            {
                mysqli_stmt_bind_param($stmt, 'iii', $manager, $offset, $limit);
            }
            else
            {
                mysqli_stmt_bind_param($stmt, 'ii', $offset, $limit);
            }

        }
        else
        {
            if($manager!=null)
            {
                mysqli_stmt_bind_param($stmt, 'i', $manager);
            }

        }

        if(mysqli_stmt_execute($stmt))
        {
            if($response = mysqli_stmt_get_result($stmt))
            {
                if(mysqli_num_rows($response)>0)
                {
                    while ($row = mysqli_fetch_assoc($response))
                    {
                        $users[] = $row;
                    }
                }
            }
        }
    }

    return $users;
}