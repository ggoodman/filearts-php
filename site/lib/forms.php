<?php

class ArticleForm extends FAForm {

	public function setUp() {
		
		$this
			->action(path('articles.save'))
			->startSection("Article")
				->hidden('id')
				->hidden('user_id', array(
					'value' => FARequest::instance()->visitor->id,
				))
				->text('title', array(
					'title' => "The title of the article",
					'label' => "Title",
					'required' => true,
					'class' => 'fa-form-w50',
				))
				->text('_tags', array(
					'label' => "Tags",
					'hint' => "Enter tags separated by comma (',')",
					'class' => 'fa-form-w33 fa-form-nl',
				))
				->textarea('body', array(
					'label' => "Article body",
					'class' => 'fa-form-w75 fa-form-nl',
					'required' => true,
				))
			->startSection()
				->submit("Submit")
				->cancel("Cancel");
	}
}


class FALoginValidator extends FAFormValidator {

	protected $user;
	
	public function getUser() {
		
		return $this->user;
	}

	public function validateControl(FAFormControl $control, $values) {
	
		if ($control->getName() == 'password') {
		
			try {
		
				$this->user = User::verify($values);
			} catch (FANotFoundException $e) {
			
				$this->addError($control, "Invalid username/password");
			}
		}
	}
}

class LoginForm extends FAForm {

	protected $login;
	
	public function getUser() {
	
		return $this->login->getUser();
	}

	public function setUp() {
		
		$this
			->action('')
			->method('post')
			->startSection("Login")
				->text('username', array(
					'label' => "Username",
					'class' => 'fa-form-w25 fa-form-nl',
					'required' => TRUE,
				))
				->password('password', array(
					'label' => "Password",
					'class' => 'fa-form-w25 fa-form-nl',
					'filter' => 'fa_hash',
					'required' => TRUE,
				))
				->checkbox('remember_me', array(
					'label' => "Remember me",
				))
			->startSection()
				->submit("Login");
			
		$this->login = new FALoginValidator;
			
		$this->addValidator($this->login);
	}
}

?>