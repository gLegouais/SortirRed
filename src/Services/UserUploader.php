<?php

namespace App\Services;

use App\Entity\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UserUploader
{

    public function uploadUsers(UploadedFile $usersCSV): string
    {
        $usersArray = file($usersCSV, FILE_IGNORE_NEW_LINES);
        $headers = $usersArray[0];
        $usersMapping = [];
        for ($i = 1; $i < count($usersArray) - 1; $i++) {
            for ($j = 0; $j < count($headers) - 1; $j++) {
                $usersMapping[$headers[$j]] = $usersArray[$i][$j];
            }
        }
        dd($usersMapping);
        return '';
    }

}