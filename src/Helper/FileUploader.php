<?php

namespace App\Helper;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    public function __construct(private ParameterBagInterface $parameterBag, private SluggerInterface $slugger) {}


public function uploadPhoto(UploadedFile $file, string $name, string $chemin): string
{

$dir = $this->parameterBag->get($chemin);
$name = $this->slugger->slug(strtolower($name)) . '-' . uniqid() . '.' . $file->guessExtension();
$file->move($dir, $name);

return $name;
}



}