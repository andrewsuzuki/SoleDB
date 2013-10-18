<?php


require_once 'class.sole.php';

new Sole;

Sole::load('users');

var_dump(Sole::get('users', null, null, array('a', 'ASC', 'CI')));


/*
$csv = 'one,"t,wo",three';

var_dump(str_getcsv($csv));

var_dump($testdb);

echo '<br><br><br>';

echo json_encode($testdb);

$testdb = array(
    'head'=>array(
        'id','phone','username','password'
        ),
    'body'=>array(
        array(1,'123-123-1234','user1','23940238h8g2hha'),
        array(2,null,'user2','a494in2f32n3n2u'),
        array(3,'123-123-1234','user3','23940238h8g2hha'),
        )
    );
*/

?>