<?php


require_once 'class.sole.php';

new Sole;

Sole::load('users');

//Sole::deleteDB('pages');

//Sole::newDB('pages');

//var_dump(Sole::get('users', null, null, array('a', 'ASC', 'CI')));

//Sole::insert('users', array('id'=>10,'phone'=>'555-555-5555','heyy'=>'666 NO ST','username'=>'heyy','password'=>'poooooo'));

Sole::update('users', array('password'=>'passwordhehe', 'address'=>'444 Cott St'), array('password'=>'passwordhehe'));

//Sole::delete('users', array('id'=>10));

//Sole::setField('users', 'nam!e', 'phone', 'John B');

//Sole::deleteField('users', 'name');

echo '<table>';

foreach(Sole::get('users') as $row)
{
    echo '<tr>';
    // echo '<td>'.$row[''].'</td>';
    foreach ($row as $cell)
    {
        echo '<td>'.$cell.'</td>';
    }
    echo '</tr>';
}

echo '</table>';

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