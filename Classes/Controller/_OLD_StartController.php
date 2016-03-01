<?php
namespace Typo3\Catmenu\Controller;

class StartController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {



	/**
	 * @var \Typo3\Catmenu\Domain\Repository\MenuRepository
	 * @inject
	 */
	protected $menuRepository;


	protected $rootFolderId = 0;
	protected $topCategory = 0;
	protected $magazin = 0;
	protected $facebook = '#';
	protected $twitter = '#';




	/**
	 * Initializes the current action
	 *
	 * @return void
	 */
	public function initializeAction() {

		$config = unserialize( $GLOBALS['GLOBALS']['TYPO3_CONF_VARS']['EXT']['extConf']['catmenu']);
		$this->rootFolderId = $config['rootFolderId'];
		$this->topCategory =  $config['topCategory'];
		$this->magazin =  $config['magazin'];
		$this->facebook =  $config['facebook'];
		$this->twitter =  $config['twitter'];
		


	}

	/**
	 * Index action for this controller.
	 *
	 * @return string The rendered view
	 */
	public function indexAction() {		

		$pageid =  intval($GLOBALS['TSFE']->id);
		$c_category = $this->menuRepository->getPageCategory($pageid);
		$root = $this->rootFolderId;
		$pages = $this->menuRepository->getsubpages($root,$c_category);
		$recent = array();
		$counter =0;

		foreach ($pages as $key => $page) {
			foreach ($page['childs'] as $index => $volume) {
				$year = $pages[$key]['title'];
				foreach ($volume['childs'] as $i => $value) {
					
					$volume = $pages[$key]['childs'] [$index]['title'];
					$t = $pages[$key]['childs'] [$index]['childs'][$i];
					$t['header'] = $volume . " / " . $year;

					if($counter <3)
					{
						
						
						//$t = $pages[$key]['childs'] [$index]['childs'][$i];
						//$t['header'] = $volume . " / " . $year;
						$recent[$t['uid']] = $t;
						unset($pages[$key]['childs'] [$index]['childs'][$i]);
						$pages[$key]['childs'] [$index]['childcount'] = count($pages[$key]['childs'] [$index]['childs']);

						if($pages[$key]['childs'] [$index]['childcount'] == 0)
						{
							unset($pages[$key]['childs'] [$index]);
							$pages[$key]['childcount'] = count($pages[$key]['childs']);
						}
						$counter++;	 			
					}else
					{
						$pages[$key]['childs'] [$index]['childs'][$i]['year'] =   $year;
					}
				}

				$pages[$key]['childs'] [$index]['year'] =   $year;			 	
			}
		}
 
		$this->view->assign('menus', $pages);
		$this->view->assign('recents', $recent);
	}


	/**
	 * Show action for this controller.
	 *
	 * @return string The rendered view
	 */
	public function showAction() {  
	 
		$pageid =  intval($GLOBALS['TSFE']->id);
		$categories = $this->menuRepository->getCategories($this->magazin);
		$recent = $this->menuRepository->getRecentArticleBycategories($categories,$this->rootFolderId);
		$this->view->assign('menus', $recent);
		$this->view->assign('pageid', $pageid);
	}


		/**
	 * Single action for this controller.
	 *
	 * @return string The rendered view
	 */
	public function singleAction() {  

	 
		$pageid =  intval($GLOBALS['TSFE']->id);
		if($pageid == 2){
			// Should not be hard coded
			$pageid = 279;
		}

		$categories = $this->menuRepository->getCategories($this->magazin);
		$recent = $this->menuRepository->getSingleIssueArticleBycategories($categories,$pageid);
		$this->view->assign('menus', $recent);
		$this->view->assign('pageid', $pageid);
	}


	public function sliderAction(){
		$categories = array(array('uid' => $this->topCategory));
		$recent = $this->menuRepository->getRecentArticleBycategories($categories,$this->rootFolderId);
		$this->view->assign('menus', $recent);	
		$this->view->assign('facebook', $this->facebook);	
		$this->view->assign('twitter', $this->twitter);	
 
 	}



	public function recentAction(){
		$categories = array(array('uid' => $this->topCategory));
		$recent = $this->menuRepository->getRecentArticleBycategories($categories,$this->rootFolderId);
		$this->view->assign('menus', $recent);	
		$this->view->assign('facebook', $this->facebook);	
		$this->view->assign('twitter', $this->twitter);	
 
 	}

 

}

?>