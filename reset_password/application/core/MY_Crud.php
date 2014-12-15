<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Crud extends CI_Model {

	private $table = '';

	/**
	 *
	 * @param String $table
	 */
	public function setTable($table)
	{
		$this->table = $table;
	}

	/**
	 *
	 * @return String
	 */
	public function getTable()
	{
		return $this->table;
	}

	/**
	 *
	 * @param Array $data
	 * @return int
	 */
	public function insert(array $data)
	{
		$this->db->insert($this->table, $data);
		return $this->db->insert_id();
	}

	/**
	 *
	 * @param Array $where
	 * @param Array $where
	 */
	public function update(array $data, array $where)
	{
		$this->db->update($this->table, $data, $where);
	}

	/**
	 *
	 * @param Array $where
	 */	
	public function delete(array $where)
	{
		$this->db->delete($this->table, $where);
	}

	/**
	 *
	 * @param Array $where
	 * @return Array
	 */
	public function getAllResults(array $where = null, $limit = null, $start = null)
	{
		$query = $this->db->get_where($this->table, $where,  $limit, $start);
		return $query->result_array();
	}

	/**
	 *
	 * @param Array $where
	 * @return Array
	 */
	public function getSingleResult(array $where)
	{
		$query = $this->db->get_where($this->table, $where);
		return $query->row_array();
	}
	
	/**
	 *
	 * @param String $sql
	 * @return Array
	 */
	public function executeQuery($sql)
	{
		$query = $this->db->query($sql);
		return $query->result_array();
	}

	/**
	 *
	 * @param Arrat $where
	 * @return Int
	 */
	public function numRows(array $where = null)
	{
		$query = $this->db->get_where($this->table, $where);
		return $query->num_rows();
	}

}
