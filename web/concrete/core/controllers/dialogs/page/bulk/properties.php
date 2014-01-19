<?
defined('C5_EXECUTE') or die("Access Denied.");
class Concrete5_Controller_Dialogs_Page_Bulk_Properties extends BackendInterfaceController {

	protected $viewPath = '/system/dialogs/page/bulk/properties';
	protected $pages = array();
	protected $canEdit = false;

	protected function canAccess() {
		$this->populatePages();
		return $this->canEdit;
	}

	protected function populatePages() {
		if (is_array($_REQUEST['item'])) {
			foreach($_REQUEST['item'] as $cID) {
				$c = Page::getByID($cID);
				if (is_object($c) && !$c->isError()) {
					$this->pages[] = $c;
				}
			}
		}

		if (count($this->pages) > 0) {
			$this->canEdit = true;
			foreach($this->pages as $c) {
				$cp = new Permissions($c);
				if (!$cp->canEditPageProperties()) {
					$this->canEdit = false;
				}
			}
		} else {
			$this->canEdit = false;
		}

		return $this->canEdit;
	}

	public function view() {
		$r = ResponseAssetGroup::get();
		$r->requireAsset('core/app/editable-fields');
		$this->populatePages();
		$form = Loader::helper('form');
		$attribs = CollectionAttributeKey::getList();
		$this->set('pages', $this->pages);
		$this->set('attributes', $attribs);
	}

	public function updateAttribute() {
		$pr = new PageEditResponse();
		$ak = CollectionAttributeKey::get($_REQUEST['name']);
		if ($this->validateAction()) {
			$this->populatePages();
			if ($this->canEdit) {
				foreach($this->pages as $c) {
					$ak->saveAttributeForm($c);
					$c->reindex();
				}


				$pr->setPages($this->pages);
				$val = $c->getAttributeValueObject($ak);
				$pr->setAdditionalDataAttribute('value',  $val->getValue('displaySanitized','display'));
				$pr->setMessage(t('Pages updated successfully.'));
			}
		}
		$pr->outputJSON();
	}

	public function clearAttribute() {
		$pr = new PageEditResponse();
		$ak = CollectionAttributeKey::get($_REQUEST['akID']);
		if ($this->validateAction()) {
			$this->populatePages();
			if ($this->canEdit) {
				foreach($this->pages as $c) {
					$c->clearAttribute($ak);			
					$c->reindex();
				}
				$pr->setPages($this->pages);
				$pr->setAdditionalDataAttribute('value',  false);
				$pr->setMessage(t('Attributes cleared successfully.'));
			}
		}
		$pr->outputJSON();
	}

}

