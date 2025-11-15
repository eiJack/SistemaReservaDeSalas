<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Usuario extends CI_Controller {
    /*
    Validacao dos tipos de retornos nas validacoes (codigo de erro)
    1 - Operacao realizada no banco de dados com sucesso(insercao, alteracao, consulta ou exclusao)
    2 - Conteudo passado nulo ou vazio
    3 - Conteudo zerado
    4 - Conteudo não inteiro
    5 - Conteudo nao é um texto
    8 - E-mail em formato inválido
    12 - Na atualizacao, pelo menos um atributo deve ser passado
    99 - parametros passados do front nao correspondem ao metodo
    */

    //atributos privados da classe
    private $idUsuario;
    private $nome;
    private $email;
    private $usuario;
    private $senha;

    public function getIdUsuario() {return $this->idUsuario;}
    public function getNome() {return $this->nome;}
    public function getEmail() {return $this->email;}
    public function getUsuario() {return $this->usuario;}
    public function getSenha() {return $this->senha;}

    public function setIdUsuario($idUsuarioFront){$this->idUsuario=$idUsuarioFront;}
    public function setNome($nomeFront){$this->nome=$nomeFront;}
    public function setEmail($emailFront){$this->email=$emailFront;}
    public function setUsuario($usuarioFront){$this->usuario=$usuarioFront;}
    public function setSenha($senhaFront){$this->senha=$senhaFront;}

    public function inserir(){
        //Atributos para controlar o status de nosso metodo
        $erros = [];
        $sucesso = false;

        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);
            $lista = ["nome"=>'0', "email"=>'0', "usuario" => '0', "senha" => '0'];
        if (verificarParam($resultado, $lista) != 1) {
                //Validar vindos de forma correta do frontend (helper)
                $erros[]=['codigo'=> 99, 'msg'=> 'Campos inexistentes ou incorretos no FrontEnd.'];
            }else{
                //Validar campos quanto ao tipo de dado e tamanho (helper)
                $retornoNome    = validarDados($resultado->nome, 'string', true);
                $retornoEmail   = validarDados($resultado->email, 'email', true);
                $retornoUsuario = validarDados($resultado->usuario,'string', true);
                $retornoSenha   = validarDados($resultado->senha, 'string', true);

                if (($retornoNome['codigoHelper'] != 0)) {
                    $erros[] = ['codigo' => $retornoNome['codigoHelper'],
                                'campo' => 'Nome',
                                'msg' => $retornoNome['msg']];
                }
                if (($retornoEmail['codigoHelper'] != 0)) {
                    $erros[] = ['codigo' => $retornoEmail['codigoHelper'],
                                'campo' => 'E-mail',
                                'msg' => $retornoEmail['msg']];
                }
                if (($retornoUsuario['codigoHelper'] != 0)) {
                    $erros[] = ['codigo' => $retornoUsuario['codigoHelper'],
                                'campo' => 'Usuário',
                                'msg' => $retornoUsuario['msg']];
                }
                if (($retornoSenha['codigoHelper'] != 0)) {
                    $erros[] = ['codigo' => $retornoSenha['codigoHelper'],
                                'campo' => 'Senha',
                                'msg' => $retornoSenha['msg']];
                }

                //se nao encontrar erros
                if (empty($erros)) {
                    $this->setNome($resultado->nome);
                    $this->setEmail($resultado->email);
                    $this->setUsuario($resultado->usuario);
                    $this->setSenha($resultado->senha);

                    $this->load->model('M_usuario');
                    $resBanco = $this->M_usuario->inserir(
                        $this->getNome(),
                        $this->getEmail(),
                        $this->getUsuario(),
                        $this->getSenha()
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
            $erros[] = ['codigo'=>0, 'msg'=>'ATENÇÃO: O seguinte erro aconteceu -> '.$e->getMessage()];
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
            $lista = ["nome"=>'0',"email"=>'0',"usuario"=>'0'];

            if (verificarParam($resultado, $lista) !=1) {
                //validar vindos de forma correta do front(helper)
                $erros[]= ['codigo'=>99, 'msg'=>'Campos inexistentes ou incorretos no FrontEnd'];
                
            }else{
                //validar campos quanto ao tipo de dado e tamanho(helper)
                $retornoNome    = validarDadosConsulta($resultado->nome, 'string');
                $retornoEmail   = validarDadosConsulta($resultado->email, 'email');
                $retornoUsuario = validarDadosConsulta($resultado->usuario, 'string');


                if ($retornoNome['codigoHelper'] != 0) {
                    $erros[] = ['codigo'=> $retornoNome['codigoHelper'],
                                'campo'=> 'Nome',
                                'msg' => $retornoNome['msg']];
                }

                if ($retornoEmail['codigoHelper'] != 0) {
                    $erros[] = ['codigo'=> $retornoEmail['codigoHelper'],
                                'campo'=> 'E-mail',
                                'msg' => $retornoEmail['msg']];
                }

                if ($retornoUsuario['codigoHelper'] != 0) {
                    $erros[] = ['codigo'=> $retornoUsuario['codigoHelper'],
                                'campo'=> 'Usuario',
                                'msg' => $retornoUsuario['msg']];
                }

                //se não encontrar erros
                if (empty($erros)) {
                    $this->setNome($resultado->nome);
                    $this->setEmail($resultado->email);
                    $this->setUsuario($resultado->usuario);

                    $this->load->model('M_usuario');
                    $resBanco = $this->M_usuario->consultar($this->getNome(),
                                                            $this->getEmail(),
                                                            $this->getUsuario());
                    if ($resBanco['codigo']==1) {
                        $sucesso = true;
                    }else{
                        //captura erro do banco
                        $erros[] = ['codigo'=>$resBanco['codigo'], 'msg'=> $resBanco['msg']];
                    }
                }
            }
        } catch (Exception $e) {
            $erros[] = ['codigo'=>0, 'msg'=>'ATENÇÃO: O seguinte erro aconteceu ->  '.$e->getMessage()];
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
            $lista = ["idUsuario"=>'0',"nome"=>'0',"email"=>'0',"senha"=>'0'];

            if (verificarParam($resultado, $lista) !=1) {
                //validar vindos de forma correta do front(helper)
                $erros[]= ['codigo'=>99, 'msg'=>'Campos inexistentes ou incorretos no FrontEnd'];
                
            }else{
                //pelo menos um dos tres parametros precisam ter dados para acontecer a atualizacao
                if (trim($resultado->nome)== '' && trim($resultado->email)=='' && trim($resultado->senha)=='') {
                    $erros[] = ['codigo'=>12, 'msg'=> 'Pelo menos um parametro precisar ser passado para atualização'];
                }else{
                    //validar campos quanto ao tipo de dado e tamanho(helper)
                    $retornoIdUsuario = validarDados($resultado->idUsuario, 'int');
                    $retornoNome      = validarDadosConsulta($resultado->nome, 'string');
                    $retornoEmail     = validarDadosConsulta($resultado->email, 'string');
                    $retornoSenha     = validarDadosConsulta($resultado->senha, 'string');
                    
                    if ($retornoIdUsuario['codigoHelper'] != 0) {
                        $erros[] = ['codigo'=> $retornoIdUsuario['codigoHelper'],
                                    'campo'=> 'ID Usuário',
                                    'msg' => $retornoIdUsuario['msg']];
                        }

                    if ($retornoNome['codigoHelper'] != 0) {
                        $erros[] = ['codigo'=> $retornoNome['codigoHelper'],
                                    'campo'=> 'Nome',
                                    'msg' => $retornoNome['msg']];
                    }

                    if ($retornoEmail['codigoHelper'] != 0) {
                        $erros[] = ['codigo'=> $retornoEmail['codigoHelper'],
                                    'campo'=> 'E-mail',
                                    'msg' => $retornoEmail['msg']];
                    }

                    if ($retornoSenha['codigoHelper'] != 0) {
                        $erros[] = ['codigo'=> $retornoSenha['codigoHelper'],
                                    'campo'=> 'Senha',
                                    'msg' => $retornoSenha['msg']];
                    }

                    //se não encontrar erros
                    if (empty($erros)) {
                        $this->setIdUsuario($resultado->idUsuario);
                        $this->setNome($resultado->nome);
                        $this->setEmail($resultado->email);
                        $this->setSenha($resultado->senha);

                        $this->load->model('M_usuario');
                        $resBanco = $this->M_usuario->alterar($this->getIdUsuario(),
                                                                $this->getNome(),
                                                                $this->getEmail(),
                                                                $this->getSenha());
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
            $erros[] = ['codigo'=>0, 'msg'=>'ATENÇÃO: O seguinte erro aconteceu -> '.$e->getMessage()];
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
            $lista = ["idUsuario"=>'0'];

            if (verificarParam($resultado, $lista) !=1) {
                //validar vindos de forma correta do front(helper)
                $erros[]= ['codigo'=>99, 'msg'=>'Campos inexistentes ou incorretos no FrontEnd'];
                
            }else{
                //validar codigo quanto ao tipo de dado e tamanho(helper)
                $retornoIdUsuario = validarDados($resultado->idUsuario, 'int');

                if ($retornoIdUsuario['codigoHelper'] != 0) {
                    $erros[] = [
                        'codigo' => $retornoIdUsuario['codigoHelper'],
                        'campo' => 'ID Usuario',
                        'msg' => $retornoIdUsuario['msg']
                    ];
                }

                //se não encontrar erros
                if (empty($erros)) {
                    $this->setIdUsuario($resultado->idUsuario);
                    $this->load->model('M_usuario');
                    $resBanco = $this->M_usuario->desativar($this->getIdUsuario());

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
            $erros[] = ['codigo'=> 0, 'msg'=> 'ATENÇÃO: O seguinte erro aconteceu -> '.$e->getMessage()];
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

    public function logar(){
        $erros = [];
        $sucesso = false;

        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);

            $lista = array(
                "usuario" => '0',
                "senha"   => '0'
            );

            if (verificarParam($resultado, $lista) != 1) {
                $erros[] = ['codigo'=> 99, 'msg'=> 'Campos inexistentes ou incorretos no FrontEnd.'];
            }else {
                $retornoUsuario = validarDados($resultado->usuario, 'string', true);
                $retornoSenha   = validarDados($resultado->senha, 'string', true);

                if ($retornoUsuario['codigoHelper'] != 0) {
                    $erros[] = [
                                'codigo' => $retornoUsuario['codigoHelper'],
                                'campo' => 'Usuario',
                                'msg' => $retornoUsuario['msg']
                    ];
                }

                if ($retornoSenha['codigoHelper'] != 0) {
                    $erros[] = [
                                'codigo' => $retornoSenha['codigoHelper'],
                                'campo' => 'Senha',
                                'msg' => $retornoSenha['msg']
                    ];
                }

                if (empty($erros)) {
                    $this->setUsuario($resultado->usuario);
                    $this->setSenha($resultado->senha);

                    $this->load->model('M_usuario');

                    $resBanco = $this->M_usuario->validaLogin($this->getUsuario(), $this->getSenha());

                    if ($resBanco['codigo'] == 1) {
                        $sucesso = true;
                    }else{
                        $erros[] = [
                            'codigo' => $resBanco['codigo'],
                            'msg'    => $resBanco['msg']
                        ];
                    }
                }
            }

        } catch (Exception $e) {
            $erros[] = ['codigo'=> 0, 'msg'=> 'ATENÇÃO: O seguinte erro aconteceu -> '.$e->getMessage()];
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