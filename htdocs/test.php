<?php

try {

	require_once 'configuratie.include.php';

	/*
	  $ubb = new CsrUbb();
	  echo $ubb->ubb_prive(array('prive' => 'P_LEDEN_MOD'));

	  //echo $ubb->getHTML('hoi[prive=P_LEDEN_MOD]hoi[/]');

	  /*
	  $mail = new Mail('brussee@live.nl','brussee@live.nl','test');
	  $mail->send();

	  /*
	  $model = MededelingenModel::instance();

	  $m1 = new Mededeling2();

	  $model->save($m1);
	  $id = $m1->id;
	  unset($m1);

	  $m2 = $model->fetch($id);

	  $model->remove($id);
	 */
} catch (Exception $e) {
	echo str_replace('#', '<br />#', $e); // stacktrace
}
