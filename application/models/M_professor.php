<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_professor extends CI_Model{
    /*
    Validação dos tipos de retornos nas validações (Código de erro)
    0 - Erro de exceção
    1 - Operação realizada no banco de dados com sucesso (Inserção, Alteração, Consulta ou Exclusão)
    8 - Houve algum problema de inserção, atualização, consulta ou exclusão
    9 - Professor desativada no sistema
    10 - Professor já cadastrada
    11 - Professor não encontrado pelo método publico
    98 - Método auxiliar de consulta que não trouxe dados
    */

    public function inserir($nome, $cpf, $tipo){
        try {
            //Verifico se professor já está cadastrada
            $retornoConsulta = $this->consultaProfessorCpf($cpf);

            if ($retornoConsulta['codigo'] != 9 &&
                $retornoConsulta['codigo'] != 10) {

                //Query de inserção dos dados
                $this->db->query("insert into tbl_professor (nome, tipo, cpf)
                                  values ({$nome}, '{$tipo}', {$cpf})");

                //Verificar se a inserção ocorreu com sucesso
                if ($this->db->affected_rows() > 0) {
                    $dados = array(
                        'codigo' => 1,
                        'msg' => 'Professor cadastrada corretamente'
                    );
                } else {
                    $dados = array(
                        'codigo' => 8,
                        'msg' => 'Houve algum problema na inserção na tabela de professores.'
                    );
                }
            } else {
                $dados = array(
                    'codigo' => $retornoConsulta['codigo'],
                    'msg' => $retornoConsulta['msg']
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

    //Método privado, pois será auxiliar nesta classe
    private function consultaProfessorCpf($cpf){
        try {
            //Query para consultar dados de acordo com parâmetros passados
            $sql = "select * from tbl_professor where cpf = '$cpf' ";

            $retornoProfessor = $this->db->query($sql);

            //Verificar se a consulta ocorreu com sucesso
            if ($retornoProfessor->num_rows() > 0) {
                $linha = $retornoProfessor->row();
                if (trim($linha->estatus) == "D") {
                    $dados = array(
                        'codigo' => 9,
                        'msg' => 'Professor desativado no sistema, caso precise reativar a mesma, fale com o administrador.'
                    );
                } else {
                    $dados = array(
                        'codigo' => 10,
                        'msg' => 'Professor já cadastrado no sistema.'
                    );
                }
            } else {
                $dados = array(
                    'codigo' => 11,
                    'msg' => 'Professor não encontrado.'
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

    //Método privado, pois será auxiliar nesta classe
    private function consultaProfessorCod($codigo){
        try {
            //Query para consultar dados de acordo com parâmetros passados
            $sql = "select * from tbl_professor where codigo = '$codigo' and estatus = '' ";

            $retornoProfessor = $this->db->query($sql);

            //Verificar se a consulta ocorreu com sucesso
            if ($retornoProfessor->num_rows() > 0) {
                $dados = array('codigo' => 1, 'msg' => 'Consulta efetuada com sucesso');
            } else {
                $dados = array(
                    'codigo' => 11,
                    'msg' => 'Professor não encontrado.'
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

    public function consultar($codigo, $nome, $cpf, $tipo){
        try {
            //Query para consultar dados de acordo com parametros passados
            $sql = "select * from tbl_professor where estatus = '' ";

            if (trim($codigo) != '') {
                $sql = $sql . " and codigo = $codigo";
            }

            if (trim($cpf) != '') {
                $sql = $sql . " and cpf = '$cpf'";
            }

            if (trim($nome) != '') {
                $sql = $sql . " and nome like '%$nome%' ";
            }

            if (trim($tipo) != '') {
                $sql = $sql . " and tipo = '$tipo'";
            }

            $sql = $sql . " order by nome ";

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
                    'msg' => 'Professor não encontrada.'
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

    public function alterar($codigo, $nome, $cpf, $tipo){
        try {
            //verifico se a sala ja esta cadastrada
            $retornoConsulta = $this->consultaProfessorCod($codigo);

            if ($retornoConsulta['codigo'] == 10) {
                //inicio a query para atualizacao
                $query = "update tbl_professor set ";

                //vamos comparar os itens
                if ($nome !== '') {
                    $query .= "nome = '$nome', ";
                }

                if ($cpf !== '') {
                    $query .= "cpf = '$cpf', ";
                }

                if ($tipo !== '') {
                    $query .= "tipo = '$tipo', ";
                }

                //termino a concatenção da querry
                $queryFinal = rtrim($query, ", ") . " where codigo = $codigo";

                //Executo a query de atualizacao dos dados
                $this->db->query($queryFinal);

                //verificar se a atualizacao ocorreu com sucesso
                if ($this->db->affected_rows() > 0) {
                    $dados = array('codigo'=> 1, 'msg'=> 'Professor atualizado corretamente.');
                } else {
                    $dados = array('codigo' => 8, 'msg' => 'Houve algum problema na atualizacao na tabela de professor.');
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
            $retornoConsulta = $this->consultaProfessorCod($codigo);

            if ($retornoConsulta['codigo'] == 10){
                //query de atualizacao dos dados
                $this->db->query("update tbl_professor set estatus = 'D'
                                  where codigo = $codigo");

                //verificar se a atualização ocorreu com sucesso
                if ($this->db->affected_rows() > 0) {
                    $dados = array('codigo'=> 1,
                                   'msg'=> 'Professor DESATIVADO corretamente.');
                }else{
                    $dados = array('codigo'=> 8,
                                    'msg'=> 'Houve algum problema na DESATIVAÇÃO do professor.');
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