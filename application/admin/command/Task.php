<?php

namespace app\admin\command;


use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;


class Task extends Command
{
    protected function configure(){
        $this->setName('Task')->setDescription('测试');
    }

    protected function execute(Input $input, Output $output)
    {
        $this->xgActualNumber();
        $output->writeln('success');
    }

    //修改实际源语数量
    public function xgActualNumber(){

        $time = time();
        $time = date('Ymd',$time);

        $list = Db::name('pj_daily_progress_tr_re')
            ->where('Work_Date','=',$time)
            ->where('delete_time',0)
            ->field(['id','Filing_Code','Job_Name','Actual_Source_Text_Count'])
            ->select();
        foreach($list as $key=>$val){
            $number = Db::name('pj_project_profile')
                ->where('Filing_Code',$val['Filing_Code'])
                ->where('Job_Name',$val['Job_Name'])
                ->value('Actual_Source_Text_Count');

            $up_data = [
                'Actual_Source_Text_Count' => $number,
            ];

            $res = Db::name('pj_daily_progress_tr_re')->where('id',$val['id'])->update($up_data);

        }

    }
}