<?php
defined('BASEPATH') or exit('No drect script acecess allowed');

class M_horario extends CI_Model{
    /* 
    Validacao dos tipos de retornos nas validacoes (codigo de erro)
    0 - Erro de exceção
    1 - Operacao realizada no banco de dados com sucesso(insercao, alteracao, consulta ou exclusao)
    8 - Houve algum problema de insercao, atualizacao, consulta ou exclusao
    9 - Horario desativado no sistema
    10 - Horario ja cadastrado
    11 - Horario nao encontrado pelo metodo publico
    98 - Metodo auxiliar de consulta que nao trouxe dados
    */

    public function inserir($descricao, $horaInicial, $horaFinal){
        try {
            //verifico se o horario ja esta cadastrado
            $retornoConsulta = $this->consultarHorario('',$horaInicial, $horaFinal);

            if ($retornoConsulta['codigo'] != 9 && $retornoConsulta['codigo'] != 10) {
                //query de insercao dos dados
                $this->db->query("insert into tbl_horario (descricao, hora_ini, hora_fim) 
                                values ('$descricao','$horaInicial','$horaFinal')");
                
                //verificar se a insercao ocorreu com sucesso
                if ($this->db->affected_rows()>0) {
                    $dados = array('codigo'=> 1, 'msg'=> 'Horario cadastrado corretamente.');
                }else {
                    $dados = array('codigo'=> 8, 'msg'=> 'Houve algum problema na insercao na tabela horarios.');
                }
            }else {
                $dados = array('codigo'=> $retornoConsulta['codigo'], 
                               'msg'=> $retornoConsulta['msg']);
            }
        } catch (Exception $e) {
            $dados = array('codigo'=> 0, 'msg'=> 'ATENÇÃO: O seguinte erro aconteceu -> '.$e->getMessage());
        }

        //envia o array $dados com as informacoes tratadas
        //acima pela estrutura de decisao if
        return $dados;
    }

    //metodo privado, pois sera auxiliar nesta classe
    private function consultarHorario($codigo, $horaInicial, $horaFinal){
        try {
            //query para consultar dados de acordo com parametros passados
            if ($codigo != '') {
                $sql = "select * from tbl_horario where codigo = $codigo ";
            }else {
                $sql = "select * from tbl_horario where hora_ini = '$horaInicial' and hora_fim = '$horaFinal'";
            }
            $retornoHorario = $this->db->query($sql);

            //verificar se a consulta ocorreu com sucesso
            if ($retornoHorario->num_rows() > 0) {
                $linha = $retornoHorario->row();
                if (trim($linha->estatus) == "D") {
                    $dados = array(
                        'codigo' => 9,
                        'msg' => 'Horario desativado no sistema, caso precise reativar o mesmo, fale com o administrador.'
                    );
                }else{
                    $dados = array(
                        'codigo' => 10,
                        'msg' => 'Horario ja cadastado no sistema'
                    );
                }
            }else {
                $dados = array(
                    'codigo' => 98,
                    'msg' => 'Horario não encontrado.'
                );
            }

        } catch (Exception $e) {
            $dados = array(
                    'codigo' => 0,
                    'msg' => 'ATENÇÃO>: O seguinte erro aconteceu: '.$e->getMessage()
                );
        }

        //envia o array $dados com as informacoes tratadas
        //acima pela estrutura de decisao if
        return $dados;
    }

    public function consultar($codigo, $descricao, $horaFinal, $horaInicial){
        try {
            //query para consultar dados de acordo com parametros passados
            $sql = "select * from tbl_horario where estatus = '' ";

            if (trim($codigo) != '') {
                $sql = $sql." and codigo = $codigo ";
            }

            if (trim($descricao) != '') {
                $sql = $sql." and descricao like '%$descricao%' ";
            }

            if (trim($horaInicial) != '') {
                $sql = $sql." and hora_ini = '$horaInicial' ";
            }

            if (trim($horaFinal) != '') {
                $sql = $sql." and hora_fim = '$horaFinal' ";
            }

            $sql = $sql. " order by codigo";

            $retorno = $this->db->query($sql);

            //verificar se a consulta ocorreu com sucesso
            if ($retorno->num_rows()>0) {
                $dados = array(
                    'codigo' => 1,
                    'msg' => 'Consulta efetuada com sucesso',
                    'dados' => $retorno->result()
                );
            }else {
                $dados = array(
                    'codigo' => 11,
                    'msg' => 'Horario nao encontrado.'
                );
            }

        } catch (Exception $e) {
            $dados = array(
                'codigo' => 00,
                'msg' => 'ATENÇÃO: O seguinte erro aceonteceu -> '.$e->getMessage()
            );
        }
        //envia o array $dados com as informacoes tratadas
        //acima pela estrutura de decisao if
        return $dados;
    }

    public function alterar($codigo, $descricao, $horaInicial, $horaFinal){
        try {
            //verificar se o horario ja esta cadastrado
            $retornoConsulta = $this->consultar($codigo, '', '', '');

            if ($retornoConsulta['codigo'] == 1) {
                //inicio a query para atualizacao
                $query = "update tbl_horario set ";

                //vamos comparar os itens
                if ($descricao !== '') {
                    $query .= "descricao = '$descricao', ";
                }

                if ($horaInicial !== '') {
                    $query .= "hora_ini = '$horaInicial', ";
                }

                if ($horaFinal !== '') {
                    $query .= "hora_fim = '$horaFinal', ";
                }

                //termino a concatenção da query
                $queryFinal = rtrim($query, ", ") . " where codigo = $codigo";

                //Executo a query de atualização dos dados
                $this->db->query($queryFinal);

                //verificar se a atualizacao ocorreu com sucesso
                if ($this->db->affected_rows() > 0) {
                    $dados = array(
                        'codigo' => 1,
                        'msg' => 'Horario atualizado corretamente'
                    );
                }else {
                    $dados = array(
                        'codigo' => 8,
                        'msg' => 'Houve algum problema na atualizacao na tabela de horario'
                    );
                }
            }else {
                $dados = array(
                    'codigo' => $retornoConsulta['codigo'],
                    'msg' => $retornoConsulta['msg']
                );
            }

        } catch (Exception $e) {
            $dados = array(
                'codigo' => 00,
                'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> '. $e->getMessage()
            );
        }
        //envia o array $dados com as informações tratadas
        //acima pela estrutura de decisao if
        return $dados;
    }

    public function desativar($codigo){
        try {
            //verifico se o horario ja esta cadastrado
            $retornoConsulta = $this->consultarHorario($codigo, '', '');

            if ($retornoConsulta['codigo'] == 10) {
                //query de atualizacao dos dados
                $this->db->query("update tbl_horario set estatus = 'D' 
                                    where codigo = $codigo");
                
                //verificar se a atualizacao ocorreu com sucesso
                if ($this->db->affected_rows() > 0) {
                    $dados = array(
                        'codigo' => 1,
                        'msg' => 'Horario DESATIVADO corretamente.'
                    );
                }else {
                    $dados = array(
                        'codigo' => 8,
                        'msg' => 'Houve algum problema na DESATIVAÇÃO do horário'
                    );
                }
            }else {
                $dados = array('codigo'=> $retornoConsulta['codigo'],
                               'msg'=> $retornoConsulta['msg']);
            }

        } catch (Exception $e) {
            $dados = array(
                'codigo' => 00,
                'msg' => 'ATENÇÃO: O seguinte erro aconteceu ->'. $e->getMessage()
            );
        }
        //envia o array $dados com as informacoes tratadas
        //acima pela estrutura de decisão if
        return $dados;
    }
}
?>