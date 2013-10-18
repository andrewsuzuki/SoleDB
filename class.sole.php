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
        $name = $db;

        if (!($file = @file_get_contents(Sole::$options['dir'].$name.'.json')))
        {
            Sole::error('DB_DNE');
        }

        if (!($db = @json_decode($file, true)) || !isset($db['head']) || !is_array($db['head']) || !isset($db['body']) || !is_array($db['body']))
        {
            Sole::error('DB_COR');
        }

        Sole::$databases[$name] = $db;
    }

    public static function get($db, $select = null, $where = null, $order = null)
    {
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

    public static function setField($field)
    {

    }

    public static function deleteField($field)
    {

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
                $error = 'Database does not exist.';
                break;

            case 'DB_COR':
                $error = 'Database is corrupted.';
                break;

            default:
                $error = 'An unknown error occurred.';
        }

        trigger_error($error, E_USER_ERROR);
    }

}

?>