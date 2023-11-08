<?php

namespace App\Form\Model;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mime\Part\File;

class UploadUsersTypeModel
{
    private UploadedFile $csvFileName;

    public function getCsv(): UploadedFile
    {
        return $this->csvFileName;
    }

    public function setCsv(UploadedFile $csvFileName): void
    {
        $this->csvFileName = $csvFileName;
    }


}