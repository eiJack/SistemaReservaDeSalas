<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_usuario extends CI_Model{
    /* validacao dos tipos de retornos nas validacoes (codigo erro)
    0 - erro de exceção
    1 - operacao realizada no banco de dados com sucesso
    4 - usuario nao validado no sistema
    5 - usuario desativado no sistema
    6 - usuario nao cadastrado no sistema
    8 - houve algum problema de insercao, atualizacao, consulta ou exclusao
    9 - horario desativada no sistema
    10 - horario ja cadastrada
    11 - horario nao encontrada pelo metodo publico
    98 - metodo auxiliar de consulta que não trouxe dados
    */

    public function inserir($nome, $email, $usuario, $senha){
        try {
            $retornoUsuario = $this -> validaUsuario($usuario);

            if ($retornoUsuario['codigo'] == 4) {
                $this->db->query("insert into tbl_usuario (nome, email, usuario, senha)
                                  values ('$nome', '$email', '$usuario', md5('$senha'))");
                
                if ($this->db->affected_rows() > 0) {
                    $dados = array('codigo' => 1,
                                    'msg' => 'usuario cadastrado corretamente');
                }else {
                    $dados = array('codigo' => 8, 'msg' => 'houve algum problema na inserção na tabela de usuario.');
                }
            } else {
                $dados = array('codigo' => $retornoUsuario['codigo'], 'msg' => $retornoUsuario['msg']);
            }

        } catch (Exception $e) {
            $dados = array('codigo' => 00, 'msg' => 'ATENCAO: o seguinte erro aconteceu -> '.$e->getMessage());
        }

        //envia o array $dados com as informações tratadas
        //acima pela estrutura de decisao if
        return $dados;
    }

    public function consultar ($nome, $email, $usuario){
        //---------------------------------
        // Função que servira para trs tipos de consulta:
        // - Para todos os usuarios
        // - Para um determinado usuario
        // - Para nomes de usuario
        //---------------------------------
        
        try {
            //query para consultar dados de acordo com parametros passados
            $sql = " select id_usuario, nome, usuario, email 
                    from tbl_usuario where estatus = '' ";

            if(trim($nome) != ''){
                $sql = $sql."and nome like '%$nome%' ";
            }
            if(trim($email) != ''){
                $sql = $sql."and email = '$email' ";
            }
            if(trim($usuario) != ''){
                $sql = $sql."and usuario like '%$usuario%' ";
            }

            $retorno = $this->db->query($sql);

            //verificar se a consulta ocorreu com sucesso
            if ($retorno->num_rows() > 0) {
                $dados = array('codigo'=>1, 'msg'=>'Consulta efetuada com sucesso', 'dados' => $retorno->result());
            }else {
                $dados = array('codigo'=>6,'msg'=>'Dados não encontrada');
            }
        } catch (Exception $e) {
            $dados = array('codigo' =>00, 'msg'=>'ATENCAO: o seguinte erro aconteceu -> '.$e->getMEssage());
        }

        //envia o array $dados com as informações tratadas
        //acima pela estrutura de decisao if
        return $dados;
    }

    public function alterar($idUsuario, $nome, $email, $senha){
        try {
            $retornoUsuario = $this->validaIdUsuario($idUsuario);

            if ($retornoUsuario['codigo'] == 1) {
                $query = "update tbl_usuario set ";

                if ($nome !== '') {
                    $query .= "nome = '$nome', ";
                }
                if ($email !== '') {
                    $query .= "email = '$email', ";
                }
                if ($senha !== '') {
                    $query .= "senha = '$senha', ";
                }

                $queryFinal = rtrim($query, ", ") . "where id_usuario = $idUsuario";

                //excuta a query
                $this->db->query($queryFinal);

                // verifica se a atualizacao foi bem-sucedida
                if ($this->db->affected_rows() > 0) {
                    $dados = array('codigo' => 1, 'msg' => 'Usuario atualizada corretamente.');
                }else {
                    $dados = array('codigo' => 8, 'msg' => 'Houve algum problema na atualizacao na tabela de usuario');
                }
            }else {
                $dados = array('codigo' => $retornoUsuario['codigo'], 'msg' => $retornoUsuario['msg']);
            }
        } catch (Exception $e) {
            $dados = array('codigo' => 00, 'msg' => 'Atenção: o seguinte erro aconteceu -> '.$e->getMessage());
        }

        return $dados;
    }

    public function desativar($idUsuario){
        try {
            $retornoUsuario = $this->validaIdUsuario($idUsuario);

            if($retornoUsuario['codigo'] == 1){
                //query de atualizacao dos dados
                $this->db->query("update tbl_usuario set estatus = 'D' where id_usuario = $idUsuario");

                //verificar se a atualizacao ocorreu com sucesso
                if ($this->db->affected_rows() > 0) {
                    $dados = array('codigo' => 1, 'msg' => 'Usuario desativada corretamente');
                }else{
                    $dados = array('codigo' => 8, 'msg' => 'Houve algum problema na desativacao da usuario');
                }
            }else {
                $dados = array('codigo' => $retornoUsuario['codigo'], 'msg' => $retornoUsuario['msg']);
            }
        } catch (Exception $e) {
            $dados = array('codigo' => 00, 'msg' => 'Atenção: o seguinte erro aconteceu -> '.$e->getMessage());
        }

        return $dados;
    }

    private function validaUsuario($usuario){
        try {
            //atributo retorno recebe o resultado do select
            //sem status pois teremos que validar
            //para verificar se esta deletado virtualmente ou não
            $retorno = $this->db->query("select * from tbl_usuario 
                                        where usuario = '$usuario'");

            //verifica se a quantidade de linhas trazidas na consulta é superior a 0
            //vinculamos o resultado da query para tratarmos o resultado do status
            $linha = $retorno->row();
            if ($retorno->num_rows() == 0) {
                $dados = array('codigo' => 4, 'msg' => 'Usuario nao existe na base de dados.');
            }else{
                if (trim($linha->estatus) == 'D') {
                    $dados = array('codigo' => 5, 'msg' => 'Usuario desativado na base de dados, nao pode ser utilizado!');
                }else{
                    $dados = array('codigo' => 1, 'msg' => 'Usuario ja cadastrado');
                }
            }

        } catch (Exception $e) {
            $dados = array('codigo' => 00, 'msg' => 'Atencao: o seguinte erro aconteceu -> '. $e->getMessage());
        }

        return $dados;
    }

    private function validaIdUsuario($idUsuario){
        try {
            // atributo retorno recebe o resultado do select
            //sem status pois teremos que validar
            //para verificar se esta deletado virtualmente ou não
            $retorno = $this->db->query("select * from tbl_usuario where id_usuario = $idUsuario");

            //verifica se a quantidade de linhas trazidas na consulta é superior a 0
            //vinculamos o resultado da query para tratarmos o resultado do status
            $linha = $retorno->row();

            if ($retorno->num_rows() == 0) {
                $dados = array('codigo' => 4, 'msg' => 'Usuario não existe na base de dados.');
            } else {
                if(trim($linha->estatus) == "D"){
                    $dados = array('codigo' => 5, 'msg' => 'Usuario ja desativado na base de dados!');
                }else{
                    $dados = array('codigo' => 1, 'msg' => 'Usuario correto');
                }
            }
        } catch (Exception $e) {
            $dados = array('codigo' => 00, 'msg' => 'Atencao: o seguinte erro aconteceu -> '.$e->getMessage());
        }

        return $dados;
    }

    public function validaLogin($usuario, $senha){
        try {
            // atributo retorno recebe o resultado do select
            // realizado na tabela usuario lembrando da funcao MDS()
            // por causa da criptografia, e sem status pois teremos que validar
            // para verificar se esta deletado virtualmente ou não 
            $retorno = $this->db->query("select * from tbl_usuario where usuario = '$usuario' and senha = md5('$senha')");

            //verifica se a quantidade de linhas trazidas na consulta é superior a 0
            //vinculamos o resultado da query para tratarmos o resultado do status
            $linha = $retorno->row();

            if ($retorno->num_rows() == 0) {
                $dados = array('codigo' => 4, 'msg' => 'Usuario ou senha indalidos.');
            } else {
                if(trim($linha->estatus) == "D"){
                    $dados = array('codigo' => 5, 'msg' => 'Usuario ja desativado na base de dados!');
                }else{
                    $dados = array('codigo' => 1, 'msg' => 'Usuario correto');
                }
            }
        } catch (Exception $e) {
            $dados = array('codigo' => 00, 'msg' => 'Atencao: o seguinte erro aconteceu -> '.$e->getMessage());
        }

        return $dados;
    }
}