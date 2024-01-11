<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSettingRequest;
use App\Http\Requests\UpdateImageRequest;
use App\Http\Requests\UpdateSettingRequest;
use App\Http\Resources\SettingResource;
use App\Repository\Interfaces\SettingRepositoryInterface;
use App\Services\FileService;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct(private SettingRepositoryInterface $settingRepository, private FileService $fileService)
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        try{
            //pagination request true or false
            $paginate = Request()->paginate ?? true;

            //count of pagination per page
            $count = Request()->count ?? 10;

            $settings = $this->settingRepository->all($count , $paginate);
            return SettingResource::collection($settings);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function store(StoreSettingRequest $request)
    {
        try{
            $data = $request->validated();
            $this->settingRepository->create($data);
            return response()->json(['message' => 'Setting Created Successfully']);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function show($id)
    {
        try{
            $setting = $this->settingRepository->find($id);
            return SettingResource::make($setting);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function update(UpdateSettingRequest $request , $id)
    {
        try{
            $data = $request->validated();
            $model = $this->settingRepository->find($id);
            $this->settingRepository->update($model , $data);
            return response()->json(['message' => 'Setting Updated Successfully']);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function destroy($id)
    {
        try{
            $this->settingRepository->delete($id);
            return response()->json(['message' => 'Setting Deleted Successfully']);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function updateImageSettings(UpdateImageRequest $request)
    {
        try{
            $data = $request->validated();
            $model = $this->settingRepository->findByKey($data['key']);

            if($request->has('image')){
                $fileName = $this->fileService->updateFileSettings($data['image'] , $model);
                $data['image'] = $fileName;

                $this->settingRepository->update($model , ['image' => $data['image']]);
            }
            return response()->json(['message' => 'Setting Updated Successfully']);
        }catch(\Exception $e){
            return $e;
        }
    }
}
