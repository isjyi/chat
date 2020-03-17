<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FileController extends Controller
{
    /**
     * @param Request $request
     * @return mixed
     */
    public function upload(Request $request)
    {
        $file = $request->file('file');
        $type = $request->input('type');
        $path = $request->input('path') ?? '';
        $path = 'uploads/'.$path.'/'.date('Ymd').'/';
        if (!$file) {
            return $this->json(500,'请选择上传的文件');
        }
        if (!$file->isValid()) {
            return $this->json(500,'文件验证失败！');
        }
        $size = $file->getSize();
        if($size > 1024 * 1024 * 5 ){
            return $this->json(500,'图片不能大于5M！');
        }
        if ($type != 'im_path') {
            $ext = $file->getClientOriginalExtension();     // 扩展名
            if(!in_array($ext,['png','jpg','gif','jpeg','pem','ico']))
            {
                return $this->json(500,'文件类型不正确！');
            }
        }
        $filename = uniqid() . '.' . $ext;
        $res = $file->move(base_path('public/'.$path), $filename);
        if($res){
            $data = ['src'=>$path.$filename];
            if ($type == 'im_path') {
                $data['name'] = $file->getFilename();
            }
            return $this->json(0,'上传成功',$data);
        }else{
            return $this->json(500,'上传失败！');
        }
    }
}
