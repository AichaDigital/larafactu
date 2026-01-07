<?php

declare(strict_types=1);

namespace Installer\Actions;

use Installer\Security\Encryption;

/**
 * Stores certificates securely with encryption.
 */
class CertificateStore
{
    private string $storagePath;

    private Encryption $encryption;

    public function __construct(?string $storagePath = null, ?Encryption $encryption = null)
    {
        $this->storagePath = $storagePath ?? LARAFACTU_ROOT.'/storage/app/certificates';

        if ($encryption === null) {
            // Get APP_KEY from .env
            $envWriter = new EnvFileWriter;
            $appKey = $envWriter->get('APP_KEY');

            if ($appKey === null) {
                throw new \RuntimeException('APP_KEY must be set before storing certificates');
            }

            $this->encryption = new Encryption($appKey);
        } else {
            $this->encryption = $encryption;
        }

        $this->ensureDirectoryExists();
    }

    /**
     * Store a certificate file (encrypted)
     */
    public function store(string $name, string $content, ?string $password = null): ActionResult
    {
        try {
            // Encrypt the certificate content
            $encryptedContent = $this->encryption->encrypt($content);

            // Generate filename
            $filename = $this->sanitizeFilename($name).'.enc';
            $filepath = $this->storagePath.'/'.$filename;

            // Store encrypted content
            $written = file_put_contents($filepath, $encryptedContent);

            if ($written === false) {
                return ActionResult::failure(
                    'No se pudo guardar el certificado',
                    'file_put_contents failed'
                );
            }

            // Set restrictive permissions
            chmod($filepath, 0600);

            // If password provided, store it separately (also encrypted)
            $passwordPath = null;
            if ($password !== null) {
                $passwordPath = $this->storePassword($name, $password);
            }

            return ActionResult::success(
                'Certificado almacenado de forma segura',
                [
                    'path' => $filepath,
                    'filename' => $filename,
                    'password_path' => $passwordPath,
                    'size' => $written,
                ]
            );

        } catch (\Throwable $e) {
            return ActionResult::failure(
                'Error al almacenar certificado: '.$e->getMessage(),
                $e->getMessage()
            );
        }
    }

    /**
     * Store certificate from uploaded file
     */
    public function storeUpload(array $uploadedFile, string $name, ?string $password = null): ActionResult
    {
        // Validate upload
        if (! isset($uploadedFile['tmp_name']) || ! is_uploaded_file($uploadedFile['tmp_name'])) {
            return ActionResult::failure(
                'Archivo no válido',
                'Invalid upload'
            );
        }

        // Validate file type
        $allowedExtensions = ['p12', 'pfx', 'pem', 'cer', 'crt'];
        $extension = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));

        if (! in_array($extension, $allowedExtensions)) {
            return ActionResult::failure(
                'Tipo de archivo no permitido. Use: '.implode(', ', $allowedExtensions),
                'Invalid file type'
            );
        }

        // Read content
        $content = file_get_contents($uploadedFile['tmp_name']);

        if ($content === false) {
            return ActionResult::failure(
                'No se pudo leer el archivo',
                'file_get_contents failed'
            );
        }

        // Store with original extension preserved in name
        return $this->store($name.'.'.$extension, $content, $password);
    }

    /**
     * Retrieve a certificate (decrypted)
     */
    public function retrieve(string $name): ActionResult
    {
        try {
            $filename = $this->sanitizeFilename($name).'.enc';
            $filepath = $this->storagePath.'/'.$filename;

            if (! file_exists($filepath)) {
                return ActionResult::failure(
                    'Certificado no encontrado',
                    'File not found: '.$filename
                );
            }

            $encryptedContent = file_get_contents($filepath);

            if ($encryptedContent === false) {
                return ActionResult::failure(
                    'No se pudo leer el certificado',
                    'file_get_contents failed'
                );
            }

            $content = $this->encryption->decrypt($encryptedContent);

            return ActionResult::success(
                'Certificado recuperado',
                [
                    'content' => $content,
                    'path' => $filepath,
                ]
            );

        } catch (\Throwable $e) {
            return ActionResult::failure(
                'Error al recuperar certificado: '.$e->getMessage(),
                $e->getMessage()
            );
        }
    }

    /**
     * Check if certificate exists
     */
    public function exists(string $name): bool
    {
        $filename = $this->sanitizeFilename($name).'.enc';

        return file_exists($this->storagePath.'/'.$filename);
    }

    /**
     * Delete a certificate
     */
    public function delete(string $name): ActionResult
    {
        $filename = $this->sanitizeFilename($name).'.enc';
        $filepath = $this->storagePath.'/'.$filename;

        if (! file_exists($filepath)) {
            return ActionResult::failure(
                'Certificado no encontrado',
                'File not found'
            );
        }

        if (! unlink($filepath)) {
            return ActionResult::failure(
                'No se pudo eliminar el certificado',
                'unlink failed'
            );
        }

        // Also delete password if exists
        $passwordFile = $this->storagePath.'/'.$this->sanitizeFilename($name).'.pwd.enc';
        if (file_exists($passwordFile)) {
            unlink($passwordFile);
        }

        return ActionResult::success('Certificado eliminado');
    }

    /**
     * Store certificate password (encrypted)
     */
    private function storePassword(string $name, string $password): string
    {
        $encryptedPassword = $this->encryption->encrypt($password);
        $filename = $this->sanitizeFilename($name).'.pwd.enc';
        $filepath = $this->storagePath.'/'.$filename;

        file_put_contents($filepath, $encryptedPassword);
        chmod($filepath, 0600);

        return $filepath;
    }

    /**
     * Retrieve certificate password
     */
    public function retrievePassword(string $name): ?string
    {
        $filename = $this->sanitizeFilename($name).'.pwd.enc';
        $filepath = $this->storagePath.'/'.$filename;

        if (! file_exists($filepath)) {
            return null;
        }

        try {
            $encryptedPassword = file_get_contents($filepath);

            return $this->encryption->decrypt($encryptedPassword);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Sanitize filename
     */
    private function sanitizeFilename(string $name): string
    {
        // Remove path components
        $name = basename($name);

        // Replace unsafe characters
        $name = preg_replace('/[^a-zA-Z0-9._-]/', '_', $name);

        // Limit length
        if (strlen($name) > 100) {
            $name = substr($name, 0, 100);
        }

        return $name;
    }

    /**
     * Ensure storage directory exists
     */
    private function ensureDirectoryExists(): void
    {
        if (! is_dir($this->storagePath)) {
            mkdir($this->storagePath, 0700, true);
        }
    }

    /**
     * Get storage path
     */
    public function getStoragePath(): string
    {
        return $this->storagePath;
    }
}
