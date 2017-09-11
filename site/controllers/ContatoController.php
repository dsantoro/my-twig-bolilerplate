<?php
	
	namespace Controller;
	
	use \R;
	use \Exception;
	use \stdclass;
	use \Lib\Util;
	use \Lib\Mailer;
	use \Lib\View;
	
	class ContatoController extends DefaultController {
		
		public static function EnviarAction($req,$res) {
			$resp = new stdclass();
			try {
				
				if (!isset($_POST['nome']) || !isset($_POST['controle']) || !isset($_POST['email'])) throw new Exception("Post inválido.");
				$controle = $_POST['nome'];
				if ($controle!='') throw new Exception("Post inválido.");
				$_POST['nome'] = $_POST['controle'];
							
				$nome = $req->Post('nome','');
				if ($nome == '') throw new Exception("Informe o nome.");
				
				$email = $req->Post('email','');
				if ($email == '') throw new Exception("Informe o e-mail.");
				
				if (!Mailer::ValidateAddress($email)) throw new Exception("Informe o e-mail corretamente.");
				
				$telefone = preg_replace('/\D/','',$req->Post('telefone',''));
				if (strlen($telefone)<10) {
					$telefone = '';
					} else {
					$telefone = Util::mask("phone",$telefone);
				}
				
				$celular = preg_replace('/\D/','',$req->Post('celular',''));
				if (strlen($celular)<10) {
					$celular = '';
					} else {
					$celular = Util::mask("phone",$celular);
				}
								
				$mensagem = $req->Post('mensagem','');
				if ($mensagem == '') throw new Exception("Informe a mensagem.");
				
				$contato = R::dispense('contato');
				$contato->data = date('Y-m-d H:i:s');
				$contato->nome = $nome;
				$contato->email = $email;
				$contato->telefone = $telefone;
				$contato->celular = $celular;
				$contato->mensagem = $mensagem;
				
				if (!R::store($contato)) throw new Exception("Não foi possível enviar o formulário.");
				
				$res->on('background',array('\Controller\ContatoController','sendContato'),array($req,$res,$contato));
				
				
				$resp->msg = "Formulário enviado com sucesso.";
				$resp->success = true;
				} catch (Exception $e) {
				$resp->success = false;
				$resp->msg = $e->getMessage();
			}
			$app = \Lib\Config::get('app');
			if ((bool)$app->debug) {
				$resp->msg = $resp->msg . " | Modo Debug Ativado";
			}
			
			$res->json($resp);
		}
		
		public static function sendContato($req,$res,$contato) {
			try {
				$infoContato = R::findOne('infocontato');
				
				$app = \Lib\Config::get('app');
				
				$mail = new Mailer(true);
				$mail->CharSet = 'utf-8';
				$mail->Sender = $infoContato->email;
				$mail->Subject = "Novo contato recebido.";
				
				$mail->SetFrom($contato->email,$contato->nome);
				$mail->AddReplyTo($contato->email,$contato->nome);
				$mail->AddAddress($infoContato->email,$app->name);
				
				$vars = array();
				$vars['base_url'] = $req->base_url;
				$vars['req'] = $req;
				$vars['contato'] = $contato;
				$vars['appName'] = $app->name;
				
				$view = new View();
				$body = $view->render('contato/emails/contato.twig',$vars);
				
				$mail->MsgHTML($body);
				$mail->Send();
				} catch (Exception $e) {
					error_log($e->getMessage());
				}
		}
		
		public static function InscreverAction($req,$res) {
		$resp = new stdclass();
		try {

			$nome = $req->Post('nome','');
			//if ($nome == '') throw new Exception("Informe o nome.");

			$email = $req->Post('email','');
			if ($email == '') throw new Exception("Informe o e-mail.");

			if (!Mailer::ValidateAddress($email)) throw new Exception("Informe o e-mail corretamente.");

			
			$newsletter = R::findOne('newsletter','email=? limit 1',array($email));
			if (!isset($newsletter->id)) {
				$newsletter = R::dispense('newsletter');
				$newsletter->data = date('Y-m-d H:i:s');				
				$newsletter->email = $email;
			}
			$newsletter->nome = $nome;
			$newsletter->ativo = 1;

			if (!R::store($newsletter)) throw new Exception("Não foi possível enviar o formulário.");			
			
			
			$resp->msg = "Formulário enviado com sucesso.";
			$resp->success = true;
		} catch (Exception $e) {
			$resp->success = false;
			$resp->msg = $e->getMessage();
		}

		$res->json($resp);
	}

	public static function UploadAction($req,$res) {
		$resp = new stdclass();
		try {
			$resp->file = (object)$_FILES['arquivo'];
			if ($_FILES['arquivo']['error']!=0) throw new Exception("Não foi possível enviar o arquivo.");
			$resp->success = true;
		} catch (Exception $e) {
			$resp->success = false;
			$resp->msg = $e->getMessage();
		}

		$tmp_name = sys_get_temp_dir().'/'.uniqid();
		if (!@copy($resp->file->tmp_name,$tmp_name)) throw new Exception("Não foi possível copiar o arquivo.");
		$resp->file->tmp_name = $tmp_name;
		
		$res->json($resp);
	}

	public static function TrabalheAction($req,$res) {
		$resp = new stdclass();
		try {
		
			if (!isset($_POST['nome']) || !isset($_POST['controle']) || !isset($_POST['email'])) throw new Exception("Post inválido.");
			$controle = $_POST['nome'];
			if ($controle!='') throw new Exception("Post inválido.");
			$_POST['nome'] = $_POST['controle'];
				
			
			$nome = $req->Post('nome','');
			if ($nome == '') throw new Exception("Informe o nome.");

			$email = $req->Post('email','');
			if ($email == '') throw new Exception("Informe o e-mail.");
			if (!Mailer::ValidateAddress($email)) throw new Exception("Informe o e-mail corretamente.");

			$telefone = preg_replace('/\D/','',$req->Post('telefone',''));
			

			if (strlen($telefone) < 10) $telefone = '';
			

			if ($telefone != '') {
				$telefone = Util::mask('phone',$telefone);
			}
			
			$cpf = $req->Post('cpf','');
			if ($cpf == '') throw new Exception("Informe o cpf.");
			
			$cep = $req->Post('cep','');
			if ($cep == '') throw new Exception("Informe o cep.");
			
			$endereco = $req->Post('endereco','');
			
			$numero = $req->Post('numero','');
			
			$complemento = $req->Post('complemento','');
			
			$cidade = $req->Post('cidade','');
			
			$estado = $req->Post('estado','');
	
			$curriculo = $req->Post('curriculo',false);
			if (!$curriculo) throw new Exception("Selecione o currículo.");
			$curriculo = (object)$curriculo;

			$trabalhe = R::findOne('trabalhe','email=? limit 1',array($email));
			if (!isset($trabalhe)) {
				$trabalhe = R::dispense('trabalhe');				
			}
			$trabalhe->data = date('Y-m-d H:i:s');
			$trabalhe->nome = $nome;
			$trabalhe->cpf = $cpf;
			$trabalhe->cep = $cep;
			$trabalhe->email = $email;
			$trabalhe->telefone = $telefone;
			$trabalhe->endereco = $endereco;
			$trabalhe->numero = $numero;
			$trabalhe->complemento = $complemento;
			$trabalhe->cidade = $cidade;
			$trabalhe->estado = $estado;
			
			if (!R::store($trabalhe)) throw new Exception("Não foi possível enviar o formulário.");

			$ext = explode('.',$curriculo->name);
			$ext = end($ext);

			$arquivo = R::dispense("trabalhearquivo");
			$arquivo->trabalhe_id = $trabalhe->id;
			$arquivo->arquivo = uniqid().".".$ext;
			$arquivo->legenda = $curriculo->name;
			$arquivo->titulo = $curriculo->name;
			$arquivo->size = $curriculo->size;
			$arquivo->ext = $ext;
			$arquivo->path = "trabalhe/{$trabalhe->id}/{$arquivo->arquivo}";
			$arquivo->galeria = "curriculos";
			$arquivo->destaque = 0;

			if (!R::store($arquivo)) {
				throw new Exception("Não foi possível enviar o currículo.");	
			}			
			$folder = dirname(dirname(__DIR__)).'/_files/trabalhe/'.$trabalhe->id;
			
			if (!file_exists($folder)) {
				mkdir($folder,0777,true);					
			}
			if (!@copy($curriculo->tmp_name,$folder.'/'.$arquivo->arquivo)) {

			}
			
			$res->on('background',array('\Controller\ContatoController','sendEmailTrabalhe'),array($req,$res,$trabalhe,$arquivo,));

			$resp->success = true;
			$resp->msg = "Formulário enviado com sucesso.";
		} catch (Exception $e) {
			$resp->success = false;
			$resp->msg = $e->getMessage();
			$resp->line = $e->getLine();
		}
		$app = \Lib\Config::get('app');
			if ((bool)$app->debug) {
				$resp->msg = $resp->msg . " | Modo Debug Ativado";
			}
			
			$res->json($resp);
	}

	public static function sendEmailTrabalhe($req,$res,$trabalhe,$arquivo) {
		try {
			$infoContato = R::findOne('infocontato');
			
			$mail = new Mailer(true);
			$mail->CharSet = 'utf-8';
			$mail->Sender = $infoContato->email;
			$mail->Subject = "Novo currículo recebido.";
			
			$mail->SetFrom($trabalhe->email,$trabalhe->nome);
			$mail->AddReplyTo($trabalhe->email,$trabalhe->nome);
			$mail->AddAddress($infoContato->email,"Brasão do Pampa");

			$vars = array();
			$vars['base_url'] = $req->base_url;
			$vars['req'] = $req;
			$vars['trabalhe'] = $trabalhe;
			$vars['arquivo'] = $arquivo;		
			
			$view = new View();
			$body = $view->render('contato/emails/trabalhe.twig',$vars);
			

			$mail->MsgHTML($body);
			$mail->Send();
		} catch (Exception $e) {
			trigger_error($e->getMessage());
		}
	}
}					