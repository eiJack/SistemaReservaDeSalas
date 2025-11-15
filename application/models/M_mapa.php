<?php
defined('BASEPATH') or exit('No direct script access allowed');

include_once("M_sala.php");
include_once("M_horario.php");
include_once("M_turma.php");
include_once("M_professor.php");

class M_mapa extends CI_Model{
    /*
    Validação dos tipos de retornos nas validações (Código de erro)
    0 - Erro de exceção
    1 - Operação realizada no banco de dados com sucesso (Inserção, Alteração, Consulta ou Exclusão)
    7 - Reserva desativada no sistema
    8 - a data esta ocupada para esta sala
    9 - Houve algum problema de insercao, atualizacao, consulta ou exclusao
    10 - Reserva já cadastrada
    11 - Reserva não encontrado pelo método publico
    98 - Método auxiliar de consulta que não trouxe dados
    */

    public function inserir($dataReserva, $codSala, $codHorario, $codTurma, $codProfessor){
        try {
            $retornoConsultarReservaTotal = $this->consultarReservaTotal($dataReserva, $codSala, $codHorario);

            if ($retornoConsultarReservaTotal['codigo'] == 11 ||
                $retornoConsultarReservaTotal['codigo'] == 7) {

                $salaObj = new M_sala();
                $retornoConsultaSala = $salaObj->consultar($codSala, '', '','');

                if ($retornoConsultaSala['codigo'] == 1) {
                    $horarioObj = new M_horario();
                    $retornoConsultaHorario = $horarioObj->consultarHorario($codHorario,'','');

                    if ($retornoConsultaHorario['codigo'] == 10) {
                        $turmaObj = new M_turma();
                        $retornoConsultaTurma = $turmaObj->consultaTurmaCod($codTurma);

                        if ($retornoConsultaTurma['codigo'] == 10) {
                            $professorObj = new M_professor();
                            $retornoConsultaProfessor = $professorObj->consultar($codProfessor,'','','');

                            if ($retornoConsultaProfessor['codigo'] == 1) {
                                $this->db->query("INSERT INTO tbl_mapa
                                                    (datareserva, sala, codigo_horario, codigo_turma, codigo_professor)
                                                    VALUES ('$dataReserva', $codSala, $codHorario, $codTurma, $codProfessor)");
                                if ($this->db->affected_rows() > 0) {
                                    $dados = array('codigo' => 1, 'msg'=>'Agendamento cadastrado corretamente.');
                                }else {
                                    $dados = array(
                                        'codigo' => 9, 'msg' => 'Houve algum problema na insercao na tabela agendamento.'
                                    );
                                }
                            }else {
                                $dados = array(
                                    'codigo' => $retornoConsultaProfessor['codigo'], 'msg'=> $retornoConsultaProfessor['msg']
                                );
                            }
                        }else {
                            $dados =array(
                                'codigo'=> $retornoConsultaTurma['codigo'], 'msg' => $retornoConsultaTurma['msg']
                            )
                        }
                    }else {
                        $dados = array(
                            'codigo' => $retornoConsultaHorario['codigo'], 'msg'=> $retornoConsultaHorario['msg']
                        );
                    }
                }else {
                    $dados = array(
                        'codigo' => $retornoConsultaSala['codigo'], 'msg'=> $retornoConsultaSala['msg']
                    );
                }
            } else {
                $dados = array(
                    'codigo' => $retornoConsultarReservaTotal['codigo'],
                    'msg' => $retornoConsultarReservaTotal['msg']
                );
            }
        } catch (Exception $e) {
            $dados = array(
                'codigo' => 0,
                'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ' . $e->getMessage()
            );
        }

        //Envia o array $dados com as informações tratadas
        //acima pela estrutura de decisão if
        return $dados;
    }

    private function consultarReservaTotal($dataReserva, $codSala, $codHorario){
        try {
            $sql = "select * from tbl_horario where codigo= $codHorario";
            $retornoHorario = $this->db-> query($sql);

            if ($retornoHorario->num_rows() > 0) {
                $linhaHr = $retornoHorario->row();
                $horaInicial = $linhaHr->hora_ini;
                $horaFinal = $linhaHr->hora_fim;

                $sql = "select * from tbl_mapa m, tbl_horario h
                        where m.datareserva = '".$dataReserva."'
                        and m.sala = $codSala and m.codigo_horario = h.codigo
                        and (h.hora_fim >= '".$horaFinal."')
                        ";
                $retornoMapa = $this->db->query($sql);

                if ($retornoMapa->num_rows()>0) {
                    $linha = $retornoMapa->row();

                    if (trim($linha->estatus) == "D") {
                        $dados = array(
                            'codigo' => 7, 'msg' => 'Agendamento desativado no sistema'
                        );
                    }else {
                        $dados = array(
                            'codigo' => 8, 'msg' => 'A data de '.$dataReserva. ' esta ocupada para esta sala'
                        );
                    }
                }else {
                    $dados = array(
                        'codigo' => 11, 'msg' => 'Reserva nao encontrada'
                    );
                }
            }else {
                $dados = array(
                    'codigo'=> 11, 'msg' => 'Reserva nao encontrada'
                );
            }
        } catch (Exception $e) {
            $dados = array(
                'codigo' => 00, 'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> '.$e->getMessage()
            );
        }
        return $dados;
    }

    public function consultar($codigo, $dataReserva, $codSala, $codHorario, $codTurma, $codProfessor){
        try {
            //Query para consultar dados de acordo com parametros passados
            $sql = "select m.codigo, date_format(m.datareserva, '%d-%m-%Y') datareservabra,
                    datareserva, m.sala, s.descricao descsala, m.codigo_horario, h.descricao as deshorario,
                    m.codigo_turma, t.descricao descturma, m.codigo_professor, p.nome as nome_professor
                    from tbl_mapa m, tbl_professor p, tbl_horario h, tbl_turma t, tbl_sala s 
                    where m.estatus = '' and m.codigo_professor = p.codigo
                                         and m.codigo_horario   = h.codigo
                                         and m.codigo_turma     = t.codigo
                                         and m.sala             = s.codigo
                   ";

            if (trim($codigo ?? '') != '') {
                $sql .= " and m.codigo = $codigo";
            }

            if (trim($dataReserva ?? '') != '') {
                $sql .= " and m.datareserva = '".$dataReserva. "' ";
            }  

            if (trim($codSala ?? '') != '') {
                $sql .= " and m.sala = $codSala";
            }

            if (trim($codHorario ?? '') != '') {
                $sql .= " and m.codigo_horario = $codHorario";
            }

            if (trim($codTurma ?? '') != '') {
                $sql .= " and m.codigo_turma = $codTurma";
            }

            if (trim($codProfessor ?? '') != '') {
                $sql .= " and m.codigo_professor = $codProfessor";
            }


            $sql = $sql . " order by m.datareserva, h.hora_ini, m.codigo_horario, m.sala ";

            $retorno = $this->db->query($sql);

            //verificar se a consulta ocorreu com sucesso
            if ($retorno->num_rows() > 0) {
                $dados = array(
                    'codigo' => 1,
                    'msg' => 'Consulta efetuada com sucesso.',
                    'dados' => $retorno->result()
                );
            }else{
                $dados = array(
                    'codigo' => 11,
                    'msg' => 'Agendamento(s) não encontrado(s).'
                );
            }

        } catch (Exception $e) {
            $dados = array(
                'codigo' => 00,
                'msg' => 'ATENÇÃO: O seguinte erro aconteceu ->' . $e->getMessage()
            );
        }
        //envia o array $dados com as informaces tratadas
        //acima pela estrutura de decisao if
        return $dados;
    }

    public function alterar($codigo, $dataReserva, $codSala, $codHorario, $codTurma, $codProfessor){
        try {
            //verifico se a sala ja esta cadastrada
            $retornoConsulta = $this->consultar($codigo, "","","","","");

            if ($retornoConsulta['codigo'] == 1) {
                //inicio a query para atualizacao
                $query = "update tbl_mapa set ";

                //vamos comparar os itens
                if ($dataReserva !== '') {
                    $query .= "datareserva = '$dataReserva', ";
                }

                if ($codSala !== '') {
                    $salaObj = new M_sala();
                    $retornoConsultaSala = $salaObj->consultar($codSala,'', '', '');

                    if ($retornoConsultaSala['codigo'] == 1) {
                        $query .= "sala = $codSala, ";
                    }else {
                        $dados = array(
                            'codigo' => $retornoConsultaSala['codigo'],
                            'msg' => $retornoConsultaSala['msg']
                        );
                    }
                }

                if ($codHorario !== '') {
                    $horarioObj = new M_horario();
                    $retornoConsultaHorario = $horarioObj->consultarHorario($codHorario,'', '', '');

                    if ($retornoConsultaHorario['codigo'] == 1) {
                        $query .= "codigo_horario = $codHorario, ";
                    }else {
                        $dados = array(
                            'codigo' => $retornoConsultaHorario['codigo'],
                            'msg' => $retornoConsultaHorario['msg']
                        );
                    }
                }

                if ($codTurma !== '') {
                    $turmaObj = new M_turma();
                    $retornoConsultaTurma = $turmaObj->consultaTurmaCod($codTurma,'', '', '');

                    if ($retornoConsultaTurma['codigo'] == 1) {
                        $query .= "codigo_turma = $codTurma, ";
                    }else {
                        $dados = array(
                            'codigo' => $retornoConsultaTurma['codigo'],
                            'msg' => $retornoConsultaTurma['msg']
                        );
                    }
                }

                if ($codProfessor !== '') {
                    $professorObj = new M_professor();
                    $retornoConsultaProfessor = $professorObj->consultar($codProfessor,'', '', '');

                    if ($retornoConsultaProfessor['codigo'] == 1) {
                        $query .= "codigo_professor = $codProfessor, ";
                    }else {
                        $dados = array(
                            'codigo' => $retornoConsultaProfessor['codigo'],
                            'msg' => $retornoConsultaProfessor['msg']
                        );
                    }
                }

                //termino a concatenção da querry
                $queryFinal = rtrim($query, ", ") . " where codigo = $codigo";

                //Executo a query de atualizacao dos dados
                $this->db->query($queryFinal);

                //verificar se a atualizacao ocorreu com sucesso
                if ($this->db->affected_rows() > 0) {
                    $dados = array('codigo'=> 1, 'msg'=> 'Agendamento atualizado corretamente.');
                } else {
                    $dados = array('codigo' => 9, 'msg' => 'Houve algum problema na atualizacao na tabela de agendamento.');
                }
            }else {
                $dados = array('codigo' => $retornoConsulta['codigo'],
                                'msg' => $retornoConsulta['msg']);
            }
        } catch (Exception $e) {
            $dados = array('codigo'=> 00, 'msg'=> 'ATENÇÃO: O seguinte erro aconteceu -> '.$e->getMessage());
        }

        //envia o array $dados com as informações tratadas
        //acima pela estrutura de decisao if
        return $dados;
    }

    public function desativar($codigo){
        
        
        try {
            //verifico se a professor ja esta cadastrada
            $retornoConsulta = $this->consultar($codigo, "","","","","");

            if ($retornoConsulta['codigo'] == 1){
                //query de atualizacao dos dados
                $this->db->query("update tbl_mapa set estatus = 'D'
                                  where codigo = $codigo");

                //verificar se a atualização ocorreu com sucesso
                if ($this->db->affected_rows() > 0) {
                    $dados = array('codigo'=> 1,
                                   'msg'=> 'Agendamento DESATIVADO corretamente.');
                }else{
                    $dados = array('codigo'=> 9,
                                    'msg'=> 'Houve algum problema na DESATIVAÇÃO do agendamento.');
                }
            } else {
                $dados = array('codigo'=> $retornoConsulta['codigo'],
                               'msg'=> $retornoConsulta['msg']);
            }
        } catch (Exception $e) {
            $dados = array('codigo'=> 00,
                           'msg'=> 'ATENÇÃO: O seguinte erro aconteceu -> '.$e->getMessage());
        }

        //envia o array $dados com as informações tratadas
        //acimda pela estrutura de decisão if
        return $dados;
    }
}
?>