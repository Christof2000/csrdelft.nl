<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrToegangException;
use CsrDelft\controller\framework\AclController;
use CsrDelft\model\documenten\DocumentCategorieModel;
use CsrDelft\model\documenten\DocumentModel;
use CsrDelft\model\entity\documenten\Document;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\CsrLayoutPage;
use CsrDelft\view\documenten\DocumentBewerkenForm;
use CsrDelft\view\documenten\DocumentContent;
use CsrDelft\view\documenten\DocumentDownloadContent;
use CsrDelft\view\documenten\DocumentToevoegenForm;
use CsrDelft\view\JsonResponse;
use CsrDelft\view\PlainView;

/**
 * DocumentenController.class.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 * @property DocumentModel $model
 */
class DocumentenController extends AclController {

	/**
	 * querystring:
	 *
	 * actie[/id[/opties]]
	 */
	public function __construct($query) {
		parent::__construct($query, DocumentModel::instance());
		$this->acl = array(
			'recenttonen' => P_DOCS_READ,
			'bekijken' => P_DOCS_READ,
			'download' => P_DOCS_READ,
			'categorie' => P_DOCS_READ,
			'zoeken' => P_DOCS_READ,
			'bewerken' => P_DOCS_MOD,
			'toevoegen' => P_DOCS_MOD,
			'verwijderen' => P_DOCS_MOD
		);
	}

	public function performAction(array $args = array()) {
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		} else {
			$this->action = 'recenttonen';
		}
		$this->view = parent::performAction($this->getParams(3));
	}

	/**
	 * Recente documenten uit alle categorieën tonen
	 */
	public function recenttonen() {
		$model = DocumentCategorieModel::instance();
		return view('documenten.documenten', [
			'categorieen' => $model->find(),
			'model' => $model
		]);
	}

	public function verwijderen($id) {
		$document = $this->model->get($id);

		if ($document === false) {
			setMelding('Document bestaat niet!', -1);
			redirect('/documenten');
		} elseif ($document->magVerwijderen()) {
			DocumentModel::instance()->delete($document);
		} else {
			setMelding('Mag document niet verwijderen', -1);
			return new JsonResponse(false);
		}

		return new PlainView(sprintf('<tr class="remove" id="document-%s"></tr>', $document->id));
	}

	public function bekijken($id) {
		$document = $this->model->get($id);

		if (!$document->magBekijken()) {
			throw new CsrToegangException();
		}

		if ($document->hasFile()) {
			return new DocumentContent($document);
		} else {
			setMelding('Document heeft geen bestand.', -1);
			redirect('/documenten');
		}
	}

	public function download($id) {
		$document = $this->model->get($id);

		if (!$document->magBekijken()) {
			throw new CsrToegangException();
		}
		if ($document->hasFile()) {
			return new DocumentDownloadContent($document);
		} else {
			setMelding('Document heeft geen bestand.', -1);
			redirect('/documenten');
		}
	}

	public function categorie($id) {
		$categorie = $this->model->getCategorieModel()->get($id);
		if ($categorie === false) {
			setMelding('Categorie bestaat niet!', -1);
			redirect('/documenten');
		} elseif (!$categorie->magBekijken()) {
			throw new CsrToegangException('Mag deze categorie niet bekijken');
		} else {
			return view('documenten.categorie', [
				'documenten' => $this->model->getCategorieModel()->getRecent($categorie, 0),
				'categorie' => $categorie,
			]);
		}
	}

	public function bewerken($id) {
		$document = $this->model->get($id);

		if ($document === false) {
			setMelding('Document niet gevonden', 2);
			redirect('/documenten');
		}

		$form = new DocumentBewerkenForm($document);
		if ($form->isPosted() && $form->validate()) {
			$this->model->update($document);

			redirect('/documenten/categorie/' . $document->categorie_id);
		} else {
			return view('default', [
				'titel' => 'Document bewerken',
				'content' => $form,
			]);
		}

	}

	public function toevoegen() {
		$form = new DocumentToevoegenForm();

		if ($form->isPosted() && $form->validate()) {
			/** @var Document $document */
			$document = $form->getModel();

			$document->eigenaar = LoginModel::getUid();
			$document->toegevoegd = getDateTime();

			$bestand = $form->getUploader()->getModel();

			$document->filename = $bestand->filename;
			$document->mimetype = $bestand->mimetype;
			$document->filesize = $bestand->filesize;

			$document->id = $this->model->create($document);

			if ($document->hasFile()) {
				$document->deleteFile();
			}

			$form->getUploader()->opslaan($document->getPath(), $document->getFullFileName());

			redirect('/documenten/categorie/' . $document->categorie_id);
		} else {
			return view('default', [
				'titel' => 'Document toevoegen',
				'content' => $form,
			]);
		}
	}

	public function zoeken() {
		if (!$this->hasParam('q')) {
			$this->exit_http(403);
		}
		$zoekterm = $this->getParam('q');

		if ($this->hasParam('limit')) {
			$limit = (int)$this->getParam('limit');
		} else {
			$limit = 5;
		}

		$result = array();
		foreach ($this->model->zoek($zoekterm, $limit) as $doc) {
			if ($doc->magBekijken()) {
				$result[] = array(
					'url' => '/documenten/bekijken/' . $doc->id . '/' . $doc->filename,
					'label' => $this->model->getCategorieModel()->find('id = ?', [$doc->categorie_id])->fetch()->naam,
					'value' => $doc->naam,
					'id' => $doc->id
				);
			}
		}
		return new JsonResponse($result);
	}
}
