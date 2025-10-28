<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Professor extends CI_Controller {
    /*
    Validacao dos tipos de retornos nas validacoes (codigo de erro)
    1 - Operacao realizada no banco de dados com sucesso(insercao, alteracao, consulta ou exclusao)
    2 - Conteudo passado nulo ou vazio
    3 - Conteudo zerado
    4 - Conteudo não inteiro
    5 - Conteudo nao é um texto
    6 - Data em formato invalido
    12 - Na atualizacao, pelo menos um atributo deve ser passado
    15 - CPF com menos de 11 digitos
    16 - CPF com todos digitos iguais
    17 - CPF com digitos verificadores incorretos
    99 - parametros passados do front nao correspondem ao metodo
    */

    //atributos privados da classe
    private $codigo;
    private $nome;
    private $cpf;
    private $tipo;
    private $estatus;

    public function getCodigo() {return $this->codigo;}
    public function getNome() {return $this->nome;}
    public function getCpf() {return $this->cpf;}
    public function getTipo() {return $this->tipo;}
    public function getEstatus() {return $this->estatus;}

    public function setCodigo($codigoFront){$this->codigo=$codigoFront;}
    public function setNome($nomeFront){$this->nome=$nomeFront;}
    public function setCpf($cpfFront){$this->cpf=$cpfFront;}
    public function setTipo($tipoFront){$this->ctipo=$tipoFront;}
    public function setEstatus($estatusFront){$this->estatus=$estatusFront;}

    public function inserir(){
        //Atributos para controlar o status de nosso metodo
        $erros = [];
        $sucesso = false;

        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);
            $lista = ["nome"=>'0', "cpf"=>'0', "tipo" => '0'];

            if (verificarParam($resultado, $lista) != 1) {
                //Validar vindos de forma correta do frontend (helper)
                $erros[]=['codigo'=> 99, 'msg'=> 'Campos inexistentes ou incorretos no FrontEnd.'];
            }else{
                //Validar campos quanto ao tipo de dado e tamanho (helper)
                $retornoNome = validarDados($resultado->nome, 'string', true);
                $retornoCPF = validarDados($resultado->cpf, 'string', true);
                $retornoCPFNroValido = validarCPF($resultado->cpf);
                $retornoTipo = validarDados($resultado->tipo, 'string', true);

                if (($retornoNome['codigoHelper'] != 0)) {
                    $erros[] = ['codigo' => $retornoNome['codigoHelper'],
                                'campo' => 'Nome',
                                'msg' => $retornoNome['msg']];
                }
                if (($retornoCPF['codigoHelper'] != 0)) {
                    $erros[] = ['codigo' => $retornoCPF['codigoHelper'],
                                'campo' => 'CPF',
                                'msg' => $retornoCPF['msg']];
                }
                if (($retornoCPFNroValido['codigoHelper'] != 0)) {
                    $erros[] = ['codigo' => $retornoCPFNroValido['codigoHelper'],
                                'campo' => 'CPF validacao numero',
                                'msg' => $retornoCPFNroValido['msg']];
                }
                if (($retornoTipo['codigoHelper'] != 0)) {
                    $erros[] = ['codigo' => $retornoTipo['codigoHelper'],
                                'campo' => 'Tipo',
                                'msg' => $retornoTipo['msg']];
                }

                //se nao encontrar erros
                if (empty($erros)) {
                    $this->setNome($resultado->nome);
                    $this->setCpf($resultado->cpf);
                    $this->setTipo($resultado->tipo);

                    $this->load->model('M_professor');
                    $resBanco = $this->M_professor->inserir(
                        $this->getNome(),
                        $this->getCpf(),
                        $this->getTipo()
                    );

                    if ($resBanco['codigo']==1) {
                        $sucesso = true;
                    }else{
                        //captura erro do banco
                        $erros[] = [
                            'codigo'=>$resBanco['codigo'],
                            'msg'=>$resBanco['msg']
                        ];
                    }
                }
            }
        } catch (Exception $e) {
            $erros[] = ['codigo'=>0, 'msg'=>'Erro inesperado'.$e->getMessage()];
        }

        //monta retorno unico
        if ($sucesso == true) {
            $retorno = ['sucesso'=> $sucesso, 'codigo'=>$resBanco['codigo'], 'msg'=>$resBanco['msg']];
        }else{
            $retorno = ['sucesso'=> $sucesso, 'erros'=> $erros];
        }

        //transforma o array em json
        echo json_encode($retorno);
    }

    public function consultar(){
        //atributos para controlar o status de nosso método
        $erros = [];
        $sucesso = false;

        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);
            $lista = ["codigo"=>'0',"nome"=>'0',"cpf"=>'0',"tipo"=>'0'];

            if (verificarParam($resultado, $lista) !=1) {
                //validar vindos de forma correta do front(helper)
                $erros[]= ['codigo'=>99, 'msg'=>'Campos inexistentes ou incorretos no FrontEnd'];
                
            }else{
                //validar campos quanto ao tipo de dado e tamanho(helper)
                $retornoCodigo = validarDadosConsulta($resultado->codigo, 'int');
                $retornoNome = validarDadosConsulta($resultado->nome, 'string');
                $retornoCPF = validarDadosConsulta($resultado->cpf, 'string');
                $retornoTipo = validarDadosConsulta($resultado->tipo, 'string');

                $retornoComparacaoHoras = compararDataHora($resultado-> horaInicial, $resultado->horaFinal, 'hora');

                if ($retornoCodigo['codigoHelper'] != 0) {
                    $erros[] = ['codigo'=> $retornoCodigo['codigoHelper'],
                                'campo'=> 'Codigo',
                                'msg' => $retornoCodigo['msg']];
                }

                if ($retornoNome['codigoHelper'] != 0) {
                    $erros[] = ['codigo'=> $retornoNome['codigoHelper'],
                                'campo'=> 'Nome',
                                'msg' => $retornoNome['msg']];
                }

                if ($retornoCPF['codigoHelper'] != 0) {
                    $erros[] = ['codigo'=> $retornoCPF['codigoHelper'],
                                'campo'=> 'CPF',
                                'msg' => $retornoCPF['msg']];
                }

                if ($retornoTipo['codigoHelper'] != 0) {
                    $erros[] = ['codigo'=> $retornoTipo['codigoHelper'],
                                'campo'=> 'Tipo',
                                'msg' => $retornoTipo['msg']];
                }

                if ($resultado->cpf != '') {
                    //cpf informado verificar se é numero valido
                    $retornoCPFNroValido = validarCPF($resultado->cpf);
                    if ($retornoCPFNroValido['codigoHelper'] != 0) {
                        $erros[]=['codigo'=>$retornoCPFNroValido['codigoHelper'],
                                  'campo'=> 'CPF validacao numero',
                                  'msg'=> $retornoCPFNroValido['msg']]
                    }
                }

                //se não encontrar erros
                if (empty($erros)) {
                    $this->setCodigo($resultado->codigo);
                    $this->setNome($resultado->nome);
                    $this->setCpf($resultado->cpf);
                    $this->setTipo($resultado->tipo);

                    $this->load->model('M_professor');
                    $resBanco = $this->M_professor->consultar($this->getCodigo(),
                                                            $this->getNome(),
                                                            $this->getCpf(),
                                                            $this->getTipo());
                    if ($resBanco['codigo']==1) {
                        $sucesso = true;
                    }else{
                        //captura erro do banco
                        $erros[] = ['codigo'=>$resBanco['codigo'], 'msg'=> $resBanco['msg']];
                    }
                }
            }
        } catch (Exception $e) {
            $erros[] = ['codigo'=>0, 'msg'=>'Erro inesperado '.$e->getMessage()];
        }

        //monta um retorno unico
        if ($sucesso == true) {
            $retorno = ['sucesso'=> $sucesso, 'codigo'=>$resBanco['codigo'],
                        'msg'=>$resBanco['msg'], 'dados'=>$resBanco['dados']];
        }else {
            $retorno = ['sucesso'=> $sucesso, 'erros'=> $erros];
        }

        //transforma o array em JSON
        echo json_encode($retorno);
    }

    public function alterar(){
        //atribuido para controlar o status de nosso método
        $erros = [];
        $sucesso = false;

        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);
            $lista = ["codigo"=>'0',"nome"=>'0',"cpf"=>'0',"tipo"=>'0'];

            if (verificarParam($resultado, $lista) !=1) {
                //validar vindos de forma correta do front(helper)
                $erros[]= ['codigo'=>99, 'msg'=>'Campos inexistentes ou incorretos no FrontEnd'];
                
            }else{
                //pelo menos um dos tres parametros precisam ter dados para acontecer a atualizacao
                if (trim($resultado->nome)== '' && trim($resultado->cpf)=='' && trim($resultado->tipo)=='') {
                    $erros[] = ['codigo'=>12, 'msg'=> 'Pelo menos um parametro precisar ser passado para atualização'];
                }else{
                    //validar campos quanto ao tipo de dado e tamanho(helper)
                    $retornoCodigo = validarDados($resultado->codigo, 'int', true);
                    $retornoNome = validarDadosConsulta($resultado->nome, 'string');
                    $retornoCPF = validarDadosConsulta($resultado->cpf, 'string');
                    $retornoTipo = validarDadosConsulta($resultado->tipo, 'string');
                    
                    if ($retornoCodigo['codigoHelper'] != 0) {
                        $erros[] = ['codigo'=> $retornoCodigo['codigoHelper'],
                                    'campo'=> 'Codigo',
                                    'msg' => $retornoCodigo['msg']];
                        }

                    if ($retornoNome['codigoHelper'] != 0) {
                        $erros[] = ['codigo'=> $retornoNome['codigoHelper'],
                                    'campo'=> 'Nome',
                                    'msg' => $retornoNome['msg']];
                    }

                    if ($retornoCPF['codigoHelper'] != 0) {
                        $erros[] = ['codigo'=> $retornoCPF['codigoHelper'],
                                    'campo'=> 'CPF',
                                    'msg' => $retornoCPF['msg']];
                    }

                    if ($retornoTipo['codigoHelper'] != 0) {
                        $erros[] = ['codigo'=> $retornoTipo['codigoHelper'],
                                    'campo'=> 'Tipo',
                                    'msg' => $retornoTipo['msg']];
                    }

                    
                    if ($resultado->cpf != 0) {
                        //cpf informado verificar se é numero valido
                        $retornoCPFNroValido = validarCPF($resultado->cpf);
                        if ($retornoCPFNroValido['codigoHelper'] != 0) {
                            $erros[] = ['codigo'=> $retornoCPFNroValido['codigoHelper'],
                                    'campo'=> 'CPF validacao numero',
                                    'msg'=> $retornoCPFNroValido['msg']];
                        }
                        
                    }
                    //se não encontrar erros
                    if (empty($erros)) {
                        $this->setCodigo($resultado->codigo);
                        $this->setNome($resultado->nome);
                        $this->setCpf($resultado->cpf);
                        $this->setTipo($resultado->tipo);

                        $this->load->model('M_professor');
                        $resBanco = $this->M_professor->alterar($this->getCodigo(),
                                                                $this->getNome(),
                                                                $this->getCpf(),
                                                                $this->getTipo());
                        if ($resBanco['codigo']==1) {
                            $sucesso = true;
                        }else{
                            //captura erro do banco
                            $erros[] = ['codigo'=>$resBanco['codigo'], 'msg'=> $resBanco['msg']];
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $erros[] = ['codigo'=>0, 'msg'=>'Erro inesperado: '.$e->getMessage()];
        }

        // Monta retorno unico
        if ($sucesso == true) {
            $retorno = ['sucesso' => $sucesso, 'codigo'=> $resBanco['codigo'], 'msg'=> $resBanco['msg']];
        } else {
            $retorno = ['sucesso'=> $sucesso, 'erros'=>$erros];
        }

        //transforma o array em JSON
        echo json_encode($retorno);
    }

    public function desativar(){
        //atribuido para controlar o status de nosso método
        $erros = [];
        $sucesso = false;

        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);
            $lista = ["codigo"=>'0'];

            if (verificarParam($resultado, $lista) !=1) {
                //validar vindos de forma correta do front(helper)
                $erros[]= ['codigo'=>99, 'msg'=>'Campos inexistentes ou incorretos no FrontEnd'];
                
            }else{
                //validar codigo quanto ao tipo de dado e tamanho(helper)
                $retornoCodigo = validarDados($resultado->codigo, 'int', true);

                if ($retornoCodigo['codigoHelper'] != 0) {
                    $erros[] = [
                        'codigo' => $retornoCodigo['codigoHelper'],
                        'campo' => 'Codigo',
                        'msg' => $retornoCodigo['msg']
                    ];
                }

                //se não encontrar erros
                if (empty($erros)) {
                    $this->setCodigo($resultado->codigo);
                    $this->load->model('M_professor');
                    $resBanco = $this->M_professor->desativar($this->getCodigo());

                    if ($resBanco['codigo']==1){
                        $sucesso = true;
                    }else{
                        //captura erro do banco
                        $erros[] = [
                            'codigo'=> $resBanco['codigo'],
                            'msg'=> $resBanco['msg']
                        ];
                    }
                }
            }
        } catch(Exception $e){
            $erros[] = ['codigo'=> 0, 'msg'=> 'Erro inesperado: '.$e->getMessage()];
        }

        //monta retorno unico
        if ($sucesso == true) {
            $retorno = [
                'sucesso' => $sucesso, 'codigo'=> $resBanco['codigo'], 'msg'=> $resBanco['msg']
            ];
        }else {
            $retorno = ['sucesso'=> $sucesso, 'erros'=> $erros];
        }

        //transforma o array em JSON
        echo json_encode($retorno);
    }
}
?>