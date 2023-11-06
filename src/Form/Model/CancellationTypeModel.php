<?php

namespace App\Form\Model;

class CancellationTypeModel
{
//est-ce que je dois vraiment remettre attributs-là ?
    private string $motif;


    public function getMotif(): string
    {
        return $this->motif;
    }

    public function setMotif(string $motif):void
    {
        $this->motif = $motif;
    }


}//fin class