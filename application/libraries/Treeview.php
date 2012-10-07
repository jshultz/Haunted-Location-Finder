<?php
	/**
	 * Created by JetBrains PhpStorm.
	 * User: OpenSkyMedia
	 * Date: 7/29/12
	 * Time: 2:16 PM
	 *
	 */
	class Treeview
	{

		public $sectionid = 1;
		private $nodesql = "SELECT pageid, parentid, page_name, rank, sectionid FROM pages ";
		private $anchor = "/pages/index/";
		private $orderby = " order by parentid, rank, pageid, sectionid";

		function __construct()
		{
			$this->obj =& get_instance();
		}

		// built specifically for silver stripe tree menu
		public function buildmenu()
		{
			// get the nodes array starting with the parent one
			$menu_array = $this->get_menu_nodes();

			$html='';
			foreach ($menu_array as $menu)
			{

				if ($this->hasChildren($menu['pageid']))
				{
					$html.="<li class=\"closed\"><a href=\"".$this->anchor . $this->sectionid . "/". $menu['pageid'] . "/\">
                      " . $menu['node'] . "</a>\n<ul>\n";
					$childarray = $menu['children'];
					foreach ( $childarray as $child)
					{
						$html.="<li><a href=\"".$this->anchor . $this->sectionid ."/" . $child['pageid'] . "/\">
                      " . $child['node'] . "</a></li>\n";
					}
					$html.= "</li>\n</ul>\n";

				} else {
					$html.="<li><a href=\"".$this->anchor . $this->sectionid ."/".  $menu['pageid'] . "/\">
                      " . $menu['node'] . "</a></li>\n";
				}

			}

			return $html;
		}

		//starts the gathering the section's parent nodes
		function get_menu_nodes()
		{
			$sql = "$this->nodesql ";

			// First get top level nodes i.e. parent id = 0
			$sql .= " WHERE sectionid =  $this->sectionid and parentid = 0 ";

			$sql .= $this->orderby;

			$result = $this->build_menu_array($sql);
			return $result;
		}

		//called if required by build_menu_array
		//@param pid = collects nodes with this parent id
		function get_child_nodes($parentid)
		{
			// just get top level nodes initially
			$sql = "$this->nodesql
                WHERE parentid = $parentid
                $this->orderby";
			$result = $this->build_menu_array($sql);
			return $result;
		}

		//the recursive menu 'engine'
		function build_menu_array($sql)
		{

			$query = $this->obj->db->query($sql);

			foreach ($query->result_array() as $row)
			{
				$node_items = array();
				$node_items['pageid'] = $row['pageid'];
				$node_items['node'] = $row['page_name'];

				// if the node has children get them now - recursive
				// store in in children array
				if ($this->hasChildren($row['pageid']))
				{
					$children = $this->get_child_nodes($row['pageid']);
					$node_items['children'] = $children;
				} else {
					$node_items['children'] = '';
				}
				$node_array[] = $node_items;
			}
			return $node_array;
		}


		function hasChildren($id)
		{
			$bool = FALSE;
			$sql = "Select pageid from pages where parentid = $id";
			$query = $this->obj->db->query($sql);

			if ($query->num_rows() > 0) $bool = TRUE;

			return $bool;
		}


	}
