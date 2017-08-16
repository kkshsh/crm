<?php
namespace App\Helps;

use Symfony\Component\HttpFoundation\File\Exception\FileException;

class Tool
{
    public function dir($directory)
    {
        if (!is_dir($directory)) {
            if (false === @mkdir($directory, 0777, true) && !is_dir($directory)) {
                throw new FileException(sprintf('Unable to create the "%s" directory', $directory));
            }
        } elseif (!is_writable($directory)) {
            throw new FileException(sprintf('Unable to write in the "%s" directory', $directory));
        }
    }

    public function getFileExtension($fileName)
    {
        return pathinfo($fileName, PATHINFO_EXTENSION);
    }

    public function base64Decode($content)
    {
        $content = strtr($content, '-_', '+/');
        return base64_decode($content);
    }

    public function base64Encode($content)
    {
        //return $content;
        return rtrim(strtr(base64_encode($content), '+/', '-_'), '=');
    }

}