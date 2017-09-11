<?php
	
	class MailerEnvio {
		public static $remetente = null;
		
		public static $destinatario = null;
		
		public static function getMailer() {
			$mail = new PHPMailer(true);
			$infoDestinatario = static::getDestinatario();
			$infoRementente = static::getRemetente();
			$mail->CharSet = 'utf-8';
			$mail->Mailer = 'smtp';
			$mail->SMTPAuth = 'true';
			$mail->Username = $infoRementente->usuario;
			$mail->Password = $infoRementente->senha;
			$mail->Port = $infoRementente->porta;
			$mail->Sender = $infoRementente->email;
			$mail->Host = $infoRementente->host;
			
			if ($infoRementente->seguranca=='Nenhuma') {
				$mail->SMTPSecure = '';
			}
			else {
				$mail->SMTPSecure = $infoRementente->seguranca;
			}
			return $mail;
		}
		
		public static function getRemetente() {
			if (static::$remetente!==null) return static::$remetente;
			
			static::$remetente = R::findOne('remetente');
			return static::$remetente;
		}
		
		public static function getDestinatario() {
			if (static::$destinatario!==null) return static::$destinatario;
			
			static::$destinatario = R::findOne('infocontato');
			return static::$destinatario;
		}
		
		public static function sendNovaMensagem($contato) {
			
			try {
				
				$mail = static::getMailer($mail);
				$infoRementente = static::getRemetente();
				$infoDestinatario = static::getDestinatario();
				$mail->Subject = "PEC 17 - Nova Mensagem para Aprovação";
				$mail->AddReplyTo($contato->email,$contato->nome);
				$mail->SetFrom($infoRementente->email,$infoRementente->nome);
				$mail->AddAddress($infoDestinatario->email,$infoDestinatario->nome);
				
				$h2o = new h2o(dirname(dirname(__FILE__)).'/_msg/email-nova-mensagem.html');
				$vars = array();
				$vars['base_url'] = Site::getBaseUrl();
				$vars['contato'] = $contato->export();
				
				
				$body = $h2o->render($vars);
				
				$mail->MsgHTML($body);
				$mail->Send();
				} catch (Exception $e) {
				error_log($e->getMessage());
			}
			
		}
		
		public static function sendEnviarMensagem($contato,$destino) {
			
			try {
				
				$mail = static::getMailer($mail);
				$infoRementente = static::getRemetente();
				$infoDestinatario = static::getDestinatario();
				
				$mail->Subject = "PEC 17 - Nova Mensagem";
				$mail->AddReplyTo($infoDestinatario->email,$infoDestinatario->nome);
				$mail->SetFrom($infoRementente->email,$infoRementente->nome);
				
				$mail->AddAddress($destino->email,$destino->nome);
				
				$h2o = new h2o(dirname(dirname(__FILE__)).'/_msg/email-mensagem.html');
				$vars = array();
				$vars['base_url'] = Site::getBaseUrl();
				$vars['contato'] = $contato->export();
				
				
				$body = $h2o->render($vars);
				
				$mail->MsgHTML($body);
				$mail->Send();
				} catch (Exception $e) {
				error_log($e->getMessage());
			}
			
		}
		
		public static function preparaEnvio($contato) {
			
			try {
				$contato->dataenvio = date('Y-m-d H:i:s');
				R::store($contato);
				
				static::sendEnviarMensagem($contato,$contato);
				
				$ListaEmails = R::find('listaemail',"email!='' and ativo=1 ORDER BY ordenamento DESC");
				
				foreach($ListaEmails as $ListaEmail) {
					if (PHPMailer::ValidateAddress($ListaEmail->email)){
						static::sendEnviarMensagem($contato,$ListaEmail);
					}
				}
				
				} catch (Exception $e) {
				error_log($e->getMessage());
			}
			
		}
		
		
		
	}										