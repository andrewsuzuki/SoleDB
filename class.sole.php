<?php

/**
 * SoleDB Single Class
 *
 * Handles flat database reading and writing.
 *
 * @package SoleDB
 * @version 1.0
 * @author  Andrew Suzuki <andrew.b.suzuki@gmail.com>
 * @link    https://github.com/andrewsuzuki/SoleDB
 */

class Sole {

    private static $options = array();

    private static $databases = array();

    public function __construct($options = array())
    {
        $default = array('dir'=>dirname(__FILE__).'/data/');

        foreach ($options as $option => $value)
        {
            if (array_key_exists($option, $default))
            {
                $default[$option] = $value;
            }
            elseif (defined('SOLE_'.strtoupper($option)))
            {
                $default[$option] = constant('SOLE_'.strtoupper($option));
            }
        }

        Sole::$options = $default;
    }

    public static function load($db)
    {
        if (!($file = @file_get_contents(Sole::$options['dir'].$db.'.json')))
        {
            Sole::error('DB_DNE');
        }

        if (!($database = @json_decode($file, true)) || !isset($database['head']) || !is_array($database['head']) || !isset($database['body']) || !is_array($database['body']))
        {
            Sole::error('DB_COR');
        }

        Sole::$databases[$db] = $database;
    }

    private static function save($db)
    {
        Sole::in($db);

        @file_put_contents(Sole::$options['dir'].$db.'.json', @json_encode(Sole::$databases[$db]));
    }

    public static function in($db, $bool = false)
    {
        if (isset(Sole::$databases[$db]))
        {
            return true;
        }
        elseif ($bool == true)
        {
            return false;
        }
        else
        {
            Sole::error('DB_DNE');
        }
    }

    public static function get($db, $select = null, $where = null, $order = null)
    {
        Sole::in($db);

        $data = Sole::$databases[$db];
        $head = $data['head'];
        $data = Sole::toAssoc($data);

        return Sole::filter($data, $select, $where, $order);
    }

    public static function filter($data, $select = null, $where = null, $order = null)
    {
        if (is_array($order)) // array(FIELD,ASC/DSC,CS/CI)
        {
            if (isset($order[0]) && isset($data[0][$order[0]])) // if order field doesn't exist, don't order
            {
                if (!isset($order[1]) || $order[1] != 'DSC')
                {
                    $order[1] = 'ASC';
                }

                if (!isset($order[2]) || $order[2] != 'CI')
                {
                    $order[2] = 'CS';
                }

                usort($data, create_function('$a, $b', 'return Sole::dataOrder($a, $b, "'.$order[0].'", "'.$order[1].'", "'.$order[2].'");'));
            }
        }

        // Currently WHERE can only process (any number AND) with = check
       
        if (is_array($where))
        {
            foreach($where as $field => $cond)
            {
                if (!is_string($field))
                {
                    unset($where[$field]);
                }
            }

            if (!empty($where))
            {
                foreach($where as $field => $cond)
                {
                    foreach ($data as $n => $row)
                    {
                        if ($row[$field] !== $cond)
                        {
                            unset($data[$n]);
                        }
                    }
                }
            }
        }

        if (is_string($select))
        {
            $select = array($select);
        }

        if (is_array($select))
        {
            $new_data = array();

            foreach($data as $row)
            {
                $new_row = array();

                foreach($select as $field)
                {
                    if (array_key_exists($field, $row))
                    {
                        $new_row[$field] = $row[$field];
                    }
                }

                $new_data[] = $new_row;
            } 

            $data = $new_data;
        }

        return $data;
    }

    public static function newDB($db)
    {
        if (!is_string($db) || trim($db) == '' || !ctype_alnum(str_replace(array('_', '-'), '', $db)) || isset(Sole::$databases[$db]) || is_file(Sole::$options['dir'].$db.'.json'))
        {
            Sole::error('NAME_INVALID');
        }

        Sole::$databases[$db] = array('head'=>array(), 'body'=>array());

        Sole::save($db);
    }

    public static function deleteDB($db)
    {
        if (!is_file(Sole::$options['dir'].$db.'.json'))
        {
            Sole::error('DB_DNE');
        }

        if (isset(Sole::$databases[$db]))
        {
            unset(Sole::$databases[$db]);
        }

        unlink(Sole::$options['dir'].$db.'.json');
    }

    public static function insertField($db, $field, $position = null, $default = '')
    {
        Sole::in($db);

        $database = Sole::$databases[$db];

        if (!is_string($field) || in_array($field, $database['head']) || trim($field) == '' || !ctype_alnum(str_replace(array('_', '-'), '', $field)))
        {
            Sole::error('NAME_INVALID');
        }

        if (strtolower($position) == '[beginning]')
        {
            $target_pos = 0;
        }
        elseif (strtolower($position) == '[end]' || !is_string($position) || !in_array($position, $database['head'])) // if position is at end, position isn't given, or "after" field doesn't exist
        {
            $target_pos = count($database['body']);
        }
        else // if "after" field is given and exists
        {
            $target_pos = array_search($position, $database['head']) + 1;
        }

        array_splice($database['head'], $target_pos, 0, $field); // insert new field in head

        foreach($database['body'] as $n => $row)
        {
            array_splice($database['body'][$n], $target_pos, 0, $default); // insert new cell in row
        }

        Sole::$databases[$db] = $database;

        Sole::save($db);
    }

    public static function deleteField($db, $field)
    {
        Sole::in($db);

        $database = Sole::$databases[$db];

        if (!in_array($field, $database['head']))
        {
            Sole::error('FIELD_DNE');
        }

        $target_pos = array_search($field, $database['head']);

        array_splice($database['head'], $target_pos, 1); // delete field in head

        foreach($database['body'] as $n => $row)
        {
            array_splice($database['body'][$n], $target_pos, 1); // delete cell in row
        }

        Sole::$databases[$db] = $database;

        Sole::save($db);
    }

    public static function insert($db, $data = array())
    {
        Sole::in($db);

        $database = Sole::$databases[$db];

        $row = array();

        foreach($database['head'] as $field)
        {
            if (array_key_exists($field, $data))
            {
                $row[] = $data[$field];
            }
            else
            {
                $row[] = '';
            }
        }

        $database['body'][] = $row;

        Sole::$databases[$db] = $database;

        Sole::save($db);
    }

    public static function update($db, $data = array(), $where = array())
    {
        Sole::in($db);

        if (!is_array($data))
        {
            return;
        }

        $database = Sole::$databases[$db];

        $where_target_fields = array();

        $n = 0;

        foreach($database['head'] as $field)
        {
            if (array_key_exists($field, $where))
            {
                $where_target_fields[] = array($n, $where[$field]);
            }

            $n++;
        }

        // if any where field does not exist in database, end method exec (bc no fields will be targeted for update)

        if (count($where_target_fields) != count($where))
        {
            return;
        }

        $data_target_fields = array();

        $n = 0;

        foreach($database['head'] as $field)
        {
            if (array_key_exists($field, $data))
            {
                $data_target_fields[] = array($n, $data[$field]);
            }

            $n++;
        }

        foreach($database['body'] as $n => $row)
        {
            $is_target = true;

            foreach($where_target_fields as $target)
            {
                if ($row[$target[0]] !== $target[1])
                {
                    $is_target = false;
                    break;
                }
            }

            if ($is_target)
            {
                foreach($data_target_fields as $target)
                {
                    $database['body'][$n][$target[0]] = $target[1];
                }
            }
        }

        Sole::$databases[$db] = $database;

        Sole::save($db);
    }

    public static function delete($db, $where = array())
    {
        Sole::in($db);

        if (!is_array($where))
        {
            return; // Error here?
        }

        $database = Sole::$databases[$db];

        $target_fields = array();

        $n = 0;

        foreach($database['head'] as $field)
        {
            if (array_key_exists($field, $where))
            {
                $target_fields[] = array($n, $where[$field]);
            }

            $n++;
        }

        foreach($database['body'] as $n => $row)
        {
            $is_target = true;

            foreach($target_fields as $target)
            {
                if ($row[$target[0]] !== $target[1])
                {
                    $is_target = false;
                    break;
                }
            }

            if ($is_target)
            {
                unset($database['body'][$n]);
            }
        }

        Sole::$databases[$db] = $database;

        Sole::save($db);
    }

    private static function toAssoc($data)
    {
        $assoc = array();

        foreach ($data['body'] as $row)
        {
            $t = array();

            foreach ($data['head'] as $n => $field)
            {
                $t[$field] = $row[$n];
            }

            $assoc[] = $t;
        }

        return $assoc;
    }

    public static function dataOrder($a, $b, $field, $direction, $case)
    {
        if ($direction == 'ASC')
        {
            if ($case == 'CS')
            {
                return strcmp($a[$field], $b[$field]);
            }
            else
            {
                return strcasecmp($a[$field], $b[$field]);
            }
        }
        else
        {
            if ($case == 'CS')
            {
                return strcmp($b[$field], $a[$field]);
            }
            else
            {
                return strcasecmp($b[$field], $a[$field]);
            }
        }
    }

    private static function error($error)
    {
        switch($error)
        {
            case 'DB_DNE':
                $error = 'Database does not exist';
                break;

            case 'DB_COR':
                $error = 'Database is corrupted';
                break;

            case 'FIELD_DNE':
                $error = 'Field does not exist';
                break;

            case 'NAME_INVALID':
                $error = 'New field/database name must be unique, be non-empty, and contain only alphanumeric characters, underscores, and hyphens';
                break;

            default:
                $error = 'An unknown error occurred';
        }

        $debug = debug_backtrace();

        if (count($debug) > 1 && ($debug_end = end($debug)) && isset($debug_end['line']))
        {
            $error = $error.' (line '.$debug_end['line'].').';
        }
        else
        {
            $error = $error.'.';
        }

        trigger_error($error, E_USER_ERROR);
    }

}

?>