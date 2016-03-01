<?php
namespace Typo3\Catmenu\Domain\Model;


class Start extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

	/**
	 * Some title.
	 *
	 * @var string
	 */
	protected $title = '';
	
    /**
	 * An empty constructor - fill it as you like
	 *
	 */
	public function __construct() {
		
	}
	
	
	/**
	 * Sets the title
	 * 
	 * @param string $title
	 * return void
	 */
	public function setTitle($title) {
		$this->title = $title;
	}
	
	/**
	 * Gets the title
	 * 
	 * @return string The title of the album
	 */
	public function getTitle() {
		return $this->title;
	}
	
}

?>