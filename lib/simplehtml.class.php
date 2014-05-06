<?php

# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# simplehtml.class.php
# -------------------------------------------------------------------
# Van deze klasse worden alle klassen afgeleid die ervoor
# bedoeld zijn om uiteindelijk HTML te tonen met view()
# -------------------------------------------------------------------

abstract class SimpleHTML implements View {

	public function getTitel() {
		return '';
	}

	public function setMelding($sMelding, $level = -1) {
		setMelding($sMelding, $level);
	}

	/**
	 * Geeft berichten weer die opgeslagen zijn in de sessie met met setMelding($message, $lvl = -1)
	 * Levels can be:
	 *
	 * -1 error
	 *  0 info
	 *  1 success
	 *  2 notify
	 *
	 * @return string html van melding(en) of lege string
	 */
	public static function getMelding() {
		if (isset($_SESSION['melding']) AND is_array($_SESSION['melding'])) {
			$sMelding = '<div id="melding">';
			$shown = array();
			foreach ($_SESSION['melding'] as $msg) {
				$hash = md5($msg['msg']);
				//if(isset($shown[$hash])) continue; // skip double messages
				$sMelding .= '<div class="msg' . $msg['lvl'] . '">';
				$sMelding.=$msg['msg'];
				$sMelding .= '</div>';
				$shown[$hash] = 1;
			}
			$sMelding .= '</div>';
			//maar één keer tonen, de melding.
			unset($_SESSION['melding']);
			return $sMelding;
		} else {
			return '';
		}
	}

	public static function getStandaardZijkolom() {
		$zijkolom = array();
		// Is het al...
		if (LidInstellingen::get('zijbalk', 'ishetal') != 'niet weergeven') {
			require_once('ishetalcontent.class.php');
			$zijkolom[] = new IsHetAlContent(LidInstellingen::get('zijbalk', 'ishetal'));
		}
		// Ga snel naar
		if (LidInstellingen::get('zijbalk', 'gasnelnaar') == 'ja') {
			require_once('MVC/model/MenuModel.class.php');
			require_once('MVC/view/MenuView.class.php');
			$zijkolom[] = new BlockMenuView(MenuModel::instance()->getMenuTree('Ga snel naar'));
		}
		// Agenda
		if (LoginLid::mag('P_AGENDA_READ') && LidInstellingen::get('zijbalk', 'agendaweken') > 0) {
			$zijkolom[] = new AgendaZijbalkView(AgendaModel::instance(), LidInstellingen::get('zijbalk', 'agendaweken'));
		}
		// Laatste mededelingen
		if (LidInstellingen::get('zijbalk', 'mededelingen') > 0) {
			require_once('mededelingen/mededeling.class.php');
			require_once('mededelingen/mededelingencontent.class.php');
			$zijkolom[] = new MededelingenZijbalkContent((int) LidInstellingen::get('zijbalk', 'mededelingen'));
		}
		// Nieuwste belangrijke forumberichten
		if (LidInstellingen::get('zijbalk', 'forum_belangrijk') > 0) {
			require_once('MVC/model/ForumModel.class.php');
			require_once('MVC/view/ForumView.class.php');
			$zijkolom[] = new ForumDraadZijbalkView(
					ForumDradenModel::instance()->getRecenteForumDraden(
							(int) LidInstellingen::get('zijbalk', 'forum_belangrijk'), true), true);
		}
		// Nieuwste forumberichten
		if (LidInstellingen::get('zijbalk', 'forum') > 0) {
			require_once('MVC/model/ForumModel.class.php');
			require_once('MVC/view/ForumView.class.php');
			$belangrijk = (LidInstellingen::get('zijbalk', 'forum_belangrijk') > 0 ? false : null);
			$zijkolom[] = new ForumDraadZijbalkView(
					ForumDradenModel::instance()->getRecenteForumDraden(
							(int) LidInstellingen::get('zijbalk', 'forum'), $belangrijk), $belangrijk);
		}
		// Zelfgeposte forumberichten
		if (LidInstellingen::get('zijbalk', 'forum_zelf') > 0) {
			require_once('MVC/model/ForumModel.class.php');
			require_once('MVC/view/ForumView.class.php');
			$posts_draden = ForumPostsModel::instance()->getRecenteForumPostsVanLid(LoginLid::instance()->getUid(), LidInstellingen::get('zijbalk', 'forum_zelf'), true);
			$zijkolom[] = new ForumPostZijbalkView($posts_draden[0], $posts_draden[1]);
		}
		// Nieuwste fotoalbum
		if (LidInstellingen::get('zijbalk', 'fotoalbum') == 'ja') {
			require_once 'fotoalbumcontent.class.php';
			$zijkolom[] = new FotalbumZijbalkContent();
		}
		// Komende verjaardagen
		if (LidInstellingen::get('zijbalk', 'verjaardagen') > 0) {
			require_once 'lid/verjaardagcontent.class.php';
			$zijkolom[] = new VerjaardagContent('komende');
		}
		return $zijkolom;
	}

	public static function getDebug($sql = true, $get = true, $post = true, $files = true, $cookie = true, $session = true) {
		$debug = '';
		if ($sql) {
			$debug .= '<hr />SQL<hr />';
			$debug .= '<pre>' . htmlentities(print_r(array("PDO" => Database::getQueries(), "MySql" => MySql::instance()->getQueries()), true)) . '</pre>';
		}
		if ($get) {
			$debug .= '<hr />GET<hr />';
			if (count($_GET) > 0) {
				$debug .= '<pre>' . htmlentities(print_r($_GET, true)) . '</pre>';
			}
		}
		if ($post) {
			$debug .= '<hr />POST<hr />';
			if (count($_POST) > 0) {
				$debug .= '<pre>' . htmlentities(print_r($_POST, true)) . '</pre>';
			}
		}
		if ($files) {
			$debug .= '<hr />FILES<hr />';
			if (count($_FILES) > 0) {
				$debug .= '<pre>' . htmlentities(print_r($_FILES, true)) . '</pre>';
			}
		}
		if ($cookie) {
			$debug .= '<hr />COOKIE<hr />';
			if (count($_COOKIE) > 0) {
				$debug .= '<pre>' . htmlentities(print_r($_COOKIE, true)) . '</pre>';
			}
		}
		if ($session) {
			$debug .= '<hr />SESSION<hr />';
			if (count($_SESSION) > 0) {
				$debug .= '<pre>' . htmlentities(print_r($_SESSION, true)) . '</pre>';
			}
		}
		return $debug;
	}

}
