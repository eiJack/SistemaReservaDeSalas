<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Horario extends CI_Controller {
    /*
    Validacao dos tipos de retornos nas validacoes (codigo de erro)
    1 - Operacao realizada no banco de dados com sucesso(insercao, alteracao, consulta ou exclusao)
    2 - Conteudo passado nulo ou vazio
    3 - Conteudo zerado
    4 - Conteudo não inteiro
    5 - Conteudo nao é um texto
    6 - Data em formato invalido
    7 - Hora em formato invalido
    12 - Na atualizacao, pelo menos um atributo deve ser passado
    13 - Hora final menor que a hora inicial
    14 - Data final menor que a data inicial
    99 - parametros passados do front nao correspondem ao metodo
    */

    //atributos privados da classe
    private $codigo;
    private $descricao;
    private $horaInicial;
    private $horaFinal;
    private $estatus;
    
    //getter dos atributos
    public function getCodigo() {return $this->codigo;}
    public function getDescricao() {return $this->descricao;}
    public function getHoraInicial() {return $this->horaInicial;}
    public function getHoraFinal() {return $this->horaFinal;}
    public function getEstatus() {return $this->estatus;}

    //setters dos atributos
    public function setCodigo($codigoFront){$this->codigo=$codigoFront;}
    public function setDescricao($descricaoFront){$this->descricao=$descricaoFront;}
    public function setHoraInicial($horaInicialFront){$this->horaInicial=$horaInicialFront;}
    public function setHoraFinal($horaFinalFront){$this->hotaFinal=$horaFinalFront;}
    public function setEstatus($estatusFront){$this->tipoUsuario=$estatusFront;}

    public function inserir(){
        //Atributos para controlar o status de nosso metodo
        $erros = [];
        $sucesso = false;

        try {
            $json = file_get_contents('php://input');
            $resultadp = json_decode($json);
            $lista = ["descricao"=>'0', "horaInicial"=>'0', "horaFinal" => '0'];

            if (verificarParam($resultado, $lista) != 1) {
                //Validar vindos de forma correta do frontend (helper)
                $erros[]=['codigo'=> 99, 'msg'=> 'Campos inexistentes ou incorretos no FrontEnd.'];
            }else{
                //Validar campos quanto ao tipo de dado e tamanho (helper)
                $retornoDescricao = validarDados($resultado->descricao, 'string', true);
                $retornoHoraInicial = validarDados($resultado->horaInicial, 'hora', true);
                $retornoHoraFinal = validarDados($resultado->horaFinal, 'hora', true);
                $retornoComparacaoHoras = compararDataHora($resultado->horaInicial, $resultado->horaFinal, 'hora');

                if (($retornoDescricao['codigoHelper'] != 0)) {
                    $erros[] = ['codigo' => $retornoDescricao['codigoHelper'],
                                'campo' => 'Descrição',
                                'msg' => $retornoDescricao['msg']];
                }
                if (($retornoHoraInicial['codigoHelper'] != 0)) {
                    $erros[] = ['codigo' => $retornoHoraInicial['codigoHelper'],
                                'campo' => 'Hora Inicial',
                                'msg' => $retornoHoraInicial['msg']];
                }
                if (($retornoHoraFinal['codigoHelper'] != 0)) {
                    $erros[] = ['codigo' => $retornoHoraFinal['codigoHelper'],
                                'campo' => 'Hora Final',
                                'msg' => $retornoHoraFinal['msg']];
                }
                if (($retornoComparacaoHoras['codigoHelper'] != 0)) {
                    $erros[] = ['codigo' => $retornoComparacaoHoras['codigoHelper'],
                                'campo' => 'Hora Inicial e hora final',
                                'msg' => $retornoComparacaoHoras['msg']];
                }

                //se nao encontrar erros
                if (empty($erros)) {
                    $this->setDescricao($resultado->descricao);
                    $this->setHoraInicial($resultado->horaInicial);
                    $this->setHoraFinal($resultado->getHoraFinal);

                    $this->load->model('M_horario');
                    $resBanco = $this->M_horario->inserir(
                        $this->getDescricao(),
                        $this->getHoraInicial(),
                        $this->getHoraFinal()
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

    


}