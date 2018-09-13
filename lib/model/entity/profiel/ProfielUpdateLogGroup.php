<?php

namespace CsrDelft\model\entity\profiel;


use CsrDelft\model\ProfielModel;

/**
 * ProfielUpdateLogGroup.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author Sander Borst <s.borst@live.nl>
 *
 * LogGroup uit het legacy log die nog niet geparsed is.
 *
 */
class ProfielUpdateLogGroup extends ProfielLogGroup {
	/**
	 * All changes in the entry
	 * @var AbstractProfielLogEntry[]
	 */
	public $entries;



	public function __construct($editor, $timestamp, $entries) {
		parent::__construct($editor, $timestamp);
		$this->entries = $entries;

	}

	/**
	 * @return string
	 */
	public function toHtml() {
		$changesHtml = [];
		foreach ($this->entries as $change) {
			$changesHtml[] = "<div class='change'>{$change->toHtml()}</div>";
		}
		return "<div class='ProfielLogEntry'>
			<div class='metadata'>Gewijzigd door ".ProfielModel::getLink($this->editor, 'civitas')." ".($this->timestamp === null ? "?" : reldate($this->timestamp->format('Y-m-d H:i:s')))."</div>
			".implode($changesHtml)."
			</div>";
	}
}