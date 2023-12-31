<?php
namespace ZendX\Paginator;

class Paginator {
	
	public static function createPaginator($totalItems, $paginatorParams){
		
		$adapterPaginator	= new \Zend\Paginator\Adapter\NullFill($totalItems);
		$paginator			= new \Zend\Paginator\Paginator($adapterPaginator);
		
		$paginator->setCurrentPageNumber($paginatorParams['currentPageNumber']);
		$paginator->setPageRange($paginatorParams['pageRange']);
		$paginator->setItemCountPerPage($paginatorParams['itemCountPerPage']);
		
		return $paginator;
	}
}