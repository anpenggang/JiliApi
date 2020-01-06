<?php
/**
 * Created by IntelliJ IDEA.
 * User: yuanguohong
 * Date: 2019/4/22
 * Time: 16:43
 */

namespace app\common\controller;


use think\Controller;
use PhpOffice\PhpSpreadsheet as PhpExcel;
use think\facade\Request;

class Upload extends Controller
{

    // 单图多图上传
    public function upload($name = ""){


        $fileNames = [];
        $files = \request()->file();


        return $fileNames;
    }

    public function uploadFile($fileName){

        $file = $this->request->file($fileName);
        if(empty($file)){
            return [];
        }
        $filePath = './upload/license';
        $filePaths = '/upload/license';
        $fileUp = $file->move($filePath);
        $data = [
            'size' =>$fileUp->getSize(),
            'fileName' => $fileUp->getFilename(),
            'saveName' => $fileUp->getSaveName(),
            'url' => $filePaths.$fileUp->getSaveName(),
        ];

        return $data;
    }

    // excel导入
    public function importExcel($file = '' , $sheet = 0){
        try{

            if (empty($file) OR !file_exists($file)) {
                throw new \Exception('文件不存在!');
            }

            /** @var Xlsx $objRead */
            $objRead = PhpExcel\IOFactory::createReader('Xlsx');

            if (!$objRead->canRead($file)) {
                /** @var Xls $objRead */
                $objRead = PhpExcel\IOFactory::createReader('Xls');

                if (!$objRead->canRead($file)) {
                    throw new \Exception('只支持导入Excel文件！');
                }
            }

            $objRead->setReadDataOnly(true);
            $obj = $objRead->load($file);

            /* 获取指定的sheet表 */
            $currSheet = $obj->getSheet($sheet);

            $arrayCurrSheet = $currSheet->toArray();

            return $arrayCurrSheet;

        }catch (\Exception $exception){
            return [];
        }
    }

    // excel 导出
    public function exportExcel($head=[] , $filename="" , $data=[]){
        try{
            $excel = new PhpExcel\Spreadsheet();

            // 添加Excel表头
            $i = 1;
            foreach ($head as $key => $value){
                $excel->getActiveSheet()->getColumnDimension($value['col'])->setAutoSize(true); // 设置宽度为自适应
                $excel->setActiveSheetIndex(0)->setCellValueExplicit($value['col'].$i , $value['name'] , "s");
            }
            // 添加数据
            $i = 2;
            foreach ($data as $keyS => $valueS){
                foreach ($head as $key => $value){
                    $excel->getActiveSheet()->setCellValueExplicit($value['col'].$i , $valueS[$value['type']] , "s");
                }
                $i++;
            }

            // 下载excel
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'.$filename.'"');
            header('Cache-Control: max-age=0');

            $writer = PhpExcel\IOFactory::createWriter($excel , 'Xlsx');
            $writer->save('php://output');

        }catch (\Exception $e){
            echo "下载失败！";
            print_r($e);
        }
    }


}