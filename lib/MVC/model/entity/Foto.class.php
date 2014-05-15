<?php

require_once 'MVC/model/entity/FotoAlbum.class.php';

/**
 * Foto.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class Foto extends Bestand {

	public function __construct(FotoAlbum $album, $bestandsnaam) {
		$this->map = $album;
		$this->bestandsnaam = $bestandsnaam;
	}

	public function getPad() {
		return $this->map->locatie . $this->bestandsnaam;
	}

	public function getThumbPad() {
		return $this->map->locatie . '_thumbs/' . $this->bestandsnaam;
	}

	public function getResizedPad() {
		return $this->map->locatie . '_resized/' . $this->bestandsnaam;
	}

	public function getThumbURL() {
		return CSR_PICS . direncode($this->map->getSubDir() . '_thumbs/' . $this->bestandsnaam);
	}

	public function getResizedURL() {
		return CSR_PICS . direncode($this->map->getSubDir() . '_resized/' . $this->bestandsnaam);
	}

	public function bestaatThumb() {
		$pad = $this->getThumbPad();
		return file_exists($pad) AND is_file($pad);
	}

	public function bestaatResized() {
		$pad = $this->getResizedPad();
		return file_exists($pad) AND is_file($pad);
	}

	public function maakThumb() {
		set_time_limit(0);
		$command = IMAGEMAGICK_PATH . ' ' . escapeshellarg($this->getPad()) . ' -thumbnail 150x150^^ -gravity center -extent 150x150 -format jpg -quality 80 ' . escapeshellarg($this->getThumbPad());
		$output = shell_exec($command) . '<hr />';
		if (defined('RESIZE_OUTPUT')) {
			echo $command . '<br />';
			echo $output;
		}
		if ($this->bestaatThumb()) {
			chmod($this->getThumbPad(), 0644);
		} else {
			setMelding('Thumb maken mislukt voor: ' . $this->getThumbPad(), -1);
		}
	}

	public function maakResized() {
		set_time_limit(0);
		$command = IMAGEMAGICK_PATH . ' ' . escapeshellarg($this->getPad()) . ' -resize 1024x1024 -format jpg -quality 85 ' . escapeshellarg($this->getResizedPad());
		$output = shell_exec($command) . '<hr />';
		if (defined('RESIZE_OUTPUT')) {
			echo $command . '<br />';
			echo $output;
		}
		if ($this->bestaatResized()) {
			chmod($this->getResizedPad(), 0644);
		} else {
			setMelding('Resized maken mislukt voor: ' . $this->getResizedPad(), -1);
		}
	}

	public function isCompleet() {
		return ($this->bestaatThumb() && $this->bestaatResized());
	}

}
