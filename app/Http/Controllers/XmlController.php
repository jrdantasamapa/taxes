<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Http\Requests;
use App\Xml;
use App\Iten;
use App\Destinatario;
use App\Emitente;
use Input;
use Fpdf;
use App\Ncm;

class XmlController extends Controller
{
     public function __construct()
    {
        $this->middleware('auth');
    }
    public function index(){
    	$xmls = Xml::paginate(10);
    	$url = 'lista';
        return view('xml.index', compact('xmls', 'url'));
    }

    public function create(){
        $url = 'create';
        return view('xml.index', compact('url'));
    }

    public function inserte(Request $request){
		$input = Input::file('xml');
		$arquivo = $_FILES['xml']['name'];
		$extensao = substr($arquivo, -3);
		if(empty($input)){
			notify()->flash('Selecio um arquivo valido',
            'error',
            ['timer'=> 3000,
            'text'=> ''
            ]);
           return back()->withInput();
		}elseif($extensao == 'zip'){
			$nome = "xml.zip";
			$this->upload($input, $nome, $xml);
			return Redirect('xml');
		}elseif($extensao == 'xml'){
			$nome = "xml.xml";
			$this->upload($input, $nome);
			$xml = simplexml_load_file(public_path($nome));
			$this->salvaxml($xml);
			return Redirect('xml');
		}else{
			notify()->flash('Somente Arquivo zip ou xml',
            'error',
            ['timer'=> 3000,
            'text'=> ''
            ]);
           return back()->withInput();
		}
	//	$this->apagaarquivo($input, $nome);
	//	
    }

    public function upload($input, $nome){
    	if($input->move(public_path(), $nome)){
       		
    	}
    }

    public function apagaarquivo($input, $nome){
    	$arquivo = public_path($nome);
    	if(unlink($arquivo)){
    	}
    }

    public function salvaxml($xml)
    {
       		$versao = $xml->NFe->infNFe['versao'];
    		$infNFe = $xml->NFe->infNFe['Id'];
    		$ide = $xml->NFe->infNFe->ide;
    		$det = $xml->NFe->infNFe->det;
    		$dest = $xml->NFe->infNFe->dest->enderDest;
    		$CNPJ = $xml->NFe->infNFe->dest->CNPJ;
    		$xNome = $xml->NFe->infNFe->dest->xNome;
    		$indIEDest = $xml->NFe->infNFe->dest->indIEDest;
    		$IE = $xml->NFe->infNFe->dest->IE;
    		$emit = $xml->NFe->infNFe->emit->enderEmit;
    		$eCNPJ = $xml->NFe->infNFe->emit->CNPJ;
    		$exNome = $xml->NFe->infNFe->emit->xNome;
    		$CRT = $xml->NFe->infNFe->emit->CRT;
    		$eIE = $xml->NFe->infNFe->emit->IE;

			$dados = array('versao', $versao, 'infNFe', $infNFe);
			$dados = compact($dados);
			$dadosdest = array('CNPJ', $CNPJ, 'xNome', $xNome, 'indIEDest', $indIEDest, 'IE', $IE);
			$dadosdest = compact($dadosdest);
			$dadosemit = array('CNPJ', $eCNPJ, 'xNome', $exNome, 'CRT', $CRT, 'IE', $eIE);
			$dadosemit = compact($dadosemit);

			$ide = json_encode($ide);
			$ide = json_decode($ide,TRUE);
			$det = json_encode($det);
			$det = json_decode($det,TRUE);
			$dest = json_encode($dest);
			$dest = json_decode($dest,TRUE);
			$emit = json_encode($emit);
			$emit = json_decode($emit,TRUE);
			$dbxmls = array_merge($dados, $ide);
			$dbdestinos = array_merge($dadosdest, $dest);
			$dbemitentes = array_merge($dadosemit, $emit);
	
			$consultaxml = Xml::where('infNFe', $infNFe)->get();
			$x = sizeof($consultaxml);
				if($x == 0){
					if($xmls = Xml::create($dbxmls)){
						$idxml = $xmls->id;
						$consultadest = Destinatario::where('CNPJ', $CNPJ)->get();
						//$iddestino = $consultadest['id'];
						$d = sizeof($consultadest);
						if($d == 0){
							$destinos = Destinatario::create($dbdestinos);
							$iddestino = $destinos->id;
						}
						$consultaemit = Emitente::where('CNPJ', $eCNPJ)->get();
						//$idemitente = $consultaemit->id;
						$e = sizeof($consultaemit);
						if($e == 0){
							$emitentes = Emitente::create($dbemitentes);
							$idemitente = $emitentes->id;
						}
						$this->inserirItens($xml, $idxml);
   					}

						notify()->flash("Xml Importado com sucesso",
            			'success',
            			['timer'=> 3000,
            			'text'=> 'Arquivo Importado'
            			]);
					
				}else{
					notify()->flash('Xml ja Cadastrado',
            		'error',
            		['timer'=> 3000,
            		'text'=> ''
            		]);
				}
   }

    public function abrirzip(){
    	
    }

    public function inserirItens($xml, $idxml)
    {
		$contaitem = count($xml->NFe->infNFe->det);
		$det = $contaitem;

		for ($i = 0; $i < $det; $i++) {
		//Outros dados
			$idxml = $idxml;
			$nItem = $xml->NFe->infNFe->det[$i];
		// Dados dos Produtos
			$produto = $nItem->prod;
			$cProd = $produto->cProd;
			$xProd = $produto->xProd;
			$NCM = $produto->NCM;
			$CFOP = $produto->CFOP;
			$CFOP = $produto->CFOP;
			$qCom = $produto->qCom;
			$vUnCom = $produto->vUnCom;
			$vProd = $produto->vProd;
			$uTrib = $produto->uTrib;
			$qTrib = $produto->qTrib;
			$vUnTrib = $produto->vUnTrib;
			$indTot = $produto->indTot;

		// Dados do Imposto
			$imposto = $nItem->imposto;
			//ICMS
			$ICMS_ICMS40_orig = $imposto->ICMS->ICMS40->orig;
			$ICMS_ICMS40_CST = $imposto->ICMS->ICMS40->CST;
			$ICMS_ICMS40_vICMS = $imposto->ICMS->ICMS40->vICMS;
			$ICMS_ICMS40_motDesICMS = $imposto->ICMS->ICMS40->motDesICMS;
			//IPI
			$IPI_cEnq = $imposto->IPI->cEnq;
			$IPI_IPITrib_CST = $imposto->IPI->IPITrib->CST;
			$IPI_IPITrib_vBC = $imposto->IPI->IPITrib->vBC;
			$IPI_IPITrib_pIPI = $imposto->IPI->IPITrib->pIPI;
			$IPI_IPITrib_vIPI = $imposto->IPI->IPITrib->vIPI;
			//PIS
			dd($xml);
			$PIS_PISNT_CST = $xml->NFe->infNFe->det[$i]->imposto->PIS->PISNT->CST;
			$PIS_COFINS_COFINSNT_CST = $xml->NFe->infNFe->det[$i]->imposto->PIS->COFINS->COFINSNT->CST;
			$PIS_PISOutr_CST = $xml->NFe->infNFe->det[$i]->imposto->PIS->PISOutr->CST;
			$PIS_PISOutr_vBC = $xml->NFe->infNFe->det[$i]->imposto->PIS->PISOutr->vBC;
			$PIS_PISOutr_pPIS = $xml->NFe->infNFe->det[$i]->imposto->PIS->PISOutr->pPIS;
			$PIS_PISOutr_vPIS = $xml->NFe->infNFe->det[$i]->imposto->PIS->PISOutr->vPIS;
			$COFINS_COFINSOutr_CST = $xml->NFe->infNFe->det[$i]->imposto->COFINS->COFINSOutrg->CST;
			$COFINS_COFINSOutr_vBC = $xml->NFe->infNFe->det[$i]->imposto->COFINS->COFINSOutr->vBC;
			$COFINS_COFINSOutr_pCOFINS = $xml->NFe->infNFe->det[$i]->imposto->COFINS->COFINSOutr->pCOFINS;
			//Montando o Array
			$dadositem = array(
				'id_xml', $idxml,
				'nItem', $i,
				'cProd', $cProd, 
				'xProd', $xProd,
				'NCM', $NCM,
				'CFOP', $CFOP,
				'CFOP', $CFOP,
				'qCom', $qCom,
				'vUnCom', $vUnCom,
				'vProd', $vProd,
				'uTrib', $uTrib,
				'qTrib', $qTrib,
				'vUnTrib', $vUnTrib,
				'indTot', $indTot,
				'ICMS_ICMS40_orig',	$ICMS_ICMS40_orig,
				'ICMS_ICMS40_CST', $ICMS_ICMS40_CST,
				'ICMS_ICMS40_vICMS', $ICMS_ICMS40_vICMS,
				'ICMS_ICMS40_motDesICMS', $ICMS_ICMS40_motDesICMS,
				'IPI_cEnq', $IPI_cEnq,
				'IPI_IPITrib_CST', $IPI_IPITrib_CST,
				'IPI_IPITrib_vBC', $IPI_IPITrib_vBC,
				'IPI_IPITrib_pIPI', $IPI_IPITrib_pIPI,
				'IPI_IPITrib_vIPI', $IPI_IPITrib_vIPI,
				'PIS_PISNT_CST', $PIS_PISNT_CST,
				'PIS_COFINS_COFINSNT_CST', $PIS_COFINS_COFINSNT_CST,
				'PIS_PISOutr_CST', $PIS_PISOutr_CST,
				'PIS_PISOutr_vBC', $PIS_PISOutr_vBC,
				'PIS_PISOutr_pPIS', $PIS_PISOutr_pPIS,
				'PIS_PISOutr_vPIS', $PIS_PISOutr_vPIS,
				'COFINS_COFINSOutr_CST', $COFINS_COFINSOutr_CST,
				'COFINS_COFINSOutr_vBC', $COFINS_COFINSOutr_vBC,
				'COFINS_COFINSOutr_pCOFINS', $COFINS_COFINSOutr_pCOFINS);
			$dadositem = compact($dadositem);
			$dbitens = array_merge($dadositem);
			$itens = Iten::create($dbitens);

		}
	}

	public function consultaxml(){
		$url = 'consultaxml';
        return view('xml.index', compact('url'));
	}

	public function calcularxml(Request $request){
		$input = Input::file('xml');
		$nome = "calculoxml.xml";

		$this->upload($input, $nome);
		//MANIPULANDO O XML
		$xml = simplexml_load_file(public_path($nome));
		//Variaveis do XML
		$item = count($xml->NFe->infNFe->det);
		//Conta o numero de Ites do XML
		$tproduto = $xml -> NFe -> infNFe -> total -> ICMSTot -> vProd;
		//Valor total dos Prodtos do XML
		$tnfe = $xml -> NFe -> infNFe -> total -> ICMSTot -> vNF;
		// Valor Total da NFe
		$tICMS = $xml -> NFe -> infNFe -> total -> ICMSTot -> vICMS;
		// Valor total do ICMS da NFe
		$emitente = $xml -> NFe -> infNFe -> emit -> xNome;
		//Razão social do Emitente
		$fantasia = $xml -> NFe -> infNFe -> emit -> xFant;
		// Nome de Fantasia do Emitente
		$endereco = $xml -> NFe -> infNFe -> emit -> enderEmit -> xMun;
		// Minicipio do Enitente
		$destino = $xml -> NFe -> infNFe -> dest -> xNome;
		// Rasão social do Destino
		$bairrodestino = $xml -> NFe -> infNFe -> dest -> enderDest -> xBairro;
		$logdestino = $xml -> NFe -> infNFe -> dest -> enderDest -> xLgr;
		$enddest = $xml -> NFe -> infNFe -> dest -> enderDest -> xMun;
		// Municipio do Destino
		$chave = $xml -> protNFe -> infProt -> chNFe;
		// Chave de acesso do XML
		$nnfe = $xml -> NFe -> infNFe -> ide -> nNF;
		// Numero da NFe do XML
		$det = $item;
		$itens = 0;
		$tst = 0;
		$i = 0;
		// Versão do XML
		$versao = $xml -> NFe -> infNFe['versao'];
//verifica da de Emissão
		if ($versao >= 3) {
					$origem = $xml -> NFe -> infNFe -> det[$i] -> imposto -> ICMS -> ICMS00 -> orig;
					$demit = $xml -> NFe -> infNFe ->ide -> dhEmi;
				} else {
					$origem = $xml -> NFe -> infNFe -> det[$i] -> imposto -> ICMS -> ICMS40 -> orig;
					$demit = $xml -> NFe -> infNFe ->ide -> dEmi;
				}

//TÍTULO DO RELATÓRIO
$titulo = "RELATORIO DE VALORES ST POR NCM";
//LOGO QUE SERÁ COLOCADO NO RELATÓRIO
$imagem = "logost.jpg";
//NUMERO DE RESULTADOS POR PÁGINA
$por_pagina = 10;
//TIPO DO PDF GERADO
//F-> SALVA NO ENDEREÇO ESPECIFICADO NA VAR END_FINAL
$tipo_pdf = "D";
//CALCULA QUANTAS PÁGINAS VÃO SER NECESSÁRIAS
$registros = number_format(($item), 2, ',', '.');

$paginas = ceil($item / $por_pagina);
//PREPARA PARA GERAR O PDF

//INICIALIZA AS VARIÁVEIS
$linha_atual = 0;
$inicio = 0;
//PÁGINAS
/*for ($x = 1; $x <= $paginas; $x++) {
	//VERIFICA
	$inicio = $linha_atual;
	$fim = $linha_atual + $por_pagina;
	if ($fim > $paginas)
		$fim = $paginas;*/
	//iNICIO DO pdf
	Fpdf::AliasNbPages();
	Fpdf::AddPage();
	Fpdf::SetFont("times", "B", 10);
	//logomarca do Relatorio
	Fpdf::SetFillColor(232, 232, 232);
	Fpdf::SetTextColor(0, 0, 0);
	Fpdf::Image('logost.png', 10, 10, -150);
	Fpdf::Ln(5);
	// Move para a direita
	Fpdf::Cell(50);
	// Titulo dentro de uma caixa
	Fpdf::Cell(85, 10, $titulo, 0, 0, 'C');
	// Quebra de linha
	Fpdf::Ln(10);
	//Cabeçalho do relatorio
	Fpdf::SetFont('times', '', 8);
	Fpdf::Line(10, 25, 200, 25);
	Fpdf::Cell(100, 6, 'Cheve de Acesso: ' . $chave, 0, 0, 'L');
	Fpdf::Cell(30, 6, 'N. NFe: ' . $nnfe, 0, 0, 'L');
	Fpdf::Cell(60, 6, 'Data de Emissão: '.$demit, 0, 0, 'L');
	Fpdf::Ln(4);
	Fpdf::Cell(190, 6, 'Fornecedor: ' . $emitente . '   -   Fantasia.:' . $fantasia . '   -   End.:' . $endereco, 0, 0, 'L');
	Fpdf::Ln(4);
	Fpdf::Cell(190, 6, 'Destinatario: ' . $destino . '  -   End.:'.$logdestino.' - ' . $bairrodestino . '-' . $enddest, 0, 0, 'L');
	Fpdf::Line(10, 38, 200, 38);
	Fpdf::Ln(4);
		$tdebICMS  = 0;
		$tcreICMS = 0;
	//EXIBE OS REGISTROS
	for ($i = 0; $i < $det; $i++) {
		$suframa = $xml -> NFe -> infNFe -> det[$i] -> imposto -> ICMS -> ICMS40 -> vICMSDeson;
		$nome = $xml -> NFe -> infNFe -> det[$i] -> prod -> xProd;
		$codigo = $xml -> NFe -> infNFe -> det[$i] -> prod -> cProd;
		$vproduto = $xml -> NFe -> infNFe -> det[$i] -> prod -> vProd;
		$ncm = $xml -> NFe -> infNFe -> det[$i] -> prod -> NCM;
		$qtrib = $xml -> NFe -> infNFe -> det[$i] -> prod -> qTrib;
		$nnfe = $xml -> NFe -> infNFe -> ide -> nNF;
		$vdesconto = $suframa;
		$vliquido = $vproduto - $suframa;
		$newvliquido = number_format(floatval($vliquido), 3, ',', '.');
		$mva = Ncm::where('NCM', $ncm)->get();
		$dados = $mva;
		if ($dados > '0') {
			foreach ($dados as $dado) {
				$vmva = $dado->MVA;
				$subtrib = $dado->reducao;
				$aliint = $dado->al_interna;
			}
			if ($subtrib == '0.00') {
				$basered = $vliquido;
				$cmva = (($vmva / 100) * $basered);
				$basajst = ($basered + $cmva);
				$debICMA = (($aliint / 100)* $basajst);
				$creditoICMS = $suframa;
		
			} else {
				$valorreducao = (($subtrib/100) * $vliquido );
				$basered = $vliquido - $valorreducao;
				$cmva = (($vmva / 100) * $basered);
				$basajst = ($basered + $cmva);
				$debICMA = (($aliint / 100)* $basajst);
				$creditoICMS = ($suframa-($suframa * 0.4167));
			}
			if ($origem == 0 or $origem == 5) {
				$ICMS = ($vproduto * 0.12);
				$aliext = '12,00';
				$creditoICMS = ($basered * 0.12);
			} else {
				$ICMS = ($vproduto * 0.04);
				$aliext = '4,00';
				$creditoICMS = ($basered * 0.04);
			}
		} else {
			$vmva = 0;
			$ICMS = '0,00';
			$aliext = '0,00';
			$creditoICMS = '0,00';
			$basered = '0,00';
			$debICMA = '0,00';
			$subtrib = '0';
			$aliint = '0';
			$basajst = '0,00';
		}
		$st = ($debICMA - $creditoICMS);
		$tst += $st;
	
		$tdebICMS += $debICMA;
		$tcreICMS += $creditoICMS;
		$itens = $itens + 1;
		
		$newvproduto = number_format(floatval($vproduto), 3, ',', '.');
		$newdebICMA = number_format($debICMA, 3, ',', '.');
		$newtnfe = number_format(floatval($tnfe), 2, ',', '.');
		$newbasered = number_format($basered, 3, ',', '.');
		$newcreICMS = number_format($creditoICMS, 3, ',', '.');
		$newbasajst = number_format($basajst, 3, ',', '.');
		$newst = number_format($st, 3, ',', '.');
		$newsubtrib = number_format($subtrib, 2, ',', '.');
		$newaliint = number_format($aliint, 2, ',', '.');
		$newvmva = number_format($vmva, 2, ',', '.');
			

			//Layuot dos Regsitor
		Fpdf::SetFont('times', 'B', 8);
		Fpdf::Cell(190, 6, 'NCM: ' . $ncm . '  -  item n.: ' . $itens . '  -  Produto: ' . $nnfe . ' - ' . $nome, 0, 0, 'L');
		Fpdf::Ln(3);
		Fpdf::SetFont('times', '', 8);
		Fpdf::Cell(40, 6, 'Valor dos Produtos:', 0, 0, 'L');
		Fpdf::Cell(20, 6, $newvproduto, 0, 0, 'R');
		Fpdf::Cell(40, 6, 'Aliquota de Reducao da Base:', 0, 0, 'L');
		Fpdf::Cell(20, 6, $newsubtrib, 0, 0, 'R');
		Fpdf::Cell(40, 6, 'Valor do Debito de ICMS:', 0, 0, 'L');
		Fpdf::Cell(20, 6, $newdebICMA, 0, 0, 'R');
		Fpdf::Ln(3);
		Fpdf::Cell(40, 6, 'Descontos:', 0, 0, 'L');
		Fpdf::Cell(20, 6, $vdesconto, 0, 0, 'R');
		Fpdf::Cell(40, 6, 'Base Reduzida:', 0, 0, 'L');
		Fpdf::Cell(20, 6, $newbasered, 0, 0, 'R');
		Fpdf::Cell(40, 6, 'Valor do Credito de ICMS:', 0, 0, 'L');
		Fpdf::Cell(20, 6, $newcreICMS, 0, 0, 'R');
		Fpdf::Ln(3);
		Fpdf::Cell(40, 6, 'Valor Liquido:', 0, 0, 'L');
		Fpdf::Cell(20, 6, $newvliquido, 0, 0, 'R');
		Fpdf::Cell(40, 6, 'Aliquota de MVA:', 0, 0, 'L');
		Fpdf::Cell(20, 6, $newvmva, 0, 0, 'R');
		Fpdf::Ln(3);
		Fpdf::Cell(40, 6, 'Aliquota Interestadual:', 0, 0, 'L');
		Fpdf::Cell(20, 6, $aliext, 0, 0, 'R');
		Fpdf::Cell(40, 6, 'Base de Cal. Ajust. MVA:', 0, 0, 'L');
		Fpdf::Cell(20, 6, $newbasajst, 0, 0, 'R');
		Fpdf::Ln(3);
		Fpdf::Cell(40, 6, 'Beneficios de ICMS:', 0, 0, 'L');
		Fpdf::Cell(20, 6, $vdesconto, 0, 0, 'R');
		Fpdf::Cell(40, 6, 'Aliquota Interna:', 0, 0, 'L');
		Fpdf::Cell(20, 6, $newaliint, 0, 0, 'R');
		Fpdf::SetFont('times', 'B', 10);
		Fpdf::Cell(40, 6, 'Valor ICMS de ST :', 0, 0, 'L');
		Fpdf::Cell(20, 6, $newst, 0, 0, 'R');
		Fpdf::Ln(5);
		Fpdf::Cell(190, 0.2, '', 1, 1, 'C');
		Fpdf::Ln(1);
		Fpdf::SetFont('times', '', 8);
		$linha_atual++;
			
	//Imprime o N de paginas
	//Fpdf::SetFont('times', '', 6);
	//Fpdf::Cell(0, 10, "Pagina $x de $paginas", 0, 0, 'R');
	}
//};

		// Totalizadores
		Fpdf::SetFont('times', 'B', 12);
		Fpdf::Cell(190, 6, 'RESUMO TOTAIS', 0, 0, 'C');
		Fpdf::Ln(5);
		Fpdf::Cell(190, 0.2, '', 1, 1, 'C');
		Fpdf::SetFont('times', '', 8);
		Fpdf::Cell(20, 6, 'NFe', 0, 0, 'C');
		Fpdf::Cell(30, 6, 'Valor NFe', 0, 0, 'C');
		Fpdf::Cell(30, 6, 'Valor Produtos', 0, 0, 'C');
		Fpdf::Cell(30, 6, 'CREDITOS DE ICMS', 0, 0, 'C');
		Fpdf::Cell(30, 6, 'DEBITOS DE ICMS', 0, 0, 'C');
		Fpdf::Cell(30, 6, 'VALOR DO ST', 0, 0, 'C');
		Fpdf::Ln(5);
		Fpdf::Cell(190, 0.2, '', 1, 1, 'C');
		//Fpdf::Ln(1);
		Fpdf::SetFont('times', 'B', 12);
		Fpdf::Cell(20, 6, $nnfe, 0, 0, 'C');
		Fpdf::Cell(30, 6, (number_format(floatval($tnfe), 2, ',', '.')), 0, 0, 'C');
		Fpdf::Cell(30, 6, (number_format(floatval($tproduto), 2, ',', '.')), 0, 0, 'C');
		Fpdf::Cell(30, 6, (number_format($tcreICMS, 2, ',', '.')), 0, 0, 'C');
		Fpdf::Cell(30, 6, (number_format($tdebICMS, 2, ',', '.')), 0, 0, 'C');
		Fpdf::Cell(30, 6, (number_format(($tdebICMS - $tcreICMS), 2, ',', '.')), 0, 0, 'C');
		Fpdf::Output('CalculoST.NFe'.$nnfe.'.pdf', 'D');
		
		return Redirect('consultaxml');

}

	
}


