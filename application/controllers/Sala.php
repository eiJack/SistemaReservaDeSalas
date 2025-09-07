<?php
defined('BASEPATH') OR exit('No direct script acess allowed');

class Sala extends CI_Controller {
/*
Validacao dos tipos de retornos nas validações (codigo de erro)
1- operação realizada no banco de dados com sucesso(insercao, alteracao, consulta ou exclusao)
2- conteudo passado nulo ou vazio
3- conteudo zerado
4- conteudo nao inteiro
5- conteudo nao é um texto
6- data em formato invalido
7- hora em formato invalido
99- parametros passados pelo front nao correspondem ao metodo
*/

//atributos privados da classe
private $codigo;
private $descricao;
private $andar;
private $capacidade;
private $estatus;

//getters dos atributos
public function getCodigo()
{
    return $this->codigo;
}

public function getDescricao()
{
    return $this->descricao;
}

public function getAndar()
{
    return $this->andar;
}

public function getCapacidade()
{
    return $this->capacidade;
}


public function getEstatus()
{
    return $this->estatus;
}

//setters dos atributos
public function setCodigo($codigoFront)
{
    $this->codigo = $codigoFront;
}


public function setDescricao($descricaoFront)
{
    $this->descricao = $descricaoFront;
}


public function setAndar($andarFront)
{
    $this->andar = $andarFront;
}


public function setCapacidade($capacidadeFront)
{
    $this->capacidade = $capacidadeFront;
}


public function setEstatus($estatusFront)
{
    $this->tipoUsuario = $estatusFront;
}


public function inserir(){
//atributos para controlar o status de nosso metodo
$erros = [ ];
$sucesso = [ ];

    try{
        $json = file_get_contents('php://input');
        $resultado = json_decode($json);
        $lista = ["codigo" => '0', "descricao" => '0', "andar" => '0', "capacidade" => '0'];

        if(verificarParam($resultado, $lista) != 1) {
            //validar vindos de forma correta do frontend(helper)
            $erros[] = ['Codigo' => 99, 'msg' => 'Campos inexistentes ou incorretos no FrontEnd.'];
        }else{
            //validar campos quanto ao tipo de dado e tamanho (helper)
            $retornoCodigo = validarDados($resultado->codigo, 'int', true);
            $retornoDescricao = validarDados($resultado->descricao,'string',true);
            $retornoAndar = validarDados($resultado->andar, 'int', true);
            $retornoCapacidade = validarDados ($resultado->capacidade, 'int', true);

            if($retornoCodigo['codigoHelper'] !=0){
                $erros[] = ['codigo' => $retornoCodigo['codigoHelper'], 
                            'campo' => 'Codigo', 
                            'msg' => $retornoCodigo['msg']];
            }

            if($retornoDescricao['codigoHelper'] !=0){
                $erros[] = ['codigo' => $retornoDescricao['codigoHelper'], 
                'campo' => 'Descricao', 
                'msg' => $retornoDescricao['msg']];
            }

            if($retornoAndar['codigoHelper'] !=0){
                $erros[] = ['codigo' => $retornoAndar['codigoHelper'],
                            'campo' => 'Andar',
                            'msg' => $retornoAndar['msg']];
            }

            if($retornoCapacidade['codigoHelper'] !=0){
                $erros[] = ['codigo' => $retornoCapacidade['codigoHelper'],
                'campo' => 'Capacidade',
                'msg' => $retornoCapacidade['msg']];
            }
            
            //se não encontrar erros 
            If(empty($erros)){
                $this->setCodigo($resultado->codigo);
                $this->setDescricao($resultado->descricao);
                $this->setAndar($resultado->andar);
                $this->setCapacidade($resultado->capacidade);

                $this->load->model('M_sala');
                $resBanco = $this->M_sala->inserir(
                    $this->getCodigo(),
                    $this->getDescricao(),
                    $this->getAndar(),
                    $this->getCapacidade()
                );

                if ($resBanco['codigo']==1) {
                    $sucesso = true;
                }else{
                    //captura erro do banco
                    $erros[] = [
                        'codigo' => $resBanco['codigo'],
                        'msg' => $resBanco['msg']
                    ];
                }
            }

        }

    } catch (Exception $e){
       $erros[] = ['codigo' => 0, 'msg' => 'Erro inesperado: ' . $e->getMessage()]; 
    }

    //monta retorno unico
    if ($sucesso == true) {
        $retorno = ['sucesso' => $sucesso, 'msg' => 'Sala cadastrada corretamente.'];
    } else {
        $retorno = ['sucesso' => $sucesso, 'erros' => $erros];
    }

    //transforma o array em json
    echo json_encode($retorno);
}

public function consultar() {
    $erros = [];
    $sucesso = false;

    try {
        $json = file_get_contents('php://input'); // CORRIGIDO
        $resultado = json_decode($json);
        $lista = [
            "codigo" => '0',
            "descricao" => '0',
            "andar" => '0',
            "capacidade" => '0'
        ];

        if (verificarParam($resultado, $lista) != 1) {
            $erros[] = [
                'codigo' => 99,
                'msg' => 'Campos inexistentes ou incorretos no FrontEnd.'
            ];
        } else {
            // Validações
            $retornoCodigo     = validarDadosConsulta($resultado->codigo, 'int');
            $retornoDescricao  = validarDadosConsulta($resultado->descricao, 'string');
            $retornoAndar      = validarDadosConsulta($resultado->andar, 'int');
            $retornoCapacidade = validarDadosConsulta($resultado->capacidade, 'int');

            if ($retornoCodigo['codigoHelper'] != 0) {
                $erros[] = [
                    'codigo' => $retornoCodigo['codigoHelper'],
                    'campo' => 'Código',
                    'msg' => $retornoCodigo['msg']
                ];
            }

            if ($retornoDescricao['codigoHelper'] != 0) {
                $erros[] = [
                    'codigo' => $retornoDescricao['codigoHelper'],
                    'campo' => 'Descrição',
                    'msg' => $retornoDescricao['msg']
                ];
            }

            if ($retornoAndar['codigoHelper'] != 0) {
                $erros[] = [
                    'codigo' => $retornoAndar['codigoHelper'],
                    'campo' => 'Andar',
                    'msg' => $retornoAndar['msg']
                ];
            }

            if ($retornoCapacidade['codigoHelper'] != 0) {
                $erros[] = [
                    'codigo' => $retornoCapacidade['codigoHelper'],
                    'campo' => 'Capacidade',
                    'msg' => $retornoCapacidade['msg']
                ];
            }

            // Se não houver erros
            if (empty($erros)) {
                $this->setCodigo($resultado->codigo);
                $this->setDescricao($resultado->descricao);
                $this->setAndar($resultado->andar);
                $this->setCapacidade($resultado->capacidade);

                $this->load->model('M_sala');
                $resBanco = $this->M_sala->consultar(
                    $this->getCodigo(),
                    $this->getDescricao(),
                    $this->getAndar(),
                    $this->getCapacidade()
                );

                if ($resBanco['codigo'] == 1) {
                    $sucesso = true;
                } else {
                    $erros[] = [
                        'codigo' => $resBanco['codigo'],
                        'msg' => $resBanco['msg']
                    ];
                }
            }
        }
    } catch (Exception $e) {
        $erros[] = [
            'codigo' => 0,
            'msg' => 'Erro inesperado: ' . $e->getMessage()
        ];
    }

    // Monta o retorno
    if ($sucesso==true) {
        $retorno = [
            'sucesso' => $sucesso,
            'codigo' => $resBanco['codigo'],
            'msg' => $resBanco['msg'],
            'dados' => $resBanco['dados']
        ];
    } else {
        $retorno = [
            'sucesso' => $sucesso,
            'erros' => $erros
        ];
    }

    echo json_encode($retorno);
}

}
?>