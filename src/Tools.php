<?php

namespace NFService\Clicksign;

use Exception;

/**
 * Classe Tools
 *
 * Classe responsável pela comunicação com a API Nectar CRM
 *
 * @category  NFService
 * @package   NFService\Clicksign\Tools
 * @author    Diego Almeida <diego.feres82 at gmail dot com>
 * @copyright 2021 NFSERVICE
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Tools
{
    /**
     * URL base para comunicação com a API
     *
     * @var string
     */
    public static $API_URL = [
        1 => 'https://app.clicksign.com/api/v1',
        2 => 'https://sandbox.clicksign.com/api/v1'
    ];

    /**
     * Variável responsável por armazenar os dados a serem utilizados para comunicação com a API
     * Dados como token, ambiente(produção ou homologação) e debug(true|false)
     *
     * @var array
     */
    private $config = [
        'token' => '',
        'environment' => 1,
        'debug' => false,
        'upload' => false,
        'decode' => true
    ];

    /**
     * Define se a classe realizará um upload
     *
     * @param bool $isUpload Boleano para definir se é upload ou não
     *
     * @access public
     * @return void
     */
    public function setUpload(bool $isUpload) :void
    {
        $this->config['upload'] = $isUpload;
    }

    /**
     * Define se a classe realizará o decode do retorno
     *
     * @param bool $decode Boleano para definir se fa decode ou não
     *
     * @access public
     * @return void
     */
    public function setDecode(bool $decode) :void
    {
        $this->config['decode'] = $decode;
    }

    /**
     * Função responsável por definir se está em modo de debug ou não a comunicação com a API
     * Utilizado para pegar informações da requisição
     *
     * @param bool $isDebug Boleano para definir se é produção ou não
     *
     * @access public
     * @return void
     */
    public function setDebug(bool $isDebug) :void
    {
        $this->config['debug'] = $isDebug;
    }

    /**
     * Função responsável por definir o ambiente atual
     *
     * @param int $environment Tipo de ambente (1 - Produção | 2 - Sandbox)
     *
     * @access public
     * @return void
     */
    public function setEnvironment(int $environment) :void
    {
        if (in_array($environment, [1, 2])) {
            $this->config['environment'] = $environment;
        }
    }

    /**
     * Função responsável por definir o token a ser utilizado para comunicação com a API
     *
     * @param string $token Token para autenticação na API
     *
     * @access public
     * @return void
     */
    public function setToken(string $token) :void
    {
        $this->config['token'] = $token;
    }

    /**
     * Recupera se é upload ou não
     *
     *
     * @access public
     * @return bool
     */
    public function getUpload() : bool
    {
        return $this->config['upload'];
    }

    /**
     * Recupera se faz decode ou não
     *
     *
     * @access public
     * @return bool
     */
    public function getDecode() : bool
    {
        return $this->config['decode'];
    }

    /**
     * Recupera o ambiente definido para a biblioteca
     *
     * @access public
     * @return int
     */
    public function getEnvironment() :int
    {
        return $this->config['environment'];
    }

    /**
     * Retorna o token utilizado para comunicação com a API
     *
     * @access public
     * @return string
     */
    public function getToken() :string
    {
        return $this->config['token'];
    }

    /**
     * Retorna os cabeçalhos padrão para comunicação com a API
     *
     * @access private
     * @return array
     */
    private function getDefaultHeaders() :array
    {
        $headers = [
            'Accept: application/json',
        ];

        if (!$this->config['upload']) {
            $headers[] = 'Content-Type: application/json';
        } else {
            $headers[] = 'Content-Type: multipart/form-data';
        }
        return $headers;
    }

    /**
     * Cadastra um novo signatário
     *
     * @param array $dados Dados para o cadastro do signatário
     *
     * @access public
     * @return array
     */
    public function cadastraSignatario(array $dados, array $params = []): array
    {
        try {
            $dados = $this->post('signers', $dados, $params);

            if (in_array($dados['httpCode'], [200, 201])) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            if (isset($dados['body']->errors)) {
                throw new Exception(implode("\r\n", $dados['body']->errors), 1);
            }

            throw new Exception('Ocorreu um erro interno', 1);
        } catch (Exception $error) {
            throw $error;
        }
    }

    /**
     * Cadastra um novo documento
     *
     * @param array $dados Dados para o cadastro do documento
     *
     * @access public
     * @return array
     */
    public function cadastraDocumento(array $dados, array $params = []): array
    {
        try {
            $dados = $this->post('documents', $dados, $params);

            if (in_array($dados['httpCode'], [200, 201])) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            if (isset($dados['body']->errors)) {
                throw new Exception(implode("\r\n", $dados['body']->errors), 1);
            }

            throw new Exception('Ocorreu um erro interno', 1);
        } catch (Exception $error) {
            throw $error;
        }
    }

    /**
     * Consulta um documento
     *
     * @param string $key Chave do documento na Clicksign
     *
     * @access public
     * @return array
     */
    public function consultaDocumento(string $key, array $params = []): array
    {
        try {
            $dados = $this->get("documents/$key", $params);

            if (in_array($dados['httpCode'], [200, 201])) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            if (isset($dados['body']->errors)) {
                throw new Exception(implode("\r\n", $dados['body']->errors), 1);
            }

            throw new Exception('Ocorreu um erro interno', 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Finalisa um documento
     *
     * @param string $key Chave do documento na Clicksign
     *
     * @access public
     * @return array
     */
    public function finalizaDocumento(string $key, array $params = []): array
    {
        try {
            $dados = $this->patch("documents/$key/finish", [], $params);

            if (in_array($dados['httpCode'], [200, 201])) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            if (isset($dados['body']->errors)) {
                throw new Exception(implode("\r\n", $dados['body']->errors), 1);
            }

            throw new Exception('Ocorreu um erro interno', 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Cancela um documento
     *
     * @param string $key Chave do documento na Clicksign
     *
     * @access public
     * @return array
     */
    public function cancelaDocumento(string $key, array $params = []): array
    {
        try {
            $dados = $this->patch("documents/$key/cancel", [], $params);

            if (in_array($dados['httpCode'], [200, 201])) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            if (isset($dados['body']->errors)) {
                throw new Exception(implode("\r\n", $dados['body']->errors), 1);
            }

            throw new Exception('Ocorreu um erro interno', 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Vincula um signatário a um documento
     *
     * @param string $documentKey Chave do documento na Clicksign
     * @param string $signerKey Chave do signatário na Clicksign
     * @param string $signerType Tipo do signatário
     *
     * @access public
     * @return array
     */
    public function vinculaSignatarioDocumento(string $documentKey, string $signerKey, string $signerType, array $params = []): array
    {
        try {
            $dados = [
                'list' => [
                    'document_key' => $documentKey,
                    'signer_key' => $signerKey,
                    'sign_as' => $signerType
                ]
            ];

            $dados = $this->post("lists", $dados, $params);

            if (in_array($dados['httpCode'], [200, 201])) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            if (isset($dados['body']->errors)) {
                throw new Exception(implode("\r\n", $dados['body']->errors), 1);
            }

            throw new Exception('Ocorreu um erro interno', 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Assina um documento
     *
     * @param string $requestSign Hash do vinculo entro documento(document) e o signatário(signer)
     * @param string $secretSign Chave Privada do signatario para assinatura de documentos
     *
     * @access public
     * @return array
     */
    public function assinaDocumento(string $requestSign, string $secretSign, array $params = []): array
    {
        try {
            /**Dados para assinatura */
            $dados = [
                'request_signature_key' => $requestSign,
                /**Calcula o HASH de assinatura de acordo com o HASH do Signatário(signer) */
                'secret_hmac_sha256' => $this->calculaHMAC($requestSign, $secretSign)
            ];

            $dados = $this->post("sign", $dados, $params);

            if (in_array($dados['httpCode'], [200, 201])) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                throw new Exception($dados['body']->message, 1);
            }

            if (isset($dados['body']->errors)) {
                throw new Exception(implode("\r\n", $dados['body']->errors), 1);
            }

            throw new Exception('Ocorreu um erro interno', 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por calcular o HMAC de uma string de acordo a secret informado
     *
     * @param string $string Texto a ser codificado
     * @param string $secret Chave Privada para codificação
     *
     * @access public
     * @return string
     */
    public function calculaHMAC($string = '', $secret = '')
    {
        return hash_hmac('sha256', $string, $secret);
    }

    /**
     * Execute a GET Request
     *
     * @param string $path
     * @param array $params
     * @param array $headers Cabeçalhos adicionais para requisição
     *
     * @access protected
     * @return array
     */
    protected function get(string $path, array $params = [], array $headers = []) :array
    {
        $opts = [
            CURLOPT_HTTPHEADER => $this->getDefaultHeaders()
        ];

        if (!empty($headers)) {
            $opts[CURLOPT_HTTPHEADER] = array_merge($opts[CURLOPT_HTTPHEADER], $headers);
        }

        $exec = $this->execute($path, $opts, $params);

        return $exec;
    }

    /**
     * Execute a POST Request
     *
     * @param string $path
     * @param string $body
     * @param array $params
     * @param array $headers Cabeçalhos adicionais para requisição
     *
     * @access protected
     * @return array
     */
    protected function post(string $path, array $body = [], array $params = [], array $headers = []) :array
    {
        $opts = [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => !$this->config['upload'] ? json_encode($body) : $body,
            CURLOPT_HTTPHEADER => $this->getDefaultHeaders()
        ];

        if (!empty($headers)) {
            $opts[CURLOPT_HTTPHEADER] = array_merge($opts[CURLOPT_HTTPHEADER], $headers);
        }

        $exec = $this->execute($path, $opts, $params);

        return $exec;
    }

    /**
     * Execute a PUT Request
     *
     * @param string $path
     * @param string $body
     * @param array $params
     * @param array $headers Cabeçalhos adicionais para requisição
     *
     * @access protected
     * @return array
     */
    protected function put(string $path, array $body = [], array $params = [], array $headers = []) :array
    {
        $opts = [
            CURLOPT_HTTPHEADER => $this->getDefaultHeaders(),
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS => json_encode($body)
        ];

        if (!empty($headers)) {
            $opts[CURLOPT_HTTPHEADER] = array_merge($opts[CURLOPT_HTTPHEADER], $headers);
        }

        $exec = $this->execute($path, $opts, $params);

        return $exec;
    }

    /**
     * Execute a PATCH Request
     *
     * @param string $path
     * @param string $body
     * @param array $params
     * @param array $headers Cabeçalhos adicionais para requisição
     *
     * @access protected
     * @return array
     */
    protected function patch(string $path, array $body = [], array $params = [], array $headers = []) :array
    {
        $opts = [
            CURLOPT_HTTPHEADER => $this->getDefaultHeaders(),
            CURLOPT_CUSTOMREQUEST => "PATCH",
            CURLOPT_POSTFIELDS => json_encode($body)
        ];

        if (!empty($headers)) {
            $opts[CURLOPT_HTTPHEADER] = array_merge($opts[CURLOPT_HTTPHEADER], $headers);
        }

        $exec = $this->execute($path, $opts, $params);

        return $exec;
    }

    /**
     * Execute a DELETE Request
     *
     * @param string $path
     * @param array $params
     * @param array $headers Cabeçalhos adicionais para requisição
     *
     * @access protected
     * @return array
     */
    protected function delete(string $path, array $params = [], array $headers = []) :array
    {
        $opts = [
            CURLOPT_HTTPHEADER => $this->getDefaultHeaders(),
            CURLOPT_CUSTOMREQUEST => "DELETE"
        ];

        if (!empty($headers)) {
            $opts[CURLOPT_HTTPHEADER] = array_merge($opts[CURLOPT_HTTPHEADER], $headers);
        }

        $exec = $this->execute($path, $opts, $params);

        return $exec;
    }

    /**
     * Execute a OPTION Request
     *
     * @param string $path
     * @param array $params
     * @param array $headers Cabeçalhos adicionais para requisição
     *
     * @access protected
     * @return array
     */
    protected function options(string $path, array $params = [], array $headers = []) :array
    {
        $opts = [
            CURLOPT_CUSTOMREQUEST => "OPTIONS"
        ];

        if (!empty($headers)) {
            $opts[CURLOPT_HTTPHEADER] = $headers;
        }

        $exec = $this->execute($path, $opts, $params);

        return $exec;
    }

    /**
     * Função responsável por realizar a requisição e devolver os dados
     *
     * @param string $path Rota a ser acessada
     * @param array $opts Opções do CURL
     * @param array $params Parametros query a serem passados para requisição
     *
     * @access protected
     * @return array
     */
    protected function execute(string $path, array $opts = [], array $params = []) :array
    {
        $params = array_filter($params, function($item) {
            return $item['name'] !== 'access_token';
        }, ARRAY_FILTER_USE_BOTH);

        $params[] = ['name' => 'access_token', 'value' => $this->config['token']];

        if (!preg_match("/^\//", $path)) {
            $path = '/' . $path;
        }

        $url = self::$API_URL[$this->config['environment']].$path;

        $curlC = curl_init();

        if (!empty($opts)) {
            curl_setopt_array($curlC, $opts);
        }

        if (!empty($params)) {
            $paramsJoined = [];

            foreach ($params as $param) {
                if (isset($param['name']) && !empty($param['name']) && isset($param['value']) && !empty($param['value'])) {
                    $paramsJoined[] = urlencode($param['name'])."=".urlencode($param['value']);
                }
            }

            if (!empty($paramsJoined)) {
                $params = '?'.implode('&', $paramsJoined);
                $url = $url.$params;
            }
        }

        curl_setopt($curlC, CURLOPT_URL, $url);
        curl_setopt($curlC, CURLOPT_RETURNTRANSFER, true);
        if (!empty($dados)) {
            curl_setopt($curlC, CURLOPT_POSTFIELDS, json_encode($dados));
        }
        $retorno = curl_exec($curlC);
        $info = curl_getinfo($curlC);
        $return["body"] = ($this->config['decode'] || !$this->config['decode'] && $info['http_code'] != '200') ? json_decode($retorno) : $retorno;
        $return["httpCode"] = curl_getinfo($curlC, CURLINFO_HTTP_CODE);
        if ($this->config['debug']) {
            $return['info'] = curl_getinfo($curlC);
        }
        curl_close($curlC);

        return $return;
    }
}
