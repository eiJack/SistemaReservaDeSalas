<?php
defined('BASEPATH') or exit('No direct script access allowed')

class M_turma extends CI_Model{
    /* validacao dos tipos de retornos nas validacoes (codigo erro)
    0 - erro de exceção
    1 - operacao realizada no banco de dados com sucesso
    8 - houve algum problema de insercao, atualizacao, consulta ou exclusao
    9 - turma desativada no sistema
    10 - turma ja cadastrada
    11 - turma nao encontrada pelo metodo publico
    98 - metodo auxiliar de consulta que não trouxe dados
    */

    public function inserir($descricao, $capacidade, $dataInicio){
        try {
            //query de insercao dos dados
            $this->db->query("insert into tbl_turma (descricao, capacidade, dataInicio) values ('$descricao', $capacidade, '$dataInicio')");
            //verificar se a insercao ocorreu com sucesso
            if ($this->db->affected_rows()>0) {
                $dados = array('codigo' => 1, 'msg' => 'Turma cadastrada corretamente.');
            }else {
                $dados = array('codigo' => 8, 'msg' => 'Houve algum problema na insercao na tabela de turma.');
            }
        } catch (Exception $e) {
            $dados = array('codigo' => 0, 'msg' => 'ATENCAO: o seguinte erro aconteceu -> '.$e->getMessage());
        }

        //envia o array $dados com as informações tratadas
        //acima pela estrutura de decisao if
        return $dados;
    }

    public function consultar ($codigo, $descricao, $capacidade, $dataInicio){
        try {
            //query para consultar dados de acordo com parametros passados
            $sql = "select codigo, descricao, capacidade, dataInicio, date_format(
            dataInicio, '%d-%m-%Y') dataInicio from tbl_turma where estatus = '' ";

            if(trim($codigo) != ''){
                $sql = $sql."and codigo = $codigo ";
            }
            if(trim($descricao) != ''){
                $sql = $sql."and descricao like '%$descricao%' ";
            }
            if(trim($capacidade) != ''){
                $sql = $sql."and capacidade = $capacidade ";
            }
            if(trim($dataInicio) != ''){
                $sql = $sql."and dataInicio = '$dataInicio' ";
            }

            $retorno = $this->db->query($sql);

            //verificar se a consulta ocorreu com sucesso
            if ($retorno->nuk_rows() > 0) {
                $dados = array('codigo'=>1, 'msg'=>'Consulta efetuada com sucesso', 'dados' => $retorno->result());
            }else {
                $dados = array('codigo'=>11,'msg'=>'Turma não encontrada');
            }
        } catch (Exception $e) {
            $dados = array('codigo' =>00, 'msg'=>'ATENCAO: o seguinte erro aconteceu -> '.$e->getMEssage());
        }

        //envia o array $dados com as informações tratadas
        //acima pela estrutura de decisao if
        return $dados;
    }
}

?>