<?php

namespace Wcx\Video;

class Stream
{
    protected $path;

    protected $mimeType;

    /**
     * Create the stream object
     * @param string $path Path to the video file
     * @throws InvalidArgumentException If the file does not exist or is not acessible
     */
    public function __construct($path)
    {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException("The file '{$path}' does not exist.");
        }
    }

    public function stream()
    {
        // Caminho do arquivo
        $path = 'fire.mp4';

        // Determina o mimetype do arquivo
        $finfo = new finfo(FILEINFO_MIME);
        $mime = $finfo->file($path);

        // Tamanho do arquivo
        $size = filesize($path);

        //Verifica se foi passado o cabe�alho Range
        if (isset($_SERVER['HTTP_RANGE'])) {
            // Parse do valor do campo
            list($specifier, $value) = explode('=', $_SERVER['HTTP_RANGE']);
            if ($specifier != 'bytes') {
                header('HTTP/1.1 400 Bad Request');
                return;
            }

            // Determina os bytes de in�cio/fim
            list($from, $to) = explode('-', $value);
            if (!$to) {
                $to = $size - 1;
            }


            // Cabe�alho da resposta
            header('HTTP/1.1 206 Partial Content');
            header('Accept-Ranges: bytes');

            // Tamanho da resposta
            header('Content-Length: ' . ($to - $from));

            // Bytes enviados na resposta
            header("Content-Range: bytes {$from}-{$to}/{$size}");

            // Abre o arquivo no modo bin�ro
            $fp = fopen($file, 'rb');

            // Avan�a at� o primeiro byte solicitado
            fseek($fp, $from);

            // Manda os dados
            while(true){
                // Verifica se j� chegou ao byte final
                if(ftell($fp) >= $to){
                    break;
                }

                // Envia o conte�do
                echo fread($fp, $chunkSize);

                // Flush do buffer
                ob_flush();
                flush();
            }
        }
        else {
            // Se n�o possui o cabe�alho Range, envia todo o arquivo
            header('Content-Length: ' . $size);

            // L� o arquivo
            readfile($file);
        }
    }
}
