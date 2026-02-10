<?php

namespace OcomonApi\Controllers;

use OcomonApi\Models\File;
use OcomonApi\Models\Config;
use OcomonApi\Core\OcomonApi;
use OcomonApi\Models\AppsRegister;

require_once __DIR__ . "../../../../../includes/functions/functions.php";


class Files extends OcomonApi
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * List files data by ticket
     */
    public function findByTicket(array $data): void
    {
        
        if (empty($data['ticket']) || !filter_var($data['ticket'], FILTER_VALIDATE_INT)) {
            $this->call(
                400,
                "invalid_data",
                "É necessário informar o número do chamado que deseja consultar"
            )->back();
            return;
        }

        
        $files = (new File())->findByTicket($data['ticket']);

        /** @var File $files */
        foreach ($files as $file) {
            $response[]['file'] = $file->data();
        }

        $this->back($response);
        return;
    }


    public function save(array $data): bool
    {
        
        /** Checagem para saber se o método pode ser acessado para o app informado na conexao 
         * Por ora essa checagem está sendo dispensada neste método.
        */

        // $app = (new AppsRegister())->methodAllowedByApp($this->headers["app"], get_class($this), __FUNCTION__);
        // if (!$app) {
        //     return $this->call(
        //         401,
        //         "access_not_allowed - Class: " . get_class($this),
        //         "Esse APP não está registrado para esse tipo de acesso: " . $this->headers["app"]
        //     )->back();
        // }
        
        
        if (empty($data['ticket']) || !filter_var($data['ticket'], FILTER_VALIDATE_INT)) {
            $this->call(
                400,
                "invalid_data",
                "É necessário informar o número do chamado para vincular o arquivo"
            )->back();
            return null;
        }

        $config = (new Config())->findById(1);
        $fileinput = $data['tmp_name'];

        $maxAllowedSize = $config->data()->conf_upld_size ?? 0;
        $fileSize = filesize($fileinput);

        if ($fileSize > $maxAllowedSize) {
            $this->call(
                400,
                "invalid_data",
                "O arquivo ultrapassa o tamanho permitido"
            )->back();
            unlink($fileinput);
            return true;
        }
        
        $allowedFileTypes = array_filter(explode("%",$config->data()->conf_upld_file_types ?? ""));
        
        /* ajustar os índices do array de forma sequencial iniciando em 0 após o array_filter */
        $allowedFileTypes = array_values($allowedFileTypes);
        // reIndexArray($allowedFileTypes);

        $mime['PDF'] = "application\/pdf";
        $mime['TXT'] = "text\/plain";
        $mime['RTF'] = "application\/rtf";
        $mime['HTML'] = "text\/html";
        $mime['WAV'] = "audio\/(x-wav|wav)";
        $mime['IMG'] = "image\/(pjpeg|jpeg|png|gif|x-ms-bmp)";
        $mime['ODF'] = "application\/vnd.oasis.opendocument.(text|spreadsheet|presentation|graphics)";
        $mime['OOO'] = "application\/vnd.sun.xml.(writer|calc|draw|impress)";
        $mime['MSO'] = "application\/(msword|vnd.ms-excel|vnd.ms-powerpoint)";
        $mime['NMSO'] = "application\/vnd.openxmlformats-officedocument.(wordprocessingml.document|spreadsheetml.sheet|presentationml.presentation|presentationml.slideshow)";

        /* Conferir se o tipo do arquivo está entre os tipos permitidos - cada tipo permitido aponta para um mime */
        $typeOK = false;
        
        
        if (empty($allowedFileTypes)) {
            $this->call(
                400,
                "invalid_data",
                "Nenhum tipo de arquivo é permitido"
            )->back();
            return null;
            unlink($fileinput);
            return true;
        }
        for ($i = 0; $i < count($allowedFileTypes); $i++) {
            if (preg_match("/^" . $mime[$allowedFileTypes[$i]] . "$/i", $data["type"])) {
                $typeOK = true;
            }
        }   

        if (!$typeOK) {
            // $this->call(
            //     400,
            //     "invalid_data",
            //     "Tipo de arquivo inválido"
            // )->back();

            unlink($fileinput);
            return true;
        }
        

        $widhAndHeight = getimagesize($fileinput);

        if (!$widhAndHeight) {
            /* Nâo é imagem */
            unset ($widhAndHeight);
            $widhAndHeight = [];
            $widhAndHeight[0] = null;
            $widhAndHeight[1] = null;
        }


        if (chop($fileinput) != "") {
            $fileinput = chop($fileinput);
        }

        if (empty($fileinput)) {
            $this->call(
                400,
                "invalid_data",
                "O arquivo informado é inválido"
            )->back();
            return null;
        }

        if (!file_exists($fileinput)) {
            $this->call(
                400,
                "invalid_data",
                "O arquivo informado não existe"
            )->back();
            return null;
        }

        // $fileRead = addslashes(fread(fopen($fileinput, "r"), $config['conf_upld_size']));
        // $fileRead = addslashes(fread(fopen($fileinput, "r"), 10485760));

        $file = new File();
        $file->img_oco = $data['ticket'];
        $file->img_nome = noSpace($data['name']);
        $file->img_tipo = $data['type'];
        $file->img_largura = $widhAndHeight[0];
        $file->img_altura = $widhAndHeight[1];
        
        $file->img_bin = file_get_contents($fileinput);
        
        // $file->img_size = $data['size'];
        $file->img_size = $fileSize;

        unlink($fileinput);
        // $file->save();

        if (!$file->save()) {
            // $this->call(
            //     400,
            //     "invalid_data",
            //     "Problemas na tentativa de gravar o arquivo"
            // )->back();

            return false;
        }

        return true;
        // $this->back($file->data());
        // return $this;
    }
    

}