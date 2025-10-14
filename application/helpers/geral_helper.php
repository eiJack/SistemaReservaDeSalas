<?php
defined('BASEPATH') or exit('No direct script access allowed');

   /*
   Função para verificar os parâmetros vindos do FrontEnd
   */
    function verificarParam($atributos, $lista) {
        //1º   -Verificar se os elementos do Front estão nos atributos necessários
        foreach($lista as $key => $value) {
            if(array_key_exists($key, get_object_vars($atributos))){
                $estatus = 1;
            }else {
                $estatus = 0;
                break;
            }
        }

        // 2º   -Verificando a quantidade de elementos
        if(count(get_object_vars($atributos)) != count($lista)) {
            $estatus = 0;
        }

        return $estatus;
    }
    
    /*Função para verificar os tipos de dados*/

    function validarDados($valor, $tipo, $tamanhoZero = true){
        //verifica vazio ou nulo
        if (is_null($valor) || $valor === '') {
            return array('codigoHelper' => 2, 'msg' => 'Conteúdo nulo ou vazio.');
        }
        //se considerar '0' como vazio
        if ($tamanhoZero &&($valor === 0 || $valor === '0')) {
            return array('codigoHelper' => 3, 'msg' => 'Conteúdo zerado.');
        }

        switch($tipo) {
            case 'int':
                //Filtra como inteiro, aceitando '123' ou 123
                if (filter_var($valor, FILTER_VALIDATE_INT) === false) {
                    return array('codigoHelper' => 4, 'msg' => 'Conteúdo não inteiro.');
                }
                break;

            case 'string':
                //Garante que é string não vazia apos trim
                if (!is_string($valor) || trim($valor) === '') {
                    return array('codigoHelper' => 5, 'msg' => 'Conteudo não é um texto.');
                }
                break;
            
            case 'date':
                //Verifico se tem padrão de data
                if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $valor, $match)) {
                    return array('codigoHelper' => 6, 'msg' => 'Data em formato inválido');
                }else {
                    //Tenta criar DataTime no formato y-m-d
                    $d = DateTime::createFromFormat('Y-m-d', $valor);
                    if (($d->format('Y-m-d') === $valor) == false) {
                        return array('codigoHelper' => 6, 'msg' => 'Data inválida.');
                    }
                }
                break;
            
            case 'hora':
                //Verifico se tem padrão de hora
                if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $valor)) {
                    return array('codigoHelper' => 7, 'msg' => 'Hora em formato inválido.');
                }
                break;
            default:
                return array('codigoHelper' => 0, 'msg' => 'Validação correta.');
        }
        //Valor default da variavel $retorno caso não ocorra erro
        return array('codigoHelper' => 0, 'msg' => 'Validação correta.');
    }

    function validarDadosConsulta($valor, $tipo){
        if($valor != ''){
            switch ($tipo) {
                case 'int':
                    // filtra como inteiro, aceita '123' ou 123
                    if (filter_var($valor, FILTER_VALIDATE_INT) === false) {
                        return array('codigoHelper' => 4, 'msg' => 'Conteúdo não inteiro.');
                    }
                    break;

                case 'string':
                    // garante que é string não vazia apos trim
                    if (!is_string($valor|| trim($valor)) === '') {
                        return array('codigoHelper' => 5, 'msg' => 'Conteúdo não é um texto.');
                    }
                    break;

                case 'date':
                    // verifico se tem padrão de data
                    if (!preg_match('/(\d{4})-(\d{2})$/',$valor, $match)) {
                        return array('codigoHelper' => 6, 'msg' => 'Data em formato inválido.');
                    }else{
                        //tenta criar DataTime no formato y-m-d
                        $d = DateTime::createFromFormat('Y-m-d', $Valor);
                        if(($d->format('Y-m-d') === $valor) == false){
                            return array('codigoHelper' => 6, 'msg' => 'Data inválida');
                        }
                    }
                    break;

                case 'hora':
                    // verifico se tem padrão de hora
                    if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $valor)) {
                        return array('codigoHelper' => 7, 'msg' => 'Hora em formato invalido.');
                    }
                break;
                default:
                    return array('codigoHelper' => 97, 'msg' => 'Tipo de dado não definido');
                break;
            }
        }
        //valor default da variavel $retorno caso não ocorra erro
        return array('codigoHelper' =>0, 'msg' => 'Validação correta.');;
    }


    //funcao para verificar se datas ou horarios iniciais sao maioria entre eles
    function compararDataHora($valorInicial, $valorFinal, $tipo){
        //Passamos a strins para hora
        $valorInicial = strtotime($valorInicial);
        $valorFinal = strtotime($valorFinal);

        if ($valorInicial != '' && $valorFinal != '') {
            if ($valorInicial > $valorFinal) {
                switch ($tipo) {
                    case 'hora':
                        return array('codigoHelper' => 13, 'msg' => 'Hora final menor que a hora inicial');
                        break;
                    
                    case 'data':
                        return array('codigoHelper' => 14, 'msg' => 'Data final menor que a data inicial.');
                        break;
                    default:
                        return array('codigoHelper' => 15, 'msg' => 'Tipo de verificacao não definida');
                }
            }
        }

        //valor defaut da variavel $retorno caso não ocorra erro
        return array('codigoHelper' => 0, 'msg' => 'Validacao correta.');
    }
?>

