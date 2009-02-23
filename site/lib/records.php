<?php

class User extends FAEntity implements FALinkable {

	public static function setTableDefinition(FATable $table) {
		
		$table->setTableName('user');
		
		$table->hasColumn('id', array(
			'type' => 'int',
			'primary' => TRUE,
			'autoIncrement' => TRUE,
		));
		$table->hasColumn('username', array(
			'type' => 'varchar',
			'size' => 40,
		));
		$table->hasColumn('password', array(
			'type' => 'char',
			'size' => 32,
		));
		$table->hasColumn('name', array(
			'type' => 'varchar',
			'size' => 60,
		));
	}
	
	public function getPath() {
	
		$path = new FAPath('users.view');
		
		return $path
			->id($this->id)
			->arg('id', $this->id);
	}

	public static function verify($credentials = array()) {
	
		$credentials = array_merge(array(
			'username' => NULL,
			'password' => NULL,
		), $credentials);
		
		$credentials['password'] = md5(SALT . $credentials['password']);
		
		$user = new User($credentials);
		
		return $user->find();
	}
}

class Article extends FAEntity implements FALinkable {

	public static function setTableDefinition(FATable $table) {
		
		$table->setTableName('article');
		
		$table->hasColumn('id', array(
			'type' => 'int',
			'primary' => TRUE,
			'autoIncrement' => TRUE,
		));
		$table->hasColumn('user_id', array(
			'type' => 'int',
		));
		$table->hasColumn('published', array(
			'type' => 'date',
		));
		$table->hasColumn('title', array(
			'type' => 'varchar',
			'size' => 120,
		));
		$table->hasColumn('body', array(
			'type' => 'text',
		));

		$table->hasOne('user', array(
			'local' => 'id',
			'foreign' => 'user_id',
			'class' => 'User',
			'prefetch' => TRUE,
		));
			
		$table->hasMany('comments', array(
			'class' => 'Comment',
			'local' => 'id',
			'foreign' => 'article_id',
		));
	}
	
	public function getTags() {
	
		$tags = array();
		
		foreach (array_map('trim', explode(',', $this->_tags)) as $tag) {
		
			$tags[] = new Tag(array('article_id' => $this->id, 'tag' => $tag));
		}
		
		return $tags;
	}
	
	public function postSave() {
	
		FAPersistence::getDatabase($this)->delete('article_tag')
			->where('article_id=?', $this->id)
			->execute();
		
		$insert = FAPersistence::getDatabase($this)->insert('article_tag')
			->column('article_id')
			->column('tag_id');
			
		$tags = array_map('trim', explode(',', $this->_tags));
		$tags = array_unique(array_filter($tags));

		foreach ($tags as $tag) {
			
			if ($tag) $insert->values(array($this->id, $tag));
		}
		
		$insert->execute();
	}
	
	public function getPath() {
	
		$path = new FAPath('.blog.view');
		
		return $path
			->arg('id', $this->id)
			->title(str_replace(' ', '-', $this->title))
			->year(date('Y', strtotime($this->published)))
			->month(date('m', strtotime($this->published)));
	}
	
	public static function prepareSelect(FAQuery $query) {
	
		return $query
			->column("GROUP_CONCAT(DISTINCT tag_id SEPARATOR ', ') as _tags")
				->leftJoin('article_tag at', 'Article.id=at.article_id')
			->column("COUNT(DISTINCT Comment.id) as num_comments")
				->leftJoin('comment Comment', 'Comment.article_id=Article.id')
			->groupBy('Article.id');
	}
}

class Comment extends FAEntity {

	public static function setTableDefinition(FATable $table) {
		
		$table->setTableName('comment');

		$table->hasColumn('id', array(
			'type' => 'int',
			'primary' => TRUE,
			'autoIncrement' => TRUE,
		));
		$table->hasColumn('user_id', array(
			'type' => 'int',
		));
		$table->hasColumn('article_id', array(
			'type' => 'int',
		));
		$table->hasColumn('parent_id', array(
			'type' => 'int',
		));
		$table->hasColumn('posted', array(
			'type' => 'date',
		));
		$table->hasColumn('body', array(
			'type' => 'text',
		));

		$table->hasOne('user', array(
			'local' => 'id',
			'foreign' => 'user_id',
			'class' => 'User',
			'prefetch' => TRUE,
		));
	
		$table->hasOne('article', array(
			'local' => 'id',
			'foreign' => 'article_id',
		));

	}
}

class Tag extends FAEntity implements FALinkable {

	public static function setTableDefinition(FATable $table) {
		
		$table->setTableName('article_tag');

		$table->hasColumn('article_id', array(
			'type' => 'int',
			'primary' => TRUE,
		));
		$table->hasColumn('tag', array(
			'column' => 'tag_id',
			'type' => 'varchar',
			'primary' => TRUE,
		));

		$table->hasOne('article', array(
			'local' => 'id',
			'foreign' => 'article_id',
			'class' => 'Article',
		));
	}
	
	public function getPath() {
	
		$path = new FAPath('tags.view');
		
		return $path
			->arg('tag', $this->tag)
			->tag($this->tag);
	}
}

?>