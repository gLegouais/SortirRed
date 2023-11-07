<?php

namespace App\Form\Model;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mime\Part\File;

class UploadUsersTypeModel
{
    private UploadedFile $csv;

    public function getCsv(): UploadedFile
    {
        return $this->csv;
    }

    public function setCsv(UploadedFile $csv): void
    {
        $this->csv = $csv;
    }


}