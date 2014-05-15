<?php

require_once 'MVC/model/entity/groepen/GroepFunctie.enum.php';

/**
 * GroepLid.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een lid van een groep.
 * 
 */
class GroepLid extends PersistentEntity {

	/**
	 * Type van groep
	 * @see Groep extends
	 * @var string
	 */
	public $groep_type;
	/**
	 * Lid van deze groep
	 * @var int
	 */
	public $groep_id;
	/**
	 * Uid van lid
	 * @var string
	 */
	public $lid_id;
	/**
	 * Opmerking bij lidmaatschap
	 * @see GroepFunctie
	 * @var string
	 */
	public $opmerking;
	/**
	 * Datum en tijd van aanmelden
	 * @var string
	 */
	public $lid_sinds;
	/**
	 * Datum en tijd van o.t.
	 * @var string
	 */
	public $lid_tot;
	/**
	 * o.t. / h.t. / f.t.
	 * @var GroepStatus
	 */
	public $status;
	/**
	 * Volgorde van weergave
	 * @var string
	 */
	public $prioriteit;
	/**
	 * Uid van aanmelder
	 * @var string
	 */
	public $door_lid_id;
	/**
	 * Database table fields
	 * @var array
	 */
	protected static $persistent_fields = array(
		'groep_type' => array(T::String),
		'groep_id' => array(T::Integer),
		'lid_id' => array(T::UID),
		'opmerking' => array(T::String),
		'lid_sinds' => array(T::DateTime),
		'lid_tot' => array(T::DateTime, true),
		'status' => array(T::Enumeration, false, 'GroepStatus'),
		'prioriteit' => array(T::Integer),
		'door_lid_id' => array(T::UID)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_keys = array('groep_type', 'groep_id', 'lid_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'groep_leden';

}
