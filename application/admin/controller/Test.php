<?php
/**
 * Created by IntelliJ IDEA.
 * User: yuanguohong
 * Date: 2019/4/22
 * Time: 11:49
 */

namespace app\admin\controller;


use app\admin\model\Admin;
use app\common\controller\Upload;
use think\Controller;
use think\Db;
use \think\facade\Request;

class Test extends Controller
{
    // 获取管理员列表
    public function getList(){
        try{

            $current = Request::param('current' , 1);
            $pageSize = Request::param('pageSize' , 20);
            $phone = Request::param('phone');

            $current = $current>=1?$current:1;
            $where = [];
            $start = ($current-1) * $pageSize;
            $data = Admin::queryData($where , $start , $pageSize , ['create_time' => 'desc']);
            $total = Admin::total($where);
            return json([
                'data' => [
                    'list' => $data,
                    'pagination' => [
                        'current' => $current ,
                        'pageSize' => $pageSize ,
                        'total' => $total
                    ]
                ],
                'status' => 'ok',
                'message' => '成功'
            ]);
        }catch (\Exception $e){
            return json(['error'=>$e->getMessage() , 'status' => 'fail']);
        }
    }

    public function createTargetData(){
        $data = Db::table('yunwu_dance_target')->where('id',">=",1)->select()->toArray();

        foreach ($data as $key => $value){
            Db::table('dance_score')->insert([
                'dance_id'=>$value['dance_id'],
                'grade_id'=>$value['grade_id'],
                'assess_id'=>$value['assess_id'],
                'target_id'=>$value['id'],
                'target_id'=>$value['id'],
                'create_time'=>date("Y-m-d H:i:s")
            ]);
        }
    }


    // 更新评定分数
    public function createAssessData(){

        $grade = [1,2,3,4,5,6,7,8,9,10,11,12];
        $gradeA = [1,2,3];
        $gradeB = [4,5,6];
        $gradeC = [7,8,9];
        $gradeD = [10,11,12];
        $scoreA = [3=>20,4=>20,5=>0,6=>0,7=>20,9=>5,10=>0,11=>5,13=>10,14=>0,15=>0,17=>10,18=>0,19=>0,21=>0,22=>10,23=>0];
        $scoreB = [3=>10,4=>10,5=>0,6=>5,7=>5,9=>10,10=>5,11=>5,13=>10,14=>10,15=>0,17=>10,18=>0,19=>5,21=>5,22=>5,23=>5];
        $scoreC = [3=>0,4=>10,5=>10,6=>5,7=>0,9=>5,10=>10,11=>5,13=>5,14=>10,15=>5,17=>5,18=>10,19=>5,21=>5,22=>5,23=>5];
        $scoreD = [3=>0,4=>5,5=>10,6=>5,7=>0,9=>5,10=>5,11=>10,13=>0,14=>0,15=>10,17=>10,18=>10,19=>10,21=>10,22=>5,23=>5];

        foreach ($grade as $keyg => $valueg){

            if(in_array($valueg , $gradeA)){
                $this->updateTarget($valueg , $scoreA);
            }
            if(in_array($valueg , $gradeB)){
                $this->updateTarget($valueg , $scoreB);
            }
            if(in_array($valueg , $gradeC)){
                $this->updateTarget($valueg , $scoreC);
            }
            if(in_array($valueg , $gradeD)){
                $this->updateTarget($valueg , $scoreD);
            }
        }
    }

    protected function updateTarget($grade = 0 , $score){

        foreach ($score as $key => $value){
            $name = "";
            switch ($key){
                case 3:$name="体态";break;
                case 4:$name="动作";break;
                case 5:$name="风格";break;
                case 6:$name="表演";break;
                case 7:$name="节奏";break;

                case 9:$name="主题";break;
                case 10:$name="结构";break;
                case 11:$name="即兴";break;

                case 13:$name="讨论";break;
                case 14:$name="表达";break;
                case 15:$name="创造";break;

                case 17:$name="鉴赏";break;
                case 18:$name="批判";break;
                case 19:$name="创新";break;

                case 21:$name="规划";break;
                case 22:$name="实践";break;
                case 23:$name="分享";break;
            }
//            Db::table("yunwu_dance_target")
//                ->where([['grade_id','=',$grade] , ['name' , '=' , $name]])
//                ->update(['score'=>$score[$key]]);

            Db::table("yunwu_dance_target");
        }
    }


    // 创建评定数据
    public function createAssess(){
        $gradeCount = 12;
        $data = [];
        for($i=1 ; $i<=$gradeCount ; $i++){
            $data = [
                ['grade_id' => $i, 'name'=>2, 'type'=>1],
                ['grade_id' => $i, 'name'=>8, 'type'=>2],
                ['grade_id' => $i, 'name'=>12, 'type'=>2],
                ['grade_id' => $i, 'name'=>16, 'type'=>2],
                ['grade_id' => $i, 'name'=>20, 'type'=>2]
            ];

            Db::table("yunwu_dance_assess")
                ->insertAll($data);
        }
    }

    // 创建指标数据
    public function createTarget(){

        $gradeA = [1,2,3];
        $gradeB = [4,5,6];
        $gradeC = [7,8,9];
        $gradeD = [10,11,12];
        $scoreA = [3=>20,4=>20,5=>0,6=>0,7=>20,9=>5,10=>0,11=>5,13=>10,14=>0,15=>0,17=>10,18=>0,19=>0,21=>0,22=>10,23=>0];
        $scoreB = [3=>10,4=>10,5=>0,6=>5,7=>5,9=>10,10=>5,11=>5,13=>10,14=>10,15=>0,17=>10,18=>0,19=>5,21=>5,22=>5,23=>5];
        $scoreC = [3=>0,4=>10,5=>10,6=>5,7=>0,9=>5,10=>10,11=>5,13=>5,14=>10,15=>5,17=>5,18=>10,19=>5,21=>5,22=>5,23=>5];
        $scoreD = [3=>0,4=>5,5=>10,6=>5,7=>0,9=>5,10=>5,11=>10,13=>0,14=>0,15=>10,17=>10,18=>10,19=>10,21=>10,22=>5,23=>5];
        $data = Db::table("yunwu_dance_assess")->where('is_delete','=',0)->select();

        foreach ($data as $key => $value){
            if(in_array($value['grade_id'] , $gradeA)){
                $score = $scoreA;
                if($value['name']==2){
                    $i = 2;
                    $data = [
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]],
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]],
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]],
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]],
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]]
                    ];
                }
                if($value['name']==8){
                    $i = 8;
                    $data = [
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]],
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]],
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]]
                    ];
                }
                if($value['name']==12){
                    $i = 12;
                    $data = [
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]],
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]],
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]]
                    ];
                }
                if($value['name']==16){
                    $i = 16;
                    $data = [
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]],
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]],
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]]
                    ];
                }
                if($value['name']==20){
                    $i = 20;
                    $data = [
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]],
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]],
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]]
                    ];
                }
            }
            if(in_array($value['grade_id'] , $gradeB)){
                $score = $scoreB;
                if($value['name']==2){
                    $i = 2;
                    $data = [
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]],
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]],
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]],
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]],
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]]
                    ];
                }
                if($value['name']==8){
                    $i = 8;
                    $data = [
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]],
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]],
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]]
                    ];
                }
                if($value['name']==12){
                    $i = 12;
                    $data = [
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]],
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]],
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]]
                    ];
                }
                if($value['name']==16){
                    $i = 16;
                    $data = [
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]],
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]],
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]]
                    ];
                }
                if($value['name']==20){
                    $i = 20;
                    $data = [
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]],
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]],
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]]
                    ];
                }
            }
            if(in_array($value['grade_id'] , $gradeC)){
                $score = $scoreC;
                if($value['name']==2){
                    $i = 2;
                    $data = [
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]],
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]],
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]],
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]],
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]]
                    ];
                }
                if($value['name']==8){
                    $i = 8;
                    $data = [
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]],
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]],
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]]
                    ];
                }
                if($value['name']==12){
                    $i = 12;
                    $data = [
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]],
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]],
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]]
                    ];
                }
                if($value['name']==16){
                    $i = 16;
                    $data = [
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]],
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]],
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]]
                    ];
                }
                if($value['name']==20){
                    $i = 20;
                    $data = [
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]],
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]],
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]]
                    ];
                }
            }
            if(in_array($value['grade_id'] , $gradeD)){
                $score = $scoreD;
                if($value['name']==2){
                    $i = 2;
                    $data = [
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]],
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]],
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]],
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]],
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]]
                    ];
                }
                if($value['name']==8){
                    $i = 8;
                    $data = [
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]],
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]],
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]]
                    ];
                }
                if($value['name']==12){
                    $i = 12;
                    $data = [
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]],
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]],
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]]
                    ];
                }
                if($value['name']==16){
                    $i = 16;
                    $data = [
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]],
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]],
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]]
                    ];
                }
                if($value['name']==20){
                    $i = 20;
                    $data = [
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]],
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]],
                        ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['id'] , 'name'=>++$i , 'score'=>$score[$i]]
                    ];
                }
            }

            if(!empty($data)){
                Db::table("yunwu_dance_target")
                    ->insertAll($data);
            }
        }
    }

    // 创建名称数据
    public function createName(){

        $i = 1;
        Db::table("yunwu_assess_target_name")
            ->insertAll([
               ['id'=>++$i , 'name'=>'技能形成'],
               ['id'=>++$i , 'name'=>'体态'],
               ['id'=>++$i , 'name'=>'动作'],
               ['id'=>++$i , 'name'=>'风格'],
               ['id'=>++$i , 'name'=>'表演'],
               ['id'=>++$i , 'name'=>'节奏'],
               ['id'=>++$i , 'name'=>'知识运用'],
               ['id'=>++$i , 'name'=>'主题'],
               ['id'=>++$i , 'name'=>'结构'],
               ['id'=>++$i , 'name'=>'即兴'],
               ['id'=>++$i , 'name'=>'交流创作'],
               ['id'=>++$i , 'name'=>'讨论'],
               ['id'=>++$i , 'name'=>'表达'],
               ['id'=>++$i , 'name'=>'创造'],
               ['id'=>++$i , 'name'=>'思维培养'],
               ['id'=>++$i , 'name'=>'鉴赏'],
               ['id'=>++$i , 'name'=>'批判'],
               ['id'=>++$i , 'name'=>'创新'],
               ['id'=>++$i , 'name'=>'行为养成'],
               ['id'=>++$i , 'name'=>'规划'],
               ['id'=>++$i , 'name'=>'实践'],
               ['id'=>++$i , 'name'=>'分享']
            ]);

    }

    // 分值计算器
    public function createScore(){

        $data = Db::table("yunwu_dance_target")->select();

        foreach ($data as $key => $value){
            if($value['score'] == 20){
                $data = [
                    ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['assess_id'] , 'target_id'=>$value['id'] , 'order_num'=>1 , 'min_score'=>18,'max_score'=>20,'describe'=>'优秀'],
                    ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['assess_id'] , 'target_id'=>$value['id'] , 'order_num'=>2 , 'min_score'=>16,'max_score'=>18,'describe'=>'优良'],
                    ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['assess_id'] , 'target_id'=>$value['id'] , 'order_num'=>3 , 'min_score'=>12,'max_score'=>16,'describe'=>'一般'],
                    ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['assess_id'] , 'target_id'=>$value['id'] , 'order_num'=>4 , 'min_score'=>0,'max_score'=>12,'describe'=>'极差'],
                ];
            }
            if($value['score'] == 10){
                $data = [
                    ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['assess_id'] , 'target_id'=>$value['id'] , 'order_num'=>1 , 'min_score'=>9,'max_score'=>10,'describe'=>'优秀'],
                    ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['assess_id'] , 'target_id'=>$value['id'] , 'order_num'=>2 , 'min_score'=>8,'max_score'=>9,'describe'=>'优良'],
                    ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['assess_id'] , 'target_id'=>$value['id'] , 'order_num'=>3 , 'min_score'=>6,'max_score'=>8,'describe'=>'一般'],
                    ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['assess_id'] , 'target_id'=>$value['id'] , 'order_num'=>4 , 'min_score'=>0,'max_score'=>6,'describe'=>'极差'],
                ];
            }
            if($value['score'] == 5){
                $data = [
                    ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['assess_id'] , 'target_id'=>$value['id'] , 'order_num'=>1 , 'min_score'=>4,'max_score'=>5,'describe'=>'优秀'],
                    ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['assess_id'] , 'target_id'=>$value['id'] , 'order_num'=>2 , 'min_score'=>3,'max_score'=>4,'describe'=>'优良'],
                    ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['assess_id'] , 'target_id'=>$value['id'] , 'order_num'=>3 , 'min_score'=>2,'max_score'=>3,'describe'=>'一般'],
                    ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['assess_id'] , 'target_id'=>$value['id'] , 'order_num'=>4 , 'min_score'=>0,'max_score'=>2,'describe'=>'极差'],
                ];
            }
            if($value['score'] == 0){
                $data = [
                    ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['assess_id'] , 'target_id'=>$value['id'] , 'order_num'=>1 , 'min_score'=>0,'max_score'=>0,'describe'=>'优秀'],
                    ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['assess_id'] , 'target_id'=>$value['id'] , 'order_num'=>2 , 'min_score'=>0,'max_score'=>0,'describe'=>'优良'],
                    ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['assess_id'] , 'target_id'=>$value['id'] , 'order_num'=>3 , 'min_score'=>0,'max_score'=>0,'describe'=>'一般'],
                    ['grade_id'=>$value['grade_id'] , 'assess_id'=>$value['assess_id'] , 'target_id'=>$value['id'] , 'order_num'=>4 , 'min_score'=>0,'max_score'=>0,'describe'=>'极差'],
                ];
            }

            if(!empty($data)){
                Db::table("yunwu_dance_score")->insertAll($data);
            }
        }

    }

    public function test(){

        $jsonArray = json_encode(['id'=>2 , 'time'=>time()]);
        $userToken = \Encode::encrypt($jsonArray);
        print_r($userToken);die;

        $ret = Db::table('bookshare_book')
            ->alias('b')
            ->join('bookshare_book_class c' , 'c.id=b.class_id and c.id=1')
            ->field("b.*,c.name as class_name")
            ->select();
        print_r($ret);
    }


}