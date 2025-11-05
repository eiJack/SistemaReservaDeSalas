<?php //pg105+
defined('BASEPATH') OR exit('No direct access allowed');

class Mapa extends CI_Controller {
    /*
    validação dos tipos de retornos nas validações (código de erro)
    1 - operações realizadas no banco de dados com sucesso
    2 - conteudo passado nulo ou vazio
    3 - conteudo zerado
    4 - conteudo não inteiro
    5 - conteudo não é um texto
    6 - data em formato invalido
    12 - na atualizacao, pelo menos um atributo deve ser passado
    99 - parametros passados do front nao correspondem ao metodo
    */

    //atributos privados da classe
    private $codigo;
    private $dataReserva;
    private $codigo_sala;
    private $codigo_horario;
    private $codigo_turma;
    private $codigo_professor;
    private $estatus;
    //atributos para mapeamento
    private $dataInicio;
    private $dataFim;
    private $diaSemana;

    //getters dos atributos
    public function getCodigo() {return $this->codigo;}
    public function getDataReserva() {return $this->dataReserva;}
    public function getCodigoSala() {return $this->codigo_sala;}
    public function getCodigoHorario() {return $this->codigo_horario;}
    public function getCodigoTurma() {return $this->codigo_turma;}
    public function getCodigoProfessor() {return $this->codigo_professor;}
    public function getEstatus() {return $this->estatus;}
    public function getDataInicio() {return $this->dataInicio;}
    public function getDataFim() {return $this->dataFim;}
    public function getDiaSemana() {return $this->diaSemana;}

    //setters dos atributos
    public function setCodigo($codigoFront){$this->codigo=$codigoFront;}
    public function setDataReserva($dataReservaFront){$this->dataReserva=$dataReservaFront;}
    public function setCodigoSala($codigo_salaFront){$this->codigo_sala=$codigo_salaFront;}
    public function setCodigoHorario($codigo_horarioFront){$this->codigo_horario=$codigo_horarioFront;}
    public function setCodigoTurma($codigo_turmaFront){$this->codigo_turma=$codigo_turmaFront;}
    public function setProfessor($professorFront){$this->codigo_professor=$codigo_professorFront;}
    public function setEstatus($estatusFront){$this->estatus=$estatusFront;}
    public function setDataInicio($dataInicioFront){$this->dataInicio=$dataInicioFront;}
    public function setDataFim($dataFimFront){$this->dataFim=$dataFimFront;}
    public function setDiaSemana($diaSemanaFront){$this->diaSemana=$diaSemanaFront;}
    
    public function inserir(){
        //Atributos para controlar o status de nosso metodo
        $erros = [];
        $sucesso = false;

        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);
            $lista = ["dataReserva" => '0', "codSala" => '0', "codHorario" => '0', "codTurma" => '0', "codProfessor" => '0'];

            if (verificarParam($resultado, $lista) != 1) {
                //Validar vindos de forma correta do frontend (helper)
                $erros[]=['codigo'=> 99, 'msg'=> 'Campos inexistentes ou incorretos no FrontEnd.'];
            }else{
                //Validar campos quanto ao tipo de dado e tamanho (helper)
                $retornoDataReserva = validarDados($resultado->dataReserva, 'date', true);
                $retornoCodSala = validarDados($resultado->codSala, 'int', true);
                $retornoCodHorario = validarDados($resultado->codHorario, 'int', true);
                $retornoCodTurma = validarDados($resultado->codTurma, 'int', true);
                $retornoCodProfessor = validarDados($resultado->codProfessor, 'int', true);

                if (($retornoDataReserva['codigoHelper'] != 0)) {
                    $erros[] = ['codigo' => $retornoDataReserva['codigoHelper'],
                                'campo' => 'Data da Reserva',
                                'msg' => $retornoDataReserva['msg']];
                }
                if (($retornoCodSala['codigoHelper'] != 0)) {
                    $erros[] = ['codigo' => $retornoCodSala['codigoHelper'],
                                'campo' => 'Codigo Sala',
                                'msg' => $retornoCodSala['msg']];
                }
                if (($retornoCodHorario['codigoHelper'] != 0)) {
                    $erros[] = ['codigo' => $retornoCodHorario['codigoHelper'],
                                'campo' => 'Codigo Horário',
                                'msg' => $retornoCodHorario['msg']];
                }
                if (($retornoCodTurma['codigoHelper'] != 0)) {
                    $erros[] = ['codigo' => $retornoCodTurma['codigoHelper'],
                                'campo' => 'Codigo da Turma',
                                'msg' => $retornoCodTurma['msg']];
                }
                if (($retornoCodProfessor['codigoHelper'] != 0)) {
                    $erros[] = ['codigo' => $retornoCodProfessor['codigoHelper'],
                                'campo' => 'Codigo do professor',
                                'msg' => $retornoCodProfessor['msg']];
                }

                //se nao encontrar erros
                if (empty($erros)) {
                    $this->setDataReserva($resultado->dataReserva);
                    $this->setCodigoSala($resultado->codSala);
                    $this->setCodigoHorario($resultado->codHorario);
                    $this->setCodigoTurma($resultado->codTurma);
                    $this->setProfessor($resultado->codProfessor);

                    $this->load->model('M_mapa');
                    $resBanco = $this->M_mapa->inserir(
                        $this->getDataReserva(),
                        $this->getCodigoSala(),
                        $this->getCodigoHorario(),
                        $this->getCodigoTurma(),
                        $this->getProfessor()
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
            $lista = ["codigo"=>'0',"dataReserva" => '0', "codSala" => '0', "codHorario" => '0', "codTurma" => '0', "codProfessor" => '0'];

            if (verificarParam($resultado, $lista) !=1) {
                //validar vindos de forma correta do front(helper)
                $erros[]= ['codigo'=>99, 'msg'=>'Campos inexistentes ou incorretos no FrontEnd'];
                
            }else{
                //validar campos quanto ao tipo de dado e tamanho(helper)
                $retornoCodigo = validarDadosConsulta($resultado->codigo, 'int');
                $retornoDataReserva = validarDadosConsulta($resultado->dataReserva, 'date');
                $retornoCodSala = validarDadosConsulta($resultado->codSala, 'int');
                $retornoCodHorario = validarDadosConsulta($resultado->codHorario, 'int');
                $retornoCodTurma = validarDadosConsulta($resultado->codTurma, 'int');
                $retornoCodProfessor = validarDadosConsulta($resultado->codProfessor, 'int');

                if ($retornoCodigo['codigoHelper'] != 0) {
                    $erros[] = ['codigo'=> $retornoCodigo['codigoHelper'],
                                'campo'=> 'Codigo',
                                'msg' => $retornoCodigo['msg']];
                }
                if (($retornoDataReserva['codigoHelper'] != 0)) {
                    $erros[] = ['codigo' => $retornoDataReserva['codigoHelper'],
                                'campo' => 'Data da Reserva',
                                'msg' => $retornoDataReserva['msg']];
                }
                if (($retornoCodSala['codigoHelper'] != 0)) {
                    $erros[] = ['codigo' => $retornoCodSala['codigoHelper'],
                                'campo' => 'Codigo Sala',
                                'msg' => $retornoCodSala['msg']];
                }
                if (($retornoCodHorario['codigoHelper'] != 0)) {
                    $erros[] = ['codigo' => $retornoCodHorario['codigoHelper'],
                                'campo' => 'Codigo Horário',
                                'msg' => $retornoCodHorario['msg']];
                }
                if (($retornoCodTurma['codigoHelper'] != 0)) {
                    $erros[] = ['codigo' => $retornoCodTurma['codigoHelper'],
                                'campo' => 'Codigo da Turma',
                                'msg' => $retornoCodTurma['msg']];
                }
                if (($retornoCodProfessor['codigoHelper'] != 0)) {
                    $erros[] = ['codigo' => $retornoCodProfessor['codigoHelper'],
                                'campo' => 'Codigo do professor',
                                'msg' => $retornoCodProfessor['msg']];
                }

                //se não encontrar erros
                if (empty($erros)) {
                    $this->setCodigo($resultado->codigo);
                    $this->setDataReserva($resultado->dataReserva);
                    $this->setCodigoSala($resultado->codSala);
                    $this->setCodigoHorario($resultado->codHorario);
                    $this->setCodigoTurma($resultado->codTurma);
                    $this->setProfessor($resultado->codProfessor);

                    $this->load->model('M_mapa');
                    $resBanco = $this->M_mapa->consultar($this->getCodigo(),
                                                        $this->getDataReserva(),
                                                        $this->getCodigoSala(),
                                                        $this->getCodigoHorario(),
                                                        $this->getCodigoTurma(),
                                                        $this->getProfessor());
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
            $lista = ["codigo"=>'0',"descricao"=>'0',"horaInicial"=>'0',"horaFinal"=>'0'];

            if (verificarParam($resultado, $lista) !=1) {
                //validar vindos de forma correta do front(helper)
                $erros[]= ['codigo'=>99, 'msg'=>'Campos inexistentes ou incorretos no FrontEnd'];
                
            }else{
                //pelo menos um dos tres parametros precisam ter dados para acontecer a atualizacao
                if (trim($resultado->dataReserva)== '' && trim($resultado->codSala)=='' && 
                    trim($resultado->codHorario)=='' && trim($resultado->codTurma)=='' && trim($resultado->codProfessor)=='') {
                    $erros[] = ['codigo'=>12, 'msg'=> 'Pelo menos um parametro precisar ser passado para atualização'];
                }else{
                    //validar campos quanto ao tipo de dado e tamanho(helper)
                    $retornoCodigo = validarDados($resultado->codigo, 'int', true);
                    $retornoDataReserva = validarDados($resultado->dataReserva, 'date');
                    $retornoCodSala = validarDados($resultado->codSala, 'int');
                    $retornoCodHorario = validarDados($resultado->codHorario, 'int');
                    $retornoCodTurma = validarDados($resultado->codTurma, 'int');
                    $retornoCodProfessor = validarDados($resultado->codProfessor, 'int');

                    $retornoComparacaoHoras = compararDataHora($resultado->horaInicial,$resultado->horaFinal, 'hora');
                    
                    if ($retornoCodigo['codigoHelper'] != 0) {
                        $erros[] = ['codigo'=> $retornoCodigo['codigoHelper'],
                                    'campo'=> 'Codigo',
                                    'msg' => $retornoCodigo['msg']];
                        }

                    if (($retornoDataReserva['codigoHelper'] != 0)) {
                    $erros[] = ['codigo' => $retornoDataReserva['codigoHelper'],
                                'campo' => 'Data da Reserva',
                                'msg' => $retornoDataReserva['msg']];
                    }
                    if (($retornoCodSala['codigoHelper'] != 0)) {
                        $erros[] = ['codigo' => $retornoCodSala['codigoHelper'],
                                    'campo' => 'Codigo Sala',
                                    'msg' => $retornoCodSala['msg']];
                    }
                    if (($retornoCodHorario['codigoHelper'] != 0)) {
                        $erros[] = ['codigo' => $retornoCodHorario['codigoHelper'],
                                    'campo' => 'Codigo Horário',
                                    'msg' => $retornoCodHorario['msg']];
                    }
                    if (($retornoCodTurma['codigoHelper'] != 0)) {
                        $erros[] = ['codigo' => $retornoCodTurma['codigoHelper'],
                                    'campo' => 'Codigo da Turma',
                                    'msg' => $retornoCodTurma['msg']];
                    }
                    if (($retornoCodProfessor['codigoHelper'] != 0)) {
                        $erros[] = ['codigo' => $retornoCodProfessor['codigoHelper'],
                                    'campo' => 'Codigo do professor',
                                    'msg' => $retornoCodProfessor['msg']];
                    }

                    //se não encontrar erros
                    if (empty($erros)) {
                        $this->setCodigo($resultado->codigo);
                        $this->setDataReserva($resultado->dataReserva);
                        $this->setCodigoSala($resultado->codSala);
                        $this->setCodigoHorario($resultado->codHorario);
                        $this->setCodigoTurma($resultado->codTurma);
                        $this->setProfessor($resultado->codProfessor);

                        $this->load->model('M_mapa');
                        $resBanco = $this->M_mapa->alterar($this->getCodigo(),
                                                                $this->getDataReserva(),
                                                                $this->getCodigoSala(),
                                                                $this->getCodigoHorario(),
                                                                $this->getCodigoTurma(),
                                                                $this->getProfessor());
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
                    $this->load->model('M_mapa');
                    $resBanco = $this->M_mapa->desativar($this->getCodigo());

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