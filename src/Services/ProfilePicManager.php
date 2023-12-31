<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProfilePicManager
{

    public function __construct(private readonly string $profilePicDirectory)
    {
    }

    public function upload(UploadedFile $profilePicture): string
    {
        $filename = uniqid() . $profilePicture->guessExtension();

        try {
            $profilePicture->move($this->getProfilePicDirectory(), $filename);
        } catch (FileException $fe) {
            return 'FAILED';
        }
        return $filename;
    }

    public function delete(?string $filename, string $directory): void
    {
        if ($filename != 'defaultAdminPicture.png' && $filename != 'defaultProfilePicture.png') {
            $path = $directory . '/' . $filename;
            if ($filename != null && file_exists($path)) {
                unlink($path);
            }
        }
    }


    public function getProfilePicDirectory(): string
    {
        return $this->profilePicDirectory;
    }
}