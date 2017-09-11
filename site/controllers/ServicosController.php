<?php

namespace Controller;

use Lib\Config;
use Lib\View;

class ServicosController extends DefaultController {
	
	
	public static function IndexAction($req,$res) {

		$tpl = $req->controller.'/'.$req->method.'.twig';
		$view_dir = dirname(__DIR__).'/views';
		if (!file_exists($view_dir.'/'.$tpl)) {
			$tpl = 'home/index.twig';
		}

		$id = $req->params[0];

		$servicos = array(
			'atelier-de-negocios' => array(
				'titulo' => 'Atelier de Negócios',
				'texto' => '<p><strong>Assessoria a empreendedores e gestores no processo de aprimoramento do seu negócio e na tomada de decisões estratégicas:</strong></p><p>Identificação e desenvolvimento de oportunidades de melhoria do negócio, através da troca de experiências com os gestores, assessorando-os no planejamento de sua estratégia, na qualificação das suas decisões e no acompanhamento do seu plano de ação.<br /> Para atender às necessidades constatadas no decorrer do projeto, são também estruturados, sempre que necessário, programas específicos de treinamento. Centrados no desenvolvimento humano, incluem abordagens que contemplam a organização, as lideranças e a equipe.</p>',
				'metodologia' => array(
					'<p><strong>Fase 1 – Diagnóstico:</strong></p>',
					'<p>Reuniões para compreensão do estágio atual do negócio;</p>',
					'<p><em>Assessment</em> dos gestores e posterior feedback de alinhamento entre todos, para definição conjunta dos objetivos do negócio e melhor aproveitamento do potencial de cada um. </p>',
					'<p><strong>Fase 2 – Definição das Melhorias e Implantação:</strong></p>',
					'<p>Identificação dos processos críticos e desenho do projeto;</p>',
					'<p>Desenvolvimento e execução de programas de treinamento, quando necessário;</p>',
					'<p>Implantação e orientação quanto ao uso das ferramentas propostas;</p>',
					'<p>Acompanhamento através de reuniões periódicas preestabelecidas. </p>',
					'<p><strong>Fase 3 – Mensuração de Resultados:</strong></p>',
					'<p>Monitoramento do desempenho.</p>'
					),
				'imagens' => array(
						'static/images/image-servicos.jpg',
						'static/images/image-servicos.jpg',
						'static/images/image-servicos.jpg',
						'static/images/image-servicos.jpg'
					)
			),
			'atelier-de-carreiras' => array(
				'titulo' => 'Atelier de Carreira',
				'texto' => '<p><strong>Análise e suporte ao desenvolvimento de alternativas de trabalho:</strong></p>
					<p>Planejamento, direcionamento e suporte às pessoas em sua busca de realização profissional.</p>
					<p>Cada projeto é tratado com sigilo e pode ser desenvolvido para:</p>
					<ul><li>Pessoas em busca de um formato de negócio;</li>Pessoas em zona de desconforto com seus empregos atuais;<li>Pessoas que foram desligadas de empresas e estão à procura de alternativas para geração de receita e continuidade de carreira;</li><li>Pessoas recém-formadas que, além de uma carreira tradicional, desejam exercitar sua visão empreendedora;</li><li>Pessoas que estão se preparando para a aposentadoria, mas querem manter uma atividade profissional ativa e satisfatória.</li></ul>',
				'metodologia' => array(
					'<p>Encontros sistemáticos durante um período predeterminado com ênfase em:</p>',
					'<p>- Avaliação comportamental;</p>',
					'<p>- Identificação de competências;</p>',
					'<p>- Elaboração de cenários e objetivos;</p>',
					'<p>- Plano de ação;</p>',
					'<p>- Decisões estratégicas;</p>',
					'<p>- Educação financeira.</p>'
					),
				'imagens' => array(
						'static/images/image-servicos.jpg',
						'static/images/image-servicos.jpg',
						'static/images/image-servicos.jpg',
						'static/images/image-servicos.jpg'
					)
			),
			'mentoring' => array(
				'titulo' => 'Mentoring',
				'texto' => '<p><strong>Assessoria no desenvolvimento de projetos de carreira e de vida:</strong></p>
					<p>Suporte ao <strong>desenvolvimento profissional e pessoal</strong>, por meio da troca de experiência, de conhecimento e da adoção de estratégias pontuais, com o objetivo de <strong>superar desafios</strong>, alcançar <strong>novos patamares de desempenho</strong> e, através da carreira, <strong>viabilizar projetos de vida</strong>.</p>',
				'metodologia' => array(
					'<p>Encontros predefinidos, que compreendem:</p>',
					'<p>- Avaliação comportamental;</p>',
					'<p>- Entendimento dos objetivos;</p>',
					'<p>- Identificação de competências;</p>',
					'<p>- Análise do ambiente de trabalho;</p>',
					'<p>- Identificação de possibilidades;</p>',
					'<p>- Estratégia de ação.</p>',
					),
				'imagens' => array(
						'static/images/image-servicos.jpg',
						'static/images/image-servicos.jpg',
						'static/images/image-servicos.jpg',
						'static/images/image-servicos.jpg'
					)
			),
			'coaching' => array(
				'titulo' => 'Coaching',
				'texto' => '<p><strong>Assessoria no aprimoramento de habilidades para a conquista de metas preestabelecidas:</strong></p>
				<p>Elaboração de um <strong>plano de autodesenvolvimento</strong>, centrado no fortalecimento de habilidades para a conquista de metas específicas, com reflexos positivos sobre a atuação profissional e a realização pessoal.</p>',
				'metodologia' => array(
					'<p>- Avaliação comportamental;</p>',
					'<p>- Série programada de sessões, que envolve:',
					'<p>- Compreensão do atual contexto profissional e pessoal;</p>',
					'<p>- Identificação dos fatores que motivaram o interesse pelo autodesenvolvimento;</p>',
					'<p>- Definição das metas que devem ser alcançadas;</p>',
					'<p>- Desenvolvimento do plano de ação;</p>',
					'<p>- Mapeamento das habilidades que precisam ser fortalecidas;</p>',
					'<p>- Acompanhamento.</p>'
					),
				'imagens' => array(
						'static/images/image-servicos.jpg',
						'static/images/image-servicos.jpg',
						'static/images/image-servicos.jpg',
						'static/images/image-servicos.jpg'
					)
			)
		);

		$res->render($tpl,array(
				'req'=>$req,
				'servico' => $servicos[$id]
			)
		);
		


	}
	
}