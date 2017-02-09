<?php

/**
 * memcachestats.php
 * 
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
use CsrDelft\Orm\DataBase\OrmMemcache;

require_once 'configuratie.include.php';

if (DEBUG OR LoginModel::mag('P_ADMIN') OR LoginModel::instance()->isSued()) {

	echo getMelding();

	echo '<h1>MemCache statistieken</h1>';

	debugprint(OrmMemcache::instance()->getStats());
}