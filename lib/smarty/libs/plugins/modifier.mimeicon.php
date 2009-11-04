<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty mime-icon modifier plugin
 *
 * Type:     modifier<br>
 * Name:     mimeicon<br>
 * Date:     October 26, 2009
 * Purpose:  Return a icon for a given mime-type.
 * Input:    mimetype
 * Example:  {$mimetype|mimeicon}
 * @link http://csrdelft.nl/feuten
 *          (svn repository)
 * @author   Jan Pieter Waagmeester < jpwaag at jpwaag dot com>
 * @version 1.0
 * @param string
 * @param string
 * @param bool
 * @return string
 */
function smarty_modifier_mimeicon($mimetype){

	if(		strpos($mimetype, 'image')!==false){ 		return Icon::getTag('mime-image');
	}elseif(strpos($mimetype, 'audio')!==false){		return Icon::getTag('mime-audio');
	}elseif(strpos($mimetype, 'ms-excel')!==false){		return Icon::getTag('mime-excel');
	}elseif(strpos($mimetype, 'msword')!==false){		return Icon::getTag('mime-word');
	}elseif(strpos($mimetype, 'ms-powerpoint')!==false){return Icon::getTag('mime-powerpoint');
	}elseif(strpos($mimetype, 'pdf')!==false){			return Icon::getTag('mime-pdf');
	}elseif(strpos($mimetype, 'plain')!==false){		return Icon::getTag('mime-plain');
	}elseif(strpos($mimetype, 'zip')!==false OR
			strpos($mimetype, 'rar')!==false){			return Icon::getTag('mime-zip');
	}else{												return Icon::getTag('mime-onbekend');
	}
}
