<?php

namespace App\Services;


class FileService {

    public function storeFile($file)
    {
        try{
            $fileName = time().rand(1,99).'.'.$file->extension();
            $file->move(public_path('uploads'), $fileName);
            return $fileName;
        }catch(\Exception $e){
            return $e;
        }
    }

    public function updateFile($file , $product)
    {
        try{
            if($product->image == null){
                $fileName = time().rand(1,99).'.'.$file->extension();
                $file->move(public_path('uploads'), $fileName);
                return $fileName;
            }else{
                if($product){
                    unlink("uploads/".$product->image);
                }
                $fileName = time().rand(1,99).'.'.$file->extension();
                $file->move(public_path('uploads'), $fileName);
                return $fileName;
            }

        }catch(\Exception $e){
            return $e;
        }
    }

    public function deleteFileFromUploads($id , $productRepository)
    {
        try{
            $model = $productRepository->find($id , []);
            if($model){
                unlink("uploads/".$model->image);
            }
        }catch(\Exception $e){
            return $e;
        }
    }

}
