<?php

class Cliente {
	public static function getToken($user,$validade=0) {
		if (!$validade) {
			$validade = 1*3600*24;
		}

		$expires = date('YmdHis',time()+$validade);


		$token = md5($user->id.$expires).$user->id.$expires;
		$token = $token.md5($token);
		return $token;
	}
	public static function getUserByToken($token) {
		$id = (int)substr($token,32,-32);
		if (!$id) return null;
		

		$expires = substr($id,-14);
		$id = (int)substr($id, 0,-14);

		$agora = date('YmdHis');
		if ($agora > $expires) return null;
		

		$sum = substr($token,-32);
		$token = substr($token,0,32);

		if (md5($token.$id.$expires)!=$sum) return null;
		if (md5($id.$expires)!=$token) return null;
		

		$user = static::getUserById($id);		
		return $user;
	}

	public static function getUser() {
		$token = Request::getCookie('cliente_token',null);
		if (!$token) return null;
		$user = static::getUserByToken($token);
		if (!$user) return null;

		return $user;
	}

	public static function setUser($cliente) {
		if (!$cliente) return null;
		$token = static::getToken($cliente);
		if (!$token) return null;
		Request::setCookie('cliente_token',$token);
	}

	public static function getUserByEmail($email='') {
		$cliente = R::findOne('cliente','deleted=0 and email=? limit 1',array($email));
		if (!isset($cliente->id)) return null;
		return $cliente;
	}

	public static function getUserByCPF($cpf='') {
		if (!$cpf) return null;
		$cpf = preg_replace('/\D/','',$cpf);
		if (strlen($cpf)!=11) return null;

		$cpf = mascara_string("###.###.###-##",$cpf);
		$cliente = R::findOne('cliente','deleted=0 and cpf=? limit 1',array($cpf));

		if (!isset($cliente->id)) return null;
		return $cliente;
	}

	public static function login($email='',$senha='') {
		if ($email=='') throw new Exception("Informe o e-mail.");
		if (!PHPMailer::ValidateAddress($email)) throw new Exception("Informe o e-mail corretamente.");

		if ($senha=='') throw new Exception("Informe a senha.");

		$cliente = static::getUserByEmail($email);
		if (!$cliente) throw new Exception("Cadastro não encontrado.");

		if ((int)$cliente->ativo!=1) throw new Exception("Cadastro inativo.");

		if (hasher($senha,$cliente->senha)!=$cliente->senha) throw new Exception("Senha inválida.");

		return static::setUser($cliente);
	}

	public static function logout() {
		Request::setCookie('cliente_token',null);
	}

	public static function recuperarSenha($email) {
		if ($email=='') throw new Exception("Informe o e-mail.");
		if (!PHPMailer::ValidateAddress($email)) throw new Exception("Informe o e-mail corretamente.");

		$cliente = static::getUserByEmail($email);
		if (!$cliente) throw new Exception("Cadastro não encontrado.");

		Events::addListener('background',array('Cliente','sendEmailRecuperarSenha'),array($cliente));
	}

	public static function sendEmailRecuperarSenha($cliente) {
		try {
			$token = static::getToken($cliente);
			if (!$token) throw new Exception("Token não encontrado.");

			$h2o = new h2o(dirname(dirname(__FILE__)).'/_msg/email-recuperar-senha.html');
			$vars = array();
			$vars['base_url'] = Site::getBaseUrl();
			$vars['cliente'] = $cliente->export();
			$vars['token'] = $token;

			$body = $h2o->render($vars);

			$infoContato = R::findOne('infocontato');

			$mail = new PHPMailer(true);
			$mail->CharSet = "utf-8";
			$mail->Subject = "Colins Militar - Recuperação de Senha.";
			$mail->Sender = $infoContato->email;
			$mail->SetFrom($infoContato->email,"Colins Militar");
			$mail->AddReplyTo($infoContato->email,"Colins Militar");
			$mail->AddAddress($cliente->email,$cliente->nome);
			$mail->MsgHTML($body);


			$mail->Send();			
		} catch (Exception $e) {
			error_log($e->getMessage());
		}
	}

	public static function sendEmailCadastroAdmin($cliente) {
		try {			

			$h2o = new h2o(dirname(dirname(__FILE__)).'/_msg/email-novo-cadastro.html');
			$vars = array();
			$vars['base_url'] = Site::getBaseUrl();
			$vars['cliente'] = $cliente->export();
			

			$body = $h2o->render($vars);

			$infoContato = R::findOne('infocontato');

			$mail = new PHPMailer(true);
			$mail->CharSet = "utf-8";
			$mail->Subject = "Colins Militar - Novo cadastro recebido.";
			$mail->Sender = $infoContato->email;
			$mail->SetFrom($cliente->email, $cliente->nome);
			$mail->AddReplyTo($cliente->email, $clietne->nome);
			$mail->AddAddress($infoContato->email, "Colins Militar");
			$mail->MsgHTML($body);


			$mail->Send();			
		} catch (Exception $e) {
			error_log($e->getMessage());
		}
	}

	public static function sendEmailCadastroAtivado($cliente) {
		try {
			$token = static::getToken($cliente);
			if (!$token) throw new Exception("Token não encontrado.");

			$h2o = new h2o(dirname(dirname(__FILE__)).'/_msg/email-cadastro-ativado.html');
			$vars = array();
			$vars['base_url'] = Site::getBaseUrl();
			$vars['cliente'] = $cliente->export();
			$vars['token'] = $token;

			$body = $h2o->render($vars);

			$infoContato = R::findOne('infocontato');

			$mail = new PHPMailer(true);
			$mail->CharSet = "utf-8";
			$mail->Subject = "Colins Militar - Cadastro ativado.";
			$mail->Sender = $infoContato->email;
			$mail->SetFrom($infoContato->email,"Colins Militar");
			$mail->AddReplyTo($infoContato->email,"Colins Militar");
			$mail->AddAddress($cliente->email,$cliente->nome);
			$mail->MsgHTML($body);


			$mail->Send();			
		} catch (Exception $e) {
			error_log($e->getMessage());
		}
	}
}