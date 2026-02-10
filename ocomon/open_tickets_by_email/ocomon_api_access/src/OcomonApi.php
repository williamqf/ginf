<?php

namespace ocomon_api_access\OcomonApi;

use CURLFile;

/**
 * Class OcomonApi
 * @package ocomon_api_access\OcomonApi
 */
abstract class OcomonApi
{
    /** @var string */
    private $apiUrl;

    /** @var array */
    private $headers;

    /** @var array */
    private $fields;

    /** @var string */
    private $endpoint;

    /** @var string */
    private $method;

    /** @var object */
    protected $response;

    /**
     * OcomonApi constructor.
     * @param string $apiUrl
     * @param string $login
     * @param string $app
     * @param string $token
     */
    public function __construct(string $apiUrl, string $login, string $app, string $token)
    {
        $this->apiUrl = $apiUrl;
        $this->headers([
            "login" => $login,
            "app" => $app,
            "token" => $token
        ]);
    }

    /**
     * @param string $method
     * @param string $endpoint
     * @param array|null $fields
     * @param array|null $headers
     */
    protected function request(string $method, string $endpoint, array $fields = null, array $headers = null): void
    {
        $this->method = $method;
        $this->endpoint = $endpoint;
        $this->fields = $fields;
        
        if ($headers)
            $this->headers($headers);

        $this->dispatch();
    }

    /**
     * @return object|null
     */
    public function response()
    {
        return $this->response;
    }

    /**
     * @return object|null
     */
    public function error()
    {
        if (!empty($this->response->errors)) {
            return $this->response->errors;
        }

        return null;
    }

    /**
     * @param array|null $headers
     */
    private function headers(?array $headers): void
    {
        if (!$headers) {
            return;
        }

        foreach ($headers as $key => $header) {
            $this->headers[] = "{$key}: {$header}";
        }
    }

    /**
     *
     */
    private function dispatch(): void
    {
        
        $files_tmp_names = [];
        $postData = [];
        if (!empty($this->fields["files[]"]) && !empty($this->fields["files[]"]['tmp_name'])) {
            /* Monta um array apenas com o indice `tmp_name' do $this->fields["files[]"] */
            foreach ($this->fields["files[]"]['tmp_name'] as $key => $value) {
                $files_tmp_names[] = $value;
            }
            
            $files = [];
            // Create array of files to post
            foreach ($files_tmp_names as $index => $file) {
                $files['files[' . $index . ']'] = curl_file_create(
                    realpath($file),
                    mime_content_type($file),
                    // basename($file)
                    $this->fields["files[]"]['name'][$index]
                );
            }
            unset($this->fields["files[]"]);

            // Combinar dados e arquivos
            $postData = array_merge($this->fields, $files);
        } else {
            $postData = $this->fields;
        }
        
        $curl = curl_init();

        $this->fields = (!empty($this->fields) ? http_build_query($this->fields) : null);

        /* Only for debug */
        // $verbose = fopen('php://temp', 'w+');
        // var_dump([
        //     'fields' => $this->fields,
        //     'headers' => $this->headers,
        //     'endpoint' => $this->endpoint,
        //     'method' => $this->method,
        //     'apiUrl' => $this->apiUrl
        // ]);

        curl_setopt_array($curl, array(
            CURLOPT_URL => "{$this->apiUrl}/{$this->endpoint}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $this->method,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => $this->headers,

            /* Only for debug */
            // CURLOPT_VERBOSE => true,
            // CURLOPT_STDERR => $verbose
        ));


        $this->response = json_decode(curl_exec($curl));

        /* Only for debug */
        // printf("cUrl error (#%d): %s<br>\n", curl_errno($curl), htmlspecialchars(curl_error($curl)));
        
        curl_close($curl);
    }

}