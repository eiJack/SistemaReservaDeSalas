<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Turma extends CI_Controller {
    /*validacao dos tipos de retornos nas validacoes (codigo de erro)
    1- operacao realizada no banco de dados com sucesso
    2- conteudo passado nulo ou vazio
    3- conteudo zerado
    4- conteudo não inteiro
    5- conteudo não é um texto
    6- data em formato invalido
    12- na atualizacao pelo menos um atributo deve ser passado
    99- parametros passados do front nao correspondem ao metodo
    */

    //atributo privados da classe
    private $codigo;
    private $descricao;
    private $capacidade;
    private $dataInicio;
    private $estatus;

    //getters dos atributos
    public function getCodigo(){
        return $this->codigo;
    }
    public function getDescricao(){
        return $this->descricao;
    }
    public function getCapacidade(){
        return $this->capacidade;
    }
    public function getDataInicio(){
        return $this->dataInicio;
    }
    public function getEstatus(){
        return $this->estatus;
    }


    //setters dos atributos
    public function setCodigo($codigoFront){
        $this->codigo = $codigoFront;
    }
    public function setDescricao($descricaoFront){
        $this->descricao = $descricaoFront;
    }
    public function setCapacidade($capacidadeFront){
        $this->capacidade = $capacidadeFront;
    }
    public function setDataInicio($dataInicioFront){
        $this->dataInicio = $dataInicioFront;
    }
    public function setEstatus($estatusFront){
        $this->tipoUsuario = $estatusFront;
    }

    public function inserir() {
        //atributos para controlar o status de nosso metodo
        $erros = [];
        $sucesso = false;

        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);
            $lista = ["descricao" => '0', "capacidade" => '0', "dataInicio" => '0'];

            if (verificarParam($resultado, $lista) != 1) {
                //validar vindos de forma correta do frontend(helper)
                $erros[]=['codigo'=>99, 'msg'=>'Campos inexistentes ou incorretos no FrontEnd.'];
            }else{
                //validar campos quanto ao tipo de dado e tamanho(helper)
                $retornoDescricao = validarDados($resultado->descricao, 'string', true);
                $retornoCapacidade = validarDados($resultado->capacidade, 'int', true);
                $retornoDataInicio = validarDados($resultado->dataInicio, 'date', true);

                if ($retornoDescricao['codigoHelper'] != 0) {
                    $erros[]=['codigo' => $retornoDescricao['codigoHelper'],
                              'campo' => 'Descricao',
                              'msg' => $retornoDescricao['msg']];
                }
                if ($retornoCapacidade['codigoHelper'] != 0) {
                    $erros[]=['codigo' => $retornoCapacidade['codigoHelper'],
                              'campo' => 'Capacidade',
                              'msg' => $retornoCapacidade['msg']];
                }
                if ($retornoDataInicio['codigoHelper'] != 0) {
                    $erros[]=['codigo' => $retornoDataInicio['codigoHelper'],
                              'campo' => 'data inicio',
                              'msg' => $retornoDataInicio['msg']];
                }

                //se não encontrar erros
                if (empty($erros)) {
                    $this->setDescricao($resultado->descricao);
                    $this->setCapacidade($resultado->capacidade);
                    $this->setDataInicio($resultado->dataInicio);

                    $this->load->model('M_turma');
                    $resBanco = $this->M_turma->inserir(
                        $this->getDescricao(),
                        $this->getCapacidade(),
                        $this->getDataInicio()
                    );

                    if ($resBanco['codigo']==1) {
                        $sucesso = true;
                    }else{
                        //captura erro do banco
                        $erros[] = ['codigo' => $resBanco['codigo'], 'msg'=>$resBanco['msg']];
                    }
                }
            }
        } catch (Exception $e) {
            $erros[] = ['codigo'=>0, 'msg'=>'Erro inesperado: '.$e->getMessage()];
        }

        //monta retorno unico
        if ($sucesso==true) {
            $retorno = ['sucesso'=>$sucesso, 'codigo'=>$resBanco['codigo'],'msg'=>$resBanco['msg']];
        }else{
            $retorno = ['sucesso'=>$sucesso, 'erros'=>$erros];
        }

        //transforma o array em JSON
        echo json_encode($retorno);
    }

    public function consultar(){
        $erros = [];
        $sucesso = false;

        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);
            $lista = ["codigo" => '0', "descricao"=> '0', "capacidade"=> '0', "dataInicio" => '0'];

            if (verificarParam($resultado,$lista) != 1) {
                //validar vindos de forma correta do frontend (helper)
                $erros[]=['codigo'=> 99, 'msg'=> 'Campos inexistentes ou incorretos no FrontEnd.'];
            }else {
                //validar campos quanto ao tipo de dado e tamanho(helper)
                $retornoCodigo = validarDadosConsulta($resultado->codigo, 'int');
                $retornoDescricao = validarDadosConsulta($resultado->descricao,'string');
                $retornoCapacidade = validarDadosConsulta($resultado->capacidade,'int');
                $retornoDataInicio = validarDadosConsulta($resultado->dataInicio, 'date');

                if ($retornoCodigo['codigoHelper'] !=0) {
                    $erros[]=['codigo'=>$retornoCodigo['codigoHelper'], 'campo'=>'Codigo', 'msg'=>$retornoCodigo['msg']];
                }
                if ($retornoDescricao['codigoHelper'] !=0) {
                    $erros[]=['codigo'=>$retornoDescricao['codigoHelper'], 'campo'=>'Descricao', 'msg'=>$retornoDescricao['msg']];
                }
                if ($retornoCapacidade['codigoHelper'] !=0) {
                    $erros[]=['codigo'=>$retornoCapacidade['codigoHelper'], 'campo'=>'Capacidade', 'msg'=>$retornoCapacidade['msg']];
                }
                if ($retornoDataInicio['codigoHelper'] !=0) {
                    $erros[]=['codigo'=>$retornoDataInicio['codigoHelper'], 'campo'=>'Andar', 'msg'=>$retornoDataInicio['msg']];
                }

                //se não encontra erros
                if (empty($erros)) {
                    $this->setCodigo($resultado->codigo);
                    $this->setDescricao($resultado->descricao);
                    $this->setCapacidade($resultado->capacidade);
                    $this->setDataInicio($resultado->dataInicio);

                    $this->load->model('M_turma');
                    $resBanco=$this->M_turma->consultar($this->getCodigo(),$this->getDescricao(),$this->getCapacidade(),$this->getDataInicio());

                    if ($resBanco['codigo']==1) {
                        $sucesso=true;
                    }else {
                        //captura erro do banco
                        $erros[]=['codigo'=> $resBanco['codigo'], 'msg'=> $resBanco['msg']];
                    }
                }
            }
        } catch (Exception $e) {
            $erros[] = ['codigo'=> 0, 'msg'=>'Erro inesperado: '.$e->getMessage()];
        }

        //monta retorno unico
        if($sucesso == true){
            $retorno = ['sucesso'=> $sucesso, 'codigo'=>$resBanco['codigo'], 'msg'=>$resBanco['msg'], 'dados'=> $resBanco['dados']];
        }else{
            $retorno = ['sucesso' => $sucesso, 'erros'=> $erros];
        }

        echo json_encode($retorno);
    }
}
?>