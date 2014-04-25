<?php

/**
 * Woonoord.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een woonoord is waar C.S.R.-ers bij elkaar wonen.
 * 
 */
class Woonoord extends Groep {

	/**
	 * Veranderingen van huisstatus
	 * @var string
	 */
	public $status_historie;
	/**
	 * woonoord / huis
	 * @see HuisStatus
	 * @var string
	 */
	public $huis_status;
	/**
	 * Database table fields
	 * @var array
	 */
	protected static $persistent_fields = array(
		'status_historie' => array('text', null, true),
		'huis_status' => array('varchar', 255, true)
	);
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'woonoorden';

	/**
	 * Extend the persistent fields.
	 */
	public static function __constructStatic() {
		parent::__constructStatic();
		self::$persistent_fields = parent::$persistent_fields + self::$persistent_fields;
	}

}
