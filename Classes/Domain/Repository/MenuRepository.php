<?php
namespace Pits\Catmenu\Domain\Repository; 

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2015
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * The repository for Menus
 */
class MenuRepository extends \TYPO3\CMS\Extbase\Persistence\Repository {

	function getCategories($parent = ''){

		$sql = "SELECT * FROM 'sys_category' where parent in($parent) and deleted = 0 and hidden =0";
		$categories =array();
		$res =  $GLOBALS['TYPO3_DB']->sql_query($sql);
		while ($row =  $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {	
			$categories[] = $row;
		}
		return $categories;
	}


	function getRecentArticleBycategories($categories,$rootFolderId){
		$catMenu = array();
		foreach ($categories as $key => $value) {			 
			$cat = $value['uid'];
			$catMenu[$cat]['uid'] = $value['uid'];
			$catMenu[$cat]['title'] = isset($value['title']) ? $value['title'] : '';
			$subpages = $this->getRecentPageByCategory($rootFolderId,$cat);
			$catMenu[$cat]['articles'] =  $subpages;//['recent'];
			$catMenu[$cat]['articlesCount'] =  count($subpages);
			if($catMenu[$cat]['articlesCount'] == 0){
				unset($catMenu[$cat]);
			}
		}
		return $catMenu;
	}


	function getSingleIssueArticleBycategories($categories,$rootFolderId){
		$catMenu = array();
		foreach ($categories as $key => $value) {			 
			$cat = $value['uid'];
			$catMenu[$cat]['uid'] = $value['uid'];
			$catMenu[$cat]['title'] = isset($value['title']) ? $value['title'] : '';
			$subpages = $this->getSingleIssuePageByCategory($rootFolderId,$cat);
			$catMenu[$cat]['articles'] =  $subpages;//['recent'];
			$catMenu[$cat]['articlesCount'] =  count($subpages);	 
		}
 
		return $catMenu;
	}


	function getSingleIssuePageByCategory($pageid,$cat){

		$pid = $vol;	
		$time = time();
		$article = array();	
		$articles = array();	 	

		$sql = "SELECT pages.*, sys_category_record_mm.uid_local FROM pages 
		left join sys_category_record_mm on sys_category_record_mm.uid_foreign = pages.uid 	
		where sys_category_record_mm.uid_local in($cat) 
		and pid = $pageid 
		and deleted = 0 and hidden =0 
		and (( pages.starttime <= $time or pages.starttime =0 ) and ( pages.endtime >= $time or pages.endtime =0 ) )
		group by pages.uid order by pages.sorting ASC limit 5 ";	
		$res =  $GLOBALS['TYPO3_DB']->sql_query($sql);
 
		while ($row =  $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
		 
				$article['uid'] = $row['uid'];
				$article['title'] = $row['title'];
				$article['url'] =  '';	 
				$article['abstract'] = $row['abstract'];
				 ++$i;
 				$articles[] = $article;
		}

 		return $articles;

	}

	function getRecentPageByCategory($pageid,$cat){	

		$cObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer'); 
		$sql = "SELECT * FROM 'pages' where pid = $pageid and deleted = 0 and hidden =0 order by sorting ASC";
		$res =  $GLOBALS['TYPO3_DB']->sql_query($sql);
		$years = array();
		$articles = array();
		$time = time();
		while ($row =  $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {	
			$years[] = $row;
		}

		foreach ($years as $key => $year) {
			
			$pid = $year['uid']	;
			$sql = "SELECT * FROM 'pages' where pid = $pid and deleted = 0 and hidden =0 order by sorting ASC limit 1 ";
			$res =  $GLOBALS['TYPO3_DB']->sql_query($sql);
			$vols = array();
			while ($row =  $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {	
				$vols[] = $row['uid'];
			}

			foreach ($vols as $k => $vol) {
				$pid = $vol;				
				$sql = "SELECT pages.*, sys_category_record_mm.uid_local FROM pages 
				left join sys_category_record_mm on sys_category_record_mm.uid_foreign = pages.uid 	
				where sys_category_record_mm.uid_local in($cat) 
				and pid = $pid 
				and deleted = 0 and hidden =0 
				and (( pages.starttime <= $time or pages.starttime =0 ) and ( pages.endtime >= $time or pages.endtime =0 ) )
				group by pages.uid order by pages.sorting ASC limit 3 ";	
				$res =  $GLOBALS['TYPO3_DB']->sql_query($sql);
				while ($row =  $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
					if(count($articles) < 3){
						$article['uid'] = $row['uid'];
						$article['title'] = $row['title'];
						$article['url'] =  $cObj->typoLink_URL(array('parameter' => $pageid)); //'index.php?id=' .$row['uid'];			 
						$article['abstract'] = $row['abstract'];
						$articles[] = $article;
 					}else
					{
						break;
					}					
				}
			}
			if(count($articles) >= 3){	 
				break;
			}
		}	
		return $articles;
	}

 


	function getsubpages($pageid,$cat,$level=0)	{
		$pages =array();
		$time = time();
		// $sql = "SELECT * FROM 'pages' where pid = $pageid order by title DESC";
		// Revision by PW from Abteilung: Pages must be sorted by pagetree
		$sql = "SELECT * FROM 'pages' where pid = $pageid order by sorting ASC";
		if($level >= 2)
		{
			if($cat == '') return array();
			$sql = "SELECT pages.*, sys_category_record_mm.uid_local FROM pages 
			left join sys_category_record_mm on sys_category_record_mm.uid_foreign = pages.uid 	
			where  pages.deleted = 0 and pages.hidden =0 
			and (( pages.starttime <= $time or pages.starttime =0 ) and ( pages.endtime >= $time or pages.endtime =0 ) )
			and sys_category_record_mm.uid_local in($cat) and pid = $pageid 
			group by pages.uid order by pages.title DESC ";
		}

		$res =  $GLOBALS['TYPO3_DB']->sql_query($sql);
		while ($row =  $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {			
			if($level >= 2) $row['media_url'] = $this->pageMedia($row['uid']);
			$pages[$row['uid'] ] = $row;
			$subpages =  $this->getsubpages($row['uid'],$cat,$level+1);
			$pages[$row['uid'] ]['childs'] = $subpages ;
			$pages[$row['uid'] ]['childcount'] = count($subpages);
		} 
		return $pages;
	}


	function getPageCategory($pageid){
		$sql = "select group_concat(sys_category.uid) as uid from sys_category_record_mm left join sys_category on sys_category.uid = sys_category_record_mm.uid_local where tablenames = 'pages' and uid_foreign =$pageid";
		$res =  $GLOBALS['TYPO3_DB']->sql_query($sql);
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		return $row['uid'];
	}


	function pageMedia($pageid){
		$sql = "SELECT sys_file_reference.uid,sys_file_reference.pid,sys_file.identifier FROM 'sys_file_reference' left join sys_file on sys_file.uid = sys_file_reference.uid_local where tablenames = 'pages' and fieldname = 'media' and sys_file_reference.pid =$pageid order by sorting_foreign asc ";
		$res =  $GLOBALS['TYPO3_DB']->sql_query($sql);
		$row =  $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		return $row['identifier'];

	}






}