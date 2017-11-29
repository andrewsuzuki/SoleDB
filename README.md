SoleDB v2 By Snaddyvitch-Dispenser
======

SoleDB is a simple flat-file database system written in PHP. It was written to mimic typical SQL queries, but with a more structured syntax. Data is stored in JSON files, which are simple and take up little space (compared to typical XML database systems).

## Requirements

* PHP 5
* Read/write permissions

## Usage

Include the single class file sole.class.php.

`require_once 'sole.class.php';`

Instantiate the class. This is often as simple as:

`new Sole();`

You can supply a number of options to the class here. For example:

`new Sole(array('dir'=>dirname(__FILE__).'/data/'));`

The option 'dir' allows you to set an absolute path to a custom directory to hold your databases. It defaults to the directory data/ in the same directory as the class file.

Load a database. Every query with the exceptions of new/delete database requires database to be loaded.

`Sole::load('users');`

Run queries ('users' is an example database name):

*Note:* Each query requires a database name as its first parameter.

**New Database** `Sole::newDB($db)`

**Delete Database** `Sole::deleteDB($db)`

**New Field (Column)** `Sole::insertField($db, $field, $position = null, $default = '')`

* $field is new field name
* $position (optional) is position of new field/column in database. Can be [beginning] for the first position, [end] for the last position, or the name of any existing field for it to be inserted after. If omitted, null, or supplied field does not exist, field will be inserted at end.
* $default (optional) will populate the new cell in each row with this value. Defaults to an empty string ('')

**Delete Field** `Sole::deleteField($db, $field)`

* $field is name of field to be deleted

**Insert row** `Sole::insert($db, $data = array())`

* $data is an associative array of key/values of to-be-inserted cells in a new row. Similar to SQL, not every field needs to be added. For example, in a database with fields id, username, password, an example insert can be `Sole::insert('users', array('id'=>4, 'username'=>'johnbrown'))`

**Update Row** `Sole::update($db, $data = array(), $where = array())`

* $data *see the insert() method description for $data*
* $where (optional) selects which row(s) will be updated. *see the get() method description for $where*

**Delete Row** `Sole::delete($db, $where = array())`

* $where selects which row(s) will be deleted. *see the get() method description for $where*

**Select/Get/Read** (see below for return info) `Sole::get($db, $select = null, $where = null, $order = null)`

* $select (optional) is an array which chooses which fields/columns to be returned.
* $where (optional) selects which row(s) will be returned. Associative key/values are given, where key is field name and value is value to match. If multiple key/values are given, a logical AND will be applied. For example, if `array('state'=>'NY', 'name'=>'John')` is supplied, results will only return rows of people named John living in New York.
* $order (optional) orders rows to be returned. This is given in an array up to three elements, in the format `array(FIELD, ASC/DSC, CS/CI)`. FIELD chooses which field to order by, ASC/DSC determinies if order will be ascending or descending (ASC default), and CS/CI determines if sorting will be case sensitive or case insensitive (CS default). Each should be supplied as strings.

That's it! Simple!

### Select/Get/Read return values

Database select returns data in the form of a multidimensional associative array. The main indexed array holds each row as an array, each of which contains a "cell" for each row complete with field name as array key and field value as array value.

For example, here is an example var_dump of a database read with fields id, username, password:

```
array(2) {
  [0]=>
  array(3) {
    ["id"]=>
    int(1)
    ["username"]=>
    string(5) "admin"
    ["password"]=>
    string(13) "ohhii4ufnkd33"
  }
  [1]=>
  array(3) {
    ["id"]=>
    int(2)
    ["username"]=>
    string(4) "john"
    ["password"]=>
    string(11) "kbai33kddda"
  }
}
```

### Example database print

The below code shows an example usage by printing out the entire database (without field names).

```php
require_once 'class.sole.php';

new Sole;

Sole::load('users');

echo '<table>';

foreach(Sole::get('users') as $row)
{
    echo '<tr>';
    foreach ($row as $cell)
    {
        echo '<td>'.$cell.'</td>';
    }
    echo '</tr>';
}

echo '</table>';
```
