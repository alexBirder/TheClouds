<?php

class TTree{
	const primary_key = 'id';
	const parent_key = 'parent';
	const level_key = 'level';

	static $errors = array();

	// вставить новый элемент
	static function insert($DB, $tbl, $root = 0, $values = array()){
		if(intval($root) > 0){
			$level_query = sprintf("SELECT `%s` FROM `%s` WHERE `%s` = '%d'", self::level_key, $tbl, self::primary_key, $root);
			$level = $DB->result($DB->query($level_query)) + 1;
		}
		else{
			$level = 0;
		}

		$data = $values;
		$data[self::parent_key] = intval($root);
		$data[self::level_key] = $level;

		$keys	= array_keys($data);
		$vals	= array_values($data);
       	$query	= sprintf("INSERT INTO `%s` (`%s`) VALUES ('%s')", $tbl, join($keys, "`,`"), join($vals, "','"));

		return $DB->query($query) ? $DB->insert_id() : null;
	}

	// обновить элемент
	static function update($DB, $tbl, $id, $values = array()){
		$data = array();
       	$data = array_merge($data, $values);

		if(isset($data[self::parent_key])) unset($data[self::parent_key]);
		if(isset($data[self::level_key])) unset($data[self::level_key]);

		$set = array();
		foreach($data as $key=>$val)
			$set[] = sprintf("`%s` = '%s'", $key, $val);
		$set = join(', ', $set);
		$query	= sprintf("UPDATE `%s` SET %s WHERE `%s` = '%d'", $tbl, $set, self::primary_key, $id);

		return $DB->query($query);
	}

	// ID всех потомков
	static function sub_ids($DB, $tbl, $id, $level = 0, $i = 0){
		$return = array();
		$query = sprintf("SELECT `%s` FROM `%s` WHERE `%s` = '%d'", self::primary_key, $tbl, self::parent_key, $id);
		$result = $DB->sql2array($query);

		foreach($result as $row){
			$return[] = $row[self::primary_key];
			if($level == 0 || $i + 1 < $level){
				$subissues = self::sub_ids($DB, $tbl, $row[self::primary_key], $level, $i + 1);
				$return = array_merge($return, $subissues);
			}
		}

		return $return;
	}

	// выбрать потомков 1 уровня
	static function children($DB, $tbl, $id, $where = null, $sort = null, $limit = null){
		$WHERE = strlen($where) ? $where : '1';
		$ORDER = is_array($sort) ? ('ORDER BY ' . join($sort, ', ')) : '';
		$LIMIT = strlen($limit) ? "LIMIT $limit" : '';

		$query = sprintf("SELECT * FROM `%s` WHERE `%s` = '%d' AND %s %s %s", $tbl, self::parent_key, $id, $WHERE, $ORDER, $LIMIT);
		return $DB->sql2array($query);
	}

	// выбрать всех потомков
	static function children_all($DB, $tbl, $id, $where = null, $sort = null, $limit = null, $path = array()){
		$return = array();

		$WHERE = strlen($where) ? $where : '1';
		$ORDER = is_array($sort) ? ('ORDER BY ' . join($sort, ', ')) : '';
		$LIMIT = strlen($limit) ? "LIMIT $limit" : '';
		$query = sprintf("SELECT * FROM `%s` WHERE `%s` = '%d' AND %s %s %s", $tbl, self::parent_key, $id, $WHERE, $ORDER, $LIMIT);
		$result = $DB->sql2array($query);

		foreach($result as $row){
			$return[] = $row;
			if(count($path) == 0 || in_array($row[self::primary_key], $path)){
				$subissues = self::children_all($DB, $tbl, $row[self::primary_key], $where, $sort, $limit, $path);
				$return = array_merge($return, $subissues);
			}
		}

		return $return;
	}

	// выбрать всех соседей
	static function neighbours($DB, $tbl, $id, $where = null, $sort = null, $limit = null){
		$parent_id = self::parent_id($DB, $tbl, $id);
		return self::children($DB, $tbl, $parent_id, $where, $sort, $limit);
	}

	// все ID родителей от элемента до корня
	static function path($DB, $tbl, $id, $reverse = true){
		$parents = array();
		while(($id = (int)self::parent_id($DB, $tbl, $id)) > 0) $parents[] = $id;
		return $reverse ? array_reverse($parents) : $parents;
	}

	// ID родителя
	static function parent_id($DB, $tbl, $id){
		$query = sprintf("SELECT `%s` FROM `%s` WHERE `%s` = '%d'", self::parent_key, $tbl, self::primary_key, $id);
		return (int)$DB->sql2result($query);
	}

	// выбрать родителя
	static function parent($DB, $tbl, $id){
		$query = sprintf("SELECT * FROM `%s` WHERE `%s` = '%d'", $tbl, self::primary_key, $id);
		return ($result = $DB->query($query)) ? $DB->fetch_array($result) : null;
	}

	// выбрать всех родителей до корня
	static function parents_all($DB, $tbl, $id, $reverse = true){
		$parents = array();
		$parents[] = self::parent($DB, $tbl, $id);
		$parent_id = $parents[count($parents) - 1][self::parent_key];
		while($parent_id > 0){
			$parents[] = self::parent($DB, $tbl, $parent_id);
			$parent_id = $parents[count($parents) - 1][self::parent_key];
		}
		return $reverse ? array_reverse($parents) : $parents;
	}

	// переместить элемент по дереву (нельзя переносить в потомков!)
	static function move($DB, $tbl, $id, $dest){
		$children = array_merge((array)$id, self::sub_ids($DB, $tbl, $id));
		if(in_array($dest, $children) == false){
			$query	= sprintf("UPDATE `%s` SET `%s` = '%d' WHERE `%s` = '%d'", $tbl, self::parent_key, $dest, self::primary_key, $id);
			if($DB->query($query)){
				foreach($children as $child){
					$upd = sprintf("UPDATE `%s` AS t LEFT JOIN `%s` AS tmp ON t.`%s` = tmp.`%s`
									SET t.`%s` = IF(t.`%s` > 0, tmp.`%s` + 1, 0)
									WHERE t.`%s` = '%d'", $tbl, $tbl, self::parent_key, self::primary_key, self::level_key, self::parent_key, self::level_key, self::primary_key, $child);
					$DB->query($upd);
				}
			}
			return true;
		}
		return false;
	}

	// удалить элемент
	static function remove($DB, $tbl, $id){
		if(is_array($id))
			$query = sprintf("DELETE FROM `%s` WHERE `%s` IN '%d'", $tbl, self::primary_key, join($id, "', '"));
		elseif(intval($id) > 0)
			$query = sprintf("DELETE FROM `%s` WHERE `%s` = '%d'", $tbl, self::primary_key, $id);
		return isset($query) ? $DB->query($query) : null;
	}

	// удалить элемент и его потомков
	static function remove_all($DB, $tbl, $id){
		$subissues = array_merge(self::sub_ids($DB, $tbl, $id), (array)$id);
		foreach($subissues as $del) self::remove($DB, $tbl, $del);			
		return count($subissues);
	}
}

?>