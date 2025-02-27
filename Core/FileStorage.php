<?php

namespace Core;

use Core\Enums\FileStorageDisk;
use Core\Enums\FileStorageType;
use Aws\S3\S3Client;
use Exception;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\UnableToRetrieveMetadata;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;

class FileStorage
{
    private string $rootPath;
    private Filesystem $filesystem;

    public function __construct(FileStorageDisk $disk = FileStorageDisk::LOCAL, $rootPath = "public/assets")
    {
        $this->rootPath = $rootPath;
        if ($disk == FileStorageDisk::LOCAL)
            $adapter = new LocalFilesystemAdapter(
                Functions::getRoot() . DIRECTORY_SEPARATOR . $this->rootPath,
                PortableVisibilityConverter::fromArray([
                                        'file' => [
                                            'public' => 0644,
                                            'private' => 0644,
                                        ],
                                        'dir' => [
                                            'public' => 0755,
                                            'private' => 0755,
                                        ],
                                    ])
                                );
        else {
            $client = new S3Client($_ENV["AWS_CLIENT"]);
            $adapter = new AwsS3V3Adapter($client, $_ENV["AWS_BUCKETNAME"]);
        }

        $this->filesystem = new Filesystem($adapter);
    }

    public function saveByPath(string $path, string $pastaDestino = "imagens",string $nomeCompletoArquivo = "",$maxSizeBytes = 6000000,fileStorageType $type = FileStorageType::IMAGEM): bool|string 
    {
        if (file_exists($path)) {

            if ($this->validType($path,$type) == false) {
                return false;
            }

            if ($this->validSize($path,$maxSizeBytes) == false) {
                return false;
            }

            $nomeArquivo = str_replace(" ", "_", basename($nomeCompletoArquivo?:$path));

            $caminhoCompleto = $pastaDestino . DIRECTORY_SEPARATOR . Functions::onlynumber(microtime()) . $nomeArquivo;

            try {
                $this->filesystem->write($caminhoCompleto, file_get_contents($path),[]);
                return DIRECTORY_SEPARATOR . Functions::getAbsolutePath($this->rootPath . DIRECTORY_SEPARATOR . $caminhoCompleto);
            } catch (Exception $e) {
                Logger::error($e->getMessage() . $e->getTraceAsString());
                Message::setError("Error ao salvar arquivo");
                return false;
            }
        }

        Message::setError("Arquivo não encontrado");
        return false;
    }

    public function saveFromResquest(array $fileArray, string $pastaDestino = "Imagens",$maxSizeBytes = 6000000,fileStorageType $type = FileStorageType::IMAGEM): bool|string
    {
        if (isset($fileArray["tmp_name"]) && $fileArray['error'] == 0) {

            if ($this->validType($fileArray["tmp_name"],$type) == false) {
                return false;
            }

            if ($this->validSize($fileArray["tmp_name"],$maxSizeBytes) == false) {
                return false;
            }

            $nomeArquivo = str_replace(" ", "_", basename($fileArray['name']));

            $caminhoCompleto = $pastaDestino . DIRECTORY_SEPARATOR . time() . $nomeArquivo;

            try {
                $this->filesystem->write($caminhoCompleto, file_get_contents($fileArray['tmp_name']));
                return DIRECTORY_SEPARATOR . Functions::getAbsolutePath($this->rootPath . DIRECTORY_SEPARATOR . $caminhoCompleto);
            } catch (Exception $e) {
                Logger::error($e->getMessage() . $e->getTraceAsString());
                Message::setError("Error ao salvar arquivo");
                return false;
            }
        }

        Message::setError("Arquivo não encontrado na requisição");
        return false;
    }

    public function validType(string $filePath,fileStorageType $type):bool
    {
        try {
            $tipo = mime_content_type($filePath);
        } catch (Exception $e) {
            Message::setError("O tamanho do arquivo não é permitido");
            return false;
        }
       
        $tiposPermitidos = [];
        if ($type == FileStorageType::DOCUMENT)
            $tiposPermitidos = ["application/pdf", "application/doc", "application/docx", "application/rtf", "application/txt", "application/odf", "application/msword"];
        else if ($type == FileStorageType::IMAGEM)
            $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/avif', 'image/webp', 'image/svg+xml'];

        if (!in_array($tipo, $tiposPermitidos)) {
            Message::setError("O tipo de arquivo não é permitido");
            return false;
        }

        return true;
    }

    public function validSize(string $filePath,int $maxSize){

        try {
            $fileSize = fileSize($filePath);
        } catch (Exception $e) {
            Message::setError("O tamanho do arquivo não é permitido");
            return false;
        }

        if ($fileSize > $maxSize) {
            Message::setError("O tamanho do arquivo não é permitido");
            return false;
        }

        return true;
    }

    public function saveFromString(string $pastaDestino = "imagens", string $contents = ""): bool
    {
        try {
            $this->filesystem->write($pastaDestino, $contents);
            return true;
        } catch (Exception $e) {
            Logger::error($e->getMessage() . $e->getTraceAsString());
            Message::setError("Error ao salvar arquivo");
            return false;
        }
    }

    public function delete(string $filePath): bool
    {
        try {
            $this->filesystem->delete($filePath);
            return true;
        } catch (FilesystemException | UnableToRetrieveMetadata $e) {
            Logger::error($e->getMessage() . $e->getTraceAsString());
            Message::setError("Error ao deletar arquivo");
            return false;
        }
    }
}
