<?php

namespace Lake\FormMedia\Http\Controllers;

use Illuminate\Routing\Controller;

use Lake\FormMedia\MediaManager;

class FormMedia extends Controller
{
    /**
     * 获取文件
     */
    public function getList()
    {
        $path = request()->input('path', '/');
        
        $currentPage = (int) request()->input('page', 1);
        $perPage = (int) request()->input('pageSize', 120);
        
        $manager = (new MediaManager())
            ->setPath($path);
        
        $files = $manager->ls();
        $list = collect($files)
            ->slice(($currentPage - 1) * $perPage, $perPage)
            ->values();
            
        $totalPage = count(collect($files)->chunk($perPage));

        $data = [
            'list' => $list, // 数据
            'total_page' => $totalPage, // 数量
            'current_page' => $currentPage, // 当前页码
            'per_page' => $perPage, // 每页数量
            'nav' => $manager->navigation()  // 导航
        ];
        
        return $this->renderJson('获取成功', 200, $data);
    }

    /**
     * 上传
     */
    public function upload()
    {
        $files = request()->file('files');
        $path = request()->get('path', '/');
        
        $type = request()->get('type');
        $nametype = request()->get('nametype', 'uniqid');
        
        $manager = (new MediaManager())
            ->setPath($path)
            ->setNametype($nametype);
        
        if ($type != 'blend') {
            if (! $manager->checkType($files, $type)) {
                return $this->renderJson('上传文件格式不被允许', -1);
            }
        }
        
        try {
            if ($manager->upload($files)) {
                return $this->renderJson('上传成功', 200);
            }
        } catch (\Exception $e) {
            return $this->renderJson('上传失败', -1);
        }
        
        return $this->renderJson('上传失败', -1);
    }

    /**
     * 新建文件夹
     */
    public function newFolder()
    {
        $dir = request()->input('dir');
        $name = request()->input('name');

        $manager = (new MediaManager())
            ->setPath($dir);

        try {
            if ($manager->newFolder($name)) {
                return $this->renderJson('创建成功', 200);
            }
        } catch (\Exception $e) {
            return $this->renderJson('创建失败', -1);
        }
        
        return $this->renderJson('创建失败', -1);
    }
    
    /**
     * 输出json
     */
    protected function renderJson($msg, $code = 200, $data = [])
    {
        return response()->json([
            'code' => $code, 
            'msg' => $msg,
            'data' => $data,
        ]);
    }
}



