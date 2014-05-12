<?php

/**
 * DragObjectModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Stores the screen coordinates of a dragable object in the session variable.
 * @see /htdocs/tools/dragobject.php
 */
class DragObjectModel {

	public static function getCoords($id, &$top, &$left) {
		if (isset($_SESSION['dragobject']) && isset($_SESSION['dragobject'][$id])) {
			$top = (int) $_SESSION['dragobject'][$id]['top'];
			$left = (int) $_SESSION['dragobject'][$id]['left'];
		}
	}

}
