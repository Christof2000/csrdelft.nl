<?php
class EetplanBekenden extends PersistentEntity {
    public $uid1;
    public $uid2;

    protected static $table_name = 'eetplan_bekendend';
    protected static $persistent_attributes = array(
        'uid1' => array(T::UID, false),
        'uid2' => array(T::UID, false),
    );
    protected static $primary_key = array('uid1', 'uid2');
}
