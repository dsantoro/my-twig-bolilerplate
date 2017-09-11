<?php
	
	namespace Controller;
	
	use \R;
	use \Exception;
	use \stdclass;
	use \Lib\Util;
	use \Lib\Date;
	use \Lib\Config;
	
	class BlogController extends DefaultController {
		public static function IndexAction($req,$res) { 
			//R::debug(true);
			
			$tpl = $req->controller.'/'.$req->method.'.twig';
			$view_dir = dirname(__DIR__).'/views';
			if (!file_exists($view_dir.'/'.$tpl)) {
				$tpl = 'home/index.twig';
			}
			
			
			$params = $req->params;
			
			$req->pagination->rows = 9;
			
			$page = (int)$req->pagination->page;
			$rows = (int)$req->pagination->rows;
			
			if ($rows<1) $rows = 9;
			if ($page<1) $page = 1;
			
			$offset = ($page-1)*$rows;
			
			$joins = array();
			$where = array();
			$values = array();
			
			$sort = $req->Get('sort','');
			if ($sort != '') {
				list($sort,$order) = explode('_',$sort);
			}
			
			if ($sort == '') {
				$sort = 'data';
				$order = 'DESC';
			}
			
			$sort = 'post.'.$sort;
			
			$busca = $req->Get('busca','');
			
			$categoria_id = 0;
			if (preg_match('/\-(\d+)$/',array_shift($params),$m)) {
				$categoria_id = (int)$m[1];
			}
			
			if ($categoria_id!=0) {
				$joins[] = "INNER JOIN postcategoria ON postcategoria.deleted=0 and postcategoria.blogpost_id = post.id";                            
				$joins[] = "INNER JOIN blogcategoria as categoria ON categoria.deleted=0 and categoria.ativo=1 and categoria.id = postcategoria.blogcategoria_id";
				
				
				$where[] = "postcategoria.blogcategoria_id=?";
				$values[] = $categoria_id;
				} else if ($busca!='') {
				$joins[] = "LEFT JOIN postcategoria ON postcategoria.deleted=0 and postcategoria.blogpost_id = post.id";                            
				$joins[] = "LEFT JOIN blogcategoria as categoria ON categoria.deleted=0 and categoria.ativo=1 and categoria.id = postcategoria.blogcategoria_id";
			}
			
			if ($busca!='') {
				$keywords = explode(' ',$busca);
				foreach($keywords as $keyword) {
					$keyword = trim($keyword);
					if ($keyword=='') continue;
					$where[] = "(post.titulo like ? or post.texto like ? or categoria.titulo like ?)";
					$values[] = "%{$keyword}%";
					$values[] = "%{$keyword}%";
					$values[] = "%{$keyword}%";
				}
			}
			
			$ano = (int)$req->Get('ano',0);
			$mes = (int)$req->Get('mes',0);
			
			if ($ano) {
				$where[] = "year(post.data)=?";
				$values[] = $ano;
			}
			if ($mes) {
				$where[] = "month(post.data)=?";
				$values[] = $mes;
			}
			
			$where[] = "post.deleted=0";
			$where[] = "post.ativo=1";			
			
			$where = join(' and ',$where);
			$joins = join(' ',$joins);
			
			$query = "SELECT SQL_CALC_FOUND_ROWS post.* FROM blogpost as post {$joins} WHERE {$where} GROUP BY post.id ORDER BY {$sort} {$order}, post.ordenamento DESC LIMIT {$offset}, {$rows}";
			
			$posts = R::getAll($query,$values);
			
			$total = (int)R::getCell("SELECT FOUND_ROWS()");
			
			$pages = ceil($total/$rows);
			if ($page>$pages) $page = 1;
			
			$result = R::convertToBeans('blogpost',$posts);
			
			$posts = array();
			
			foreach($result as $Post) {
				$post = (object)$Post->export();
				$Imagem = $Post->getArquivo();
				$post->imagem = $Imagem->path;
				$post->resumo = Util::text_break($post->texto, 250);
				$posts[] = $post;
			}
			
			
			$vars = array();
			$vars['posts'] = $posts;
			$vars['req'] = $req;
			
			$vars['pages'] = $pages;
			$vars['page'] = $page;
			$vars['sort'] = $sort;
			$vars['order'] = $order;
			
			$res->render($tpl,$vars);
		}
		
		public static function InternaAction($req,$res) { 
			//R::debug(true);
			
			
			$tpl = $req->controller.'/'.$req->method.'.twig';
			$view_dir = dirname(__DIR__).'/views';
			if (!file_exists($view_dir.'/'.$tpl)) {
				$tpl = 'home/index.twig';
			}
			
			$params = $req->params;
			
			if (preg_match('/\-(\d+)$/',array_shift($params),$m)) {
				$post_id = (int)$m[1];
				$Post = R::findOne('blogpost','deleted=0 and ativo=1 and id=? limit 1',array($post_id));     
			}
			
			if (!isset($Post->id)) {
				$res->redirect("index.php/404");
				return;
			}
			
			
			$meta = new stdclass();
			$Meta = R::findOne('meta',"pagina=?",array('home'));
			$meta->title = $Post->titulo .' - '.$Meta->title;	
			
			$meta->description = Util::text_break($Post->texto,170);
			$meta->keywords = Util::text_break($Post->texto,170);
			
			$facebook = new stdclass();
			$facebook->title = $meta->title;
			$facebook->description = $meta->description;
			
			$Imagens = $Post->getArquivos();
			
			if (count($Imagens)>0) {
				$Imagem = array_shift($Imagens);
				array_unshift($Imagens, $Imagem);
				$facebook->image = $base_url."/_files/".$Imagem->path;
				$facebook->image_url = $base_url."/_files/".$Imagem->path;
			}
			
			$Videos = $Post->getVideos();
			
			$Arquivos = $Post->getArquivos('Arquivos');
			
			$vars = array();
			$vars['base_url'] = $req->base_url;
			$vars['post'] = $Post;
			$vars['imagens'] = $Imagens;
			$vars['videos'] = $Videos;
			$vars['arquivos'] = $Arquivos;
			$vars['meta'] = $meta;
			$vars['req'] = $req;
			$res->render($tpl,$vars);
		}
		
		
		
		public static function LoadCategorias($req,$res) { 
			//R::debug(true);
			
			$params = $req->params;
			
			$widget = array();
			
			$categorias = R::getAll("SELECT 
			categoria.* 
			
			FROM blogcategoria as categoria 
			
			INNER JOIN postcategoria ON postcategoria.blogcategoria_id = categoria.id and postcategoria.deleted=0
			INNER JOIN blogpost as post ON post.deleted=0 and post.ativo=1 and post.id = postcategoria.blogpost_id
			
			WHERE
			
			categoria.deleted=0
			and categoria.ativo=1
			
			GROUP BY categoria.id 
			
			ORDER BY categoria.ordenamento DESC
			");
			
			
			$categorias = R::convertToBeans('blogcategoria',$categorias);
			
			
			return $categorias;
			
		}
		
		public static function LoadArquivos($req,$res) {
			$application = Config::get('application');
			$month = $application['data']['month'];
			$datas = R::getAll("SELECT year(post.data) as ano, month(post.data) as mes from blogpost as post where post.deleted=0 and post.ativo=1 group by ano asc, mes asc");
			$anos = array();
			if ((bool)$datas) {
				foreach($datas as $data) {
					$ano = (int)$data['ano'];
					$mes = (int)$data['mes'];
					if (!isset($anos[$ano])) {
						$anos[$ano] = array();
					}
					$anos[$ano][$mes] = $month[$mes];
				}
			}
			return $anos;
			
		}
		
			public static function LoadPost($req,$res) { 
			//R::debug(true);
			
			$params = $req->params;
			
			
			$joins = array();
			$where = array();
			$values = array();
						
			$joins[] = "LEFT JOIN postcategoria ON postcategoria.deleted=0 and postcategoria.blogpost_id = post.id";                            
			$joins[] = "LEFT JOIN blogcategoria as categoria ON categoria.deleted=0 and categoria.ativo=1 and categoria.id = postcategoria.blogcategoria_id";
			

			$where[] = "post.deleted=0";
			$where[] = "post.ativo=1";			
			
			$where = join(' and ',$where);
			$joins = join(' ',$joins);
			
			$query = "SELECT SQL_CALC_FOUND_ROWS post.* FROM blogpost as post {$joins} WHERE {$where} GROUP BY post.id ORDER BY post.destaque DESC, post.data DESC, post.ordenamento DESC LIMIT 1";
			
			$posts = R::getAll($query,$values);
			
			$result = R::convertToBeans('blogpost',$posts);
			
			$posts = array();
			
			foreach($result as $Post) {
				$post = (object)$Post->export();
				$Imagem = $Post->getArquivo();
				$post->imagem = $Imagem->path;
				$post->resumo = Util::text_break($post->texto, 250);
				$posts[] = $post;
			}
			
			
			return $posts;
		}
		
		
	}																					