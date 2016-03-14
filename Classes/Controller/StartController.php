<?php
namespace Pits\Catmenu\Controller;

class StartController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {



	/**
	 * @var \Pits\Catmenu\Domain\Repository\MenuRepository
	 * @inject
	 */
	protected $menuRepository;

	/**
	 * @var \TYPO3\CMS\Frontend\Page\PageRepository
	 * @inject
	 */
	protected $pageRepository;


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
		$parent = $this->pageRepository->getRootLine($pageid);
		$pids = array();
		foreach ($parent as $key => $value) {
			$pids[] = $value['uid'];
		}
		if(!in_array($this->rootFolderId , $pids))
		{	
            $new_page_id = 0;	 
            $d = new \datetime();
            $time = time();
			$temp = $this->pageRepository->getMenu($this->rootFolderId,'uid,title,starttime,endtime,deleted,hidden','title desc' ,' ');
            if(!empty($temp)){ 
                foreach ($temp as $key => $value) {
                    if(
                        ($value['starttime'] <= $time || $value['starttime'] ==0)   &&  
                        ($value['endtime'] >= $time || $value['endtime'] ==0)  && 
                        ($value['deleted'] == 0 && $value['hidden'] ==0 )     
                    ){
                        $temp2 = $this->pageRepository->getMenu($value['uid'],'uid,title,starttime,endtime,deleted,hidden','title desc',""  );
                        if(!empty($temp2)){ 
                            foreach ($temp2 as $k => $val) {
                                if(
                                    ($val['starttime'] <= $time || $val['starttime'] ==0)   &&  
                                    ($val['endtime'] >= $time || $val['endtime'] ==0)  && 
                                    ($val['deleted'] == 0 && $val['hidden'] ==0 )     
                                ){
                                    $new_page_id = $val['uid'];
                                    break;
                                }                             
                            }  
                        }
                        if($new_page_id !=0){
                            break;
                        }                      
                    }
                }      
            }
            $pageid = $new_page_id ? $new_page_id : $pageid;
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