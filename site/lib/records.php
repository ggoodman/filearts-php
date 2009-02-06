<?php

class User extends FAEntity {

	protected $tableDef = array(
		'table' => 'user',
		'columns' => array(
			'id' => array(
				'type' => 'int',
				'primary' => TRUE,
				'autoIncrement' => TRUE,
			),
			'username' => array(
				'type' => 'varchar',
				'size' => 40,
			),
			'password' => array(
				'type' => 'char',
				'size' => 32,
			),
			'name' => array(
				'type' => 'varchar',
				'size' => 60,
			),
		),
	);

	public function verify($credentials = array()) {
	
		$credentials = array_merge(array(
			'username' => NULL,
			'password' => NULL,
		), $credentials);
		
		$matched = FAPersistence::getInstance()->findAll('User')
			->where('User.username=? AND User.password=?', array(
				$credentials['username'],
				md5(SALT . $credentials['password']),
			)
		);
		
		if ($matched->valid()) return $matched->current();
	}
}

class Article extends FAEntity {

	protected $tableDef = array(
		'table' => 'article',
		'columns' => array(
			'id' => array(
				'type' => 'int',
				'primary' => TRUE,
				'autoIncrement' => TRUE,
			),
			'user_id' => array(
				'type' => 'int',
			),
			'published' => array(
				'type' => 'date',
			),
			'title' => array(
				'type' => 'varchar',
				'size' => 120,
			),
			'body' => array(
				'type' => 'text',
			),
		),
		'hasOne' => array(
			'user' => array(
				'local' => 'id',
				'foreign' => 'user_id',
				'record' => 'User',
				'prefetch' => TRUE,
			),
		),
		'hasMany' => array(
			'comments' => array(
				'record' => 'Comment',
				'local' => 'id',
				'foreign' => 'article_id',
			),
		),
	);
	
	public function postSave() {
	
		FAPersistence::getDatabase()->delete('article_tag')
			->where('article_id=?', $this->id)
			->execute();
		
		$insert = FAPersistence::getDatabase()->insert('article_tag')
			->column('article_id')
			->column('tag_id');
			
		$tags = array_map('trim', explode(',', $this->tags));
		$tags = array_unique(array_filter($tags));

		foreach ($tags as $tag) {
			
			if ($tag) $insert->values(array($this->id, $tag));
		}
		
		$insert->execute();
	}
	
	public function prepareSelect(FAQuery $query) {
	
		return $query
			->column("GROUP_CONCAT(DISTINCT tag_id SEPARATOR ', ') as tags")
				->leftJoin('article_tag at', 'Article.id=at.article_id')
			->column("COUNT(DISTINCT Comment.id) as num_comments")
				->leftJoin('comment Comment', 'Comment.article_id=Article.id')
			->groupBy('Article.id');
	}
}

class Comment extends FAEntity {

	protected $tableDef = array(
		'table' => 'comment',
		'columns' => array(
			'id' => array(
				'type' => 'int',
				'primary' => TRUE,
				'autoIncrement' => TRUE,
			),
			'user_id' => array(
				'type' => 'int',
			),
			'article_id' => array(
				'type' => 'int',
			),
			'parent_id' => array(
				'type' => 'int',
			),
			'posted' => array(
				'type' => 'date',
			),
			'body' => array(
				'type' => 'text',
			),
		),
		'hasOne' => array(
			'user' => array(
				'local' => 'id',
				'foreign' => 'user_id',
				'record' => 'User',
				'prefetch' => TRUE,
			),
			'article' => array(
				'local' => 'id',
				'foreign' => 'article_id',
			),
		),
	);
}

?>