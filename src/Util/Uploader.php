<?php

namespace App\Util;

use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class Uploader
{
    public function __construct(
        private readOnly SluggerInterface $slugger,
        private readOnly ContainerBagInterface $containerBag,
    ){}


    public function upload(uploadedFile $uploadedImage): string
    {
        if($uploadedImage) {
            $originalFilename = pathinfo($uploadedImage->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $this->slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$uploadedImage->guessExtension();

            try
            {
                $uploadedImage->move($this->containerBag->get('upload_directory'), $newFilename);
                return $newFilename;
            } catch (FileException $exception)
            {
                dd($exception);
            }
        }
    }
}