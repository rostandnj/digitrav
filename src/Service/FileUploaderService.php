<?php
/**
 * Created by PhpStorm.
 * User: rostandnj
 * Date: 3/4/19
 * Time: 10:55 AM
 */

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FileUploaderService
{
    private $targetDirectory;

    public function __construct(ContainerInterface $c)
    {
        $this->targetDirectory = $c->get("kernel")->getProjectDir()."/public/assets/uploads";
    }

    public function upload(UploadedFile $file)
    {
        if($file =="")
        {
            $data["statut"]=false;
            $data["code"]=401;
            $data["message"]="file_size_exceed";

            return $data;
        }
        else
        {
            $fileName = md5(uniqid()).'.'.$file->guessExtension();

            $size = $file->getSize();
            $on = $file->getClientOriginalName();
            $ext = $file->getExtension();
            $mime = $file->getMimeType();

            if($size >=2097152)
            {
                $data["statut"]=false;
                $data["code"]=401;
                $data["message"]="file_size_exceed";

                return $data;
            }

            try {
                $file->move($this->getTargetDirectory(), $fileName);
            } catch (FileException $e) {
                $data["statut"]=false;
                $data["code"]=401;
                $data["message"]=$e->getMessage();

                return $data;
            }

            $data["statut"]=true;
            $data["code"]=201;
            $data["file"]=["path"=>$fileName,"size"=>$size,"name"=>$on,"type"=>$mime,
                "extension"=>".".$ext];
            $data["message"]="";

            return $data;
        }


    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }
}