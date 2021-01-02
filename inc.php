<?php
class inc
{
    public function  __construct(){

        ini_set('display_errors', 'On');
        //检测是否传入信息
        if(empty($_GET['time'])){
            //导入模板
            include './web.html';
            die;
        }else if(!preg_match("/^\d{10}$/",$_GET['time'])){
            echo '<img src="./public/img/what'.rand(1,4).'.jpg" width="100%">';die;
        }

        $get_date = $this->check();

        $time = $get_date['time'];
        $bus_direction = $get_date['bus_direction'];
        $jr_direction = $get_date['jr_direction'];
        $jr_switch = $get_date['jr_switch'];
        $date = $this->search($time,$bus_direction,$jr_direction,$jr_switch);
        $date = $this->template($date,$bus_direction,$jr_switch);
        // $this->save_date($get_date);
        echo $date;

    }

    /**
     *　GETから入れたDATEをチェックする。
     */
    private function check()
    {
        if(!isset($_GET['user_id']) || !preg_match("/^\d{8}$/",$_GET['user_id'])) $_GET['user_id'] = '00000001';
        if(!isset($_GET['time']) || !preg_match("/^\d{10}$/",$_GET['time'])) $_GET['time'] = time();
        if(!isset($_GET['bus_direction']) || !preg_match("/^\d{1}$/",$_GET['bus_direction'])) $_GET['bus_direction'] = 0;
        if(!isset($_GET['jr_direction']) || !preg_match("/^\d{1}$/",$_GET['jr_direction'])) $_GET['jr_direction'] = 0;
        if(!isset($_GET['jr_switch']) || !preg_match("/^\d{1}$/",$_GET['jr_switch'])) $_GET['jr_switch'] = 1;
        $user_id = $_GET['user_id'];
        $time = $_GET['time'];
        $bus_direction = $_GET['bus_direction'];
        $jr_direction = $_GET['jr_direction'];
        $jr_switch = $_GET['jr_switch'];

        return array(
                'user_id' => $user_id,
                'time' => $time,
                'bus_direction' => $bus_direction,
                'jr_direction' => $jr_direction,
                'jr_switch' => $jr_switch
        );
    }

    public function save_date($get_date)
    {

        $con = @mysqli_connect('localhost' , 'root' , '578647');
        if (!$con)die('Could not connect: ' . mysql_error());
        mysqli_select_db($con,"shibaura_bus");
        $sql = "INSERT INTO bus (user_id, search_time, bus_direction , jr_direction , jr , time) 
VALUES (".$get_date['user_id'].", ".$get_date['time']." , ".$get_date['bus_direction']." ,
".$get_date['jr_direction']." , ".$get_date['jr_switch']." , ".time().')';
//        echo $sql;
        mysqli_query($con,$sql);



        mysqli_close($con);
    }

    public function update_date()
    {
        $date = $this ->jopen('web_page_json.json');
//        print_r($date);
        $str = $this->update($date);
        print_r(json_encode($str,true));
    }

    public function template($date, $bus_direction,$jr_switch)
    {
        $tpl = '';
        if (!empty($date) && $bus_direction == 0){
            foreach ($date as $k => $v) {
                $ok = array_key_exists('ok', $v) ? 'ok' : '';
                $tpl .= '<div class="result '.$ok.'">
                           <div class="border">';
                if(strlen($k) == 4){
                    $tpl .= '<div>'.$this->b_time($k).'</div>';
                    $tpl .= '<div>
                               <span class="icon"><img src="./public/icon/school-building.png" class="arrowhead" /></span>
                             </div>';
                }else{
                    $tpl .= '<div>
                               <span>'.$this->b_time(explode('~',$k)[0]).'</span><br>
                               <span>　︴</span><br>
                               <span>'.$this->b_time(explode('~',$k)[1]).'</span><br>
                             </div>';
                    $tpl .= '<div>
                               <span class="icon"><img src="./public/icon/school-building.png" class="arrowhead" /></span>
                             </div>';
                }
                $tpl .=     '<div>
                               <span><img src="public/img/Arrow-OutRight-icon.png" class="arrowhead"></span>
                             </div>
                             <div>' .$this->plus($k). '</div>
                             <div>
                               <span class="icon"><img src="./public/icon/stop-clipart-emoji-13.png" class="arrowhead" /></span>
                             </div>';
                if($jr_switch){
                    $tpl .= '<div>
                               <span><img src="public/img/Arrow-OutRight-icon.png" class="arrowhead"></span>
                             </div>
                             <div>';
                    foreach($v as $kk => $vv){
                        if($vv != 'ok'){
                            $tip = $this -> utf8_str_split($vv, 1)[4];
                            $tpl .= $this -> b_time($vv) . '<span class="label">' . $tip . '</span><br />';
                        }
                    }
                    $tpl .= '</div>';
                }

                $tpl .=    '</div>
                         </div>';
            }
        }else if(!empty($date) && $bus_direction == 1){
            foreach ($date as $k => $v) {
                $ok = array_key_exists('ok', $v) ? 'ok' : '';
                $tpl .= '<div class="result '.$ok.'">
                           <div class="border">';
                if($jr_switch){
                    $tpl .= '<div>';
                    foreach($v as $kk => $vv){
                        if($vv != 'ok'){
                            $tip = $this -> utf8_str_split($vv, 1)[4];
                            $vv = substr($vv, 0, 4);
                            $tpl .= $this -> b_time($vv) . '<span class="label">' . $tip . '</span><br />';
                        }
                    }
                    $tpl .= '</div>
                             <div>
                               <span><img src="public/img/Arrow-OutRight-icon.png" class="arrowhead"></span>
                             </div>';
                }
                $tpl .=     '<div>';
                if(strlen($k) == 4){
                    $tpl .=   '<span>'.$this->b_time($k).'</span><br>';
                }else if(substr($k,4,1) == '~'){
                    $tpl .=   '<span>'.$this->b_time(explode('~',$k)[0]).'</span><br>
                               <span>　︴</span><br>
                               <span>'.$this->b_time(explode('~',$k)[1]).'</span><br>';
                }else if(substr($k,4,1) == ','){
                    foreach(explode(',',$k) as $kk => $vv){
                        $tpl .=   '<span>'.$this->b_time($vv).'</span><br>';
                    }
                }
                $tpl .=     '</div>
                             <div>
                               <span class="icon"><img src="./public/icon/stop-clipart-emoji-13.png" class="arrowhead" /></span>
                             </div>
                             <div>
                               <span><img src="public/img/Arrow-OutRight-icon.png" class="arrowhead"></span>
                             </div>';
                if(strlen($k) == 4){
                    $tpl .= '<div class="gofungo">'.$this->plus($k).'</div>';
                }else if(substr($k,4,1) == ','){
                    $tpl .= '<div class="gofungo">';
                    foreach(explode(',',$k) as $kk => $vv){
                        $tpl .= $this->plus($vv).'<br>';
                    }
                    $tpl .= '</div>';
                }else{
                    $tpl .= '<div class="gofungo">約5分後</div>';
                }
                $tpl .=     '<div>
                               <span class="icon"><img src="./public/icon/school-building.png" class="arrowhead" /></span>
                             </div>';

                $tpl .=   '</div>
                         </div>';
            }
        }else{
            $tpl = 'none';
        }
        return $tpl;
    }

    /**
     * @param $time
     * @param int $bus_direction Bus方向
     *                           0 -> 学校から駅
     *                           1 -> 駅から学校
     * @param int $jr_direction  JR時刻表方向
     *                           0 -> 東大宮　〜　大宮・上野・新宿
     *                           1 -> 東大宮　〜　宇都宮・黒磯
     * @param int $jr            JR時刻表表示
     *                           0 -> ON
     *                           1 -> OFF
     * @return mixed
     */
    public function search($time, $bus_direction, $jr_direction, $jr_switch)
    {
        $bus_json = $this->jopen();

        $bus_direction = $bus_direction == 0 ? 'school_train' : 'train_school';
        $mon = date('m',$time);
        $day = date('d',$time);
        $hour = $this->hour(date('H',$time));
        $min = date('i',$time);

        if(!array_key_exists($mon,$bus_json['calendar'])){
            return false;die;
        }

        if($bus_direction == 'school_train'){
            $jr_direction = $jr_direction == 0 ? 'jr_nobori' : 'jr_kudari';
            $day_date = $bus_json['calendar'][$mon][$day][$bus_direction];
            $jr = $bus_json['calendar'][$mon][$day][$jr_direction];
            $day_date_arr = array();
//            echo '<pre>';print_r($day_date);die;
            foreach ($day_date as $k => $v){
                if(strlen($v) == 4){
                    $day_date_arr[$v] = array();
                    if($jr_switch){
                        foreach ($jr as $kk => $vv){
                            if(strlen($vv) == 5){
                                $tip = $this->train_tip_change($jr_direction,substr($vv,2,1));
                                $vv = substr($vv,0,2).substr($vv,3,2);
                            }else{
                                $tip = $this->train_tip_change($jr_direction,'default');
                                $vv = substr($vv,0,4);
                            }
                            //                        echo $this->plus($day_date[$k+1],5,false);
                            if($this->plus($v,5,false) <= $vv && @$this->plus($day_date[$k+1],5,false) > $vv){
                                $day_date_arr[$v][] = $vv.$tip;
                            }else if(end($day_date) == $v && $this->plus($v,5,false) <= $vv){
                                $day_date_arr[$v][] = $vv.$tip;
                                break;
                            }
                        }
                    }
                }else if(strlen($v) == 5 && substr($v,4,1) == '~'){
//                    if($jr_switch){
                    foreach($jr as $kk => $vv){
                        if(strlen($vv) == 5){
                            $tip = $this -> train_tip_change($jr_direction, substr($vv, 2, 1));
                            $vv = substr($vv, 0, 2) . substr($vv, 3, 2);
                        }else{
                            $tip = $this -> train_tip_change($jr_direction, 'default');
                            $vv = substr($vv, 0, 4);
                        }
                        if($this -> plus(substr($v, 0, 4), 5, false) <= $vv && $this -> plus($day_date[$k + 2], 4, false) > $vv){
                            $day_date_arr[$v . substr($day_date[$k + 1], 1, 5)][] = $vv . $tip;
                        }
                    }
//                    }
                }
            }
            if($jr_switch){
                $i = 0;
                foreach($day_date_arr as $k => $v){
                    if($i){
                        $day_date_arr[$i][] = $v[0];
                        $i = 0;
                    }
                    if(empty($v)){
                        $i = $k;
                    }
                }
            }
//            echo '<pre>';print_r($day_date_arr);die;


            foreach ($day_date_arr as $k => $v){
                //如果有 ～ 符号，删除符号
                if(strlen($k) > 4){
                    $timess = explode('~',$k);
                    if($timess[0] >= $hour.$min || $timess[1] >= $hour.$min){
                        $day_date_arr[$k]['ok'] = 'ok';
                        break;
                    }
                }else{
                    if($k >= $hour.$min){
                        $day_date_arr[$k] = $v;
                        $day_date_arr[$k]['ok'] = 'ok';
                        break;
                    }
                }
            }
        }else{
            $day_date = $bus_json['calendar'][$mon][$day][$bus_direction];
            $jr_direction = $jr_direction == 0 ? 'jr_kudari' : 'jr_nobori';
            $jr = $bus_json['calendar'][$mon][$day][$jr_direction];
            $day_date_arr = array();
//            echo '<pre>';print_r($day_date);die;
            if($jr_switch){
                foreach($jr as $k => $v){
                    if(strlen($v) == 5){
                        $tip = $this -> train_tip_change($jr_direction, substr($v, 2, 1));
                        $jr[$k] = substr($v, 0, 2) . substr($v, 3, 2) . $tip;
                    }else{
                        $tip = $this -> train_tip_change($jr_direction, 'default');
                        $jr[$k] = $v . $tip;
                    }
                }
            }
//            echo '<pre>';print_r($jr);die;
            foreach ($day_date as $k => $v){
                strlen($v) == 4 && $time = $v;
                if(strlen($v) > 4 && substr($v,4,1) == '~')
                    $time = substr($v,0,4).'~'.substr($day_date[$k+1],1,5);
                $day_date_arr[$time] = array();
            }
            $a = 1;
            if($jr_switch){
                foreach($jr as $k => $v){
                    $jr_time = substr($v, 0, 4);
                    $last = 0;
                    foreach($day_date_arr as $kk => $vv){
                        if($last){
                            strlen($last) != 4 && $last = substr($last, 5, 4);
                        }
                        if(strlen($kk) == 4 && $kk >= $jr_time && $last < $jr_time && $last != 0){
                            $day_date_arr[$kk][] = $v;
                            break;
                        }else if(strlen($kk) == 4 && $kk >= $jr_time && $last < $jr_time && $last == 0){
                            /**
                             * 把第一个信息的不用的部分去掉，留下一条信息
                             */
                            $day_date_arr[$kk] = $v;
                        }else{
                            //                        $start = substr($kk, 0, 4);
                            $stop = substr($kk, 5, 4);
                            if($jr_time > $last && $jr_time < $stop){
                                $day_date_arr[$kk] = $v;
                            }
                        }
                        $last = $kk;
                    }
                }


                foreach($day_date_arr as $k => $v){
                    if(empty($v)) $day_date_arr[$k][] = end($day_date_arr[$last]);
                    $last = $k;

                    /**
                     * 把第一个信息的不用的部分去掉，留下一条信息
                     */
                    if(!is_array($v)){
                        $day_date_arr[$k] = array($v);
                    }
                }
            }

//            echo '<pre>';print_r($day_date_arr);die;

            //检测时间，时间段内加入ok
            foreach ($day_date_arr as $k => $v){
                if(strlen($k) > 4){
                    $timess = explode('~',$k);
                    if($timess[0] >= $hour.$min || $timess[1] >= $hour.$min){
                        $day_date_arr[$k]['ok'] = 'ok';
                        break;
                    }
                }else{
                    if(substr($k,0,4) >= $hour.$min){
                        $day_date_arr[$k] = $v;
                        $day_date_arr[$k]['ok'] = 'ok';
                        break;
                    }
                }
            }

            if($jr_switch){
                /*
                 * 修复一个地铁多个bus的问题
                 * [2201,2154] => Array([0] => 2147金)
                 */
                $date = $day_date_arr;
                $day_date_arr = array();
                $last_v = 0;
                foreach($date as $k => $v){
                    if($last_v != $v[0]){
                        $day_date_arr[$k] = $v;
                    }else{
                        unset($day_date_arr[$last_k]);
                        $day_date_arr[$last_k . ',' . $k] = $v;
                    }

                    $last_v = $v[0];
                    $last_k = $k;
                }
            }

        }

//        echo '<pre>';print_r($day_date_arr);die;
        return $day_date_arr;
    }


    //open json file
    public function jopen($fname = 'busjson.json')
    {
        $fp = fopen($fname,"r");
        $str = fread($fp,filesize($fname));
        $date = json_decode($str,true);
        return $date;
    }

    /**
     * 格式化bus信息
     * @param $date
     */
    public function update($date)
    {
        $bus_date = array();
        foreach ($date['calendar'] as $k){
            $bus_date['calendar'][$k['month']] = $k;
            $bus_date['calendar'][$k['month']]['list'] = array();
            foreach ($k['list'] as $kk){
                $bus_date['calendar'][$k['month']]['list'][$kk['day']] = $kk;
                foreach ($date['timesheet'] as $kkk){
                    if($kk['ts_id'] == $kkk['ts_id']){
                        $bus_date['calendar'][$k['month']]['list'][$kk['day']] = $kkk;
                    }
                }
            }
        }

//        echo '<pre>';print_r($bus_date);die;

        $bus_times = array('time'=>time());
        foreach ($bus_date['calendar'] as $k => $v){

            $bus_times['calendar'][$k] = array();
            foreach ($v['list'] as $kk => $vv){
                $kk = $this->hour($kk);
                $bus_times['calendar'][$k][$kk]['school_train'] = array();
                $bus_times['calendar'][$k][$kk]['train_school'] = array();
                $bus_times['calendar'][$k][$kk]['jr_nobori'] = array();
                $bus_times['calendar'][$k][$kk]['jr_kudari'] = array();
                foreach (@$vv['list'] as $kkk => $vvv){
                    if(!empty($vvv['bus_right']['num1'])){
                        $time_array = explode('.',$vvv['bus_right']['num1']);
                        foreach ($time_array as $kkkk){
                            $hour = $this->hour($vvv['time']);
                            array_push($bus_times['calendar'][$k][$kk]['school_train'],$hour.$kkkk);
                        }
                    }
                    if(!empty($vvv['bus_right']['memo1'])){
                        //删除字符串空格再分割再删除空数组
                        $text = $this->utf8_str_split(str_replace(' ', '',$vvv['bus_right']['memo1']));
                        $yori = array_search('よ',$text);
                        $made = array_search('ま',$text);
                        $hour = $this->hour($vvv['time']);
                        if($yori == false && $made == false){
                            array_push($bus_times['calendar'][$k][$kk]['school_train'],$hour.'00~');
                        }
                        if($yori != false && $made == false){
                            $start = $yori == 4 ? 2 : 3;
                            $end = $yori == 4 ? 3 : 4;
                            array_push($bus_times['calendar'][$k][$kk]['school_train'],$hour.$text[$start].$text[$end].'~');
                        }
                        if($yori == false && $made != false){
                            $start = $made == 4 ? 2 : 3;
                            $end = $made == 4 ? 3 : 4;
                            array_push($bus_times['calendar'][$k][$kk]['school_train'],'~'.$hour.$text[$start].$text[$end]);
                        }
                        if($yori != false && $made != false){
                            $start = $yori == 4 ? 2 : 3;
                            $end = $yori == 4 ? 3 : 4;
                            array_push($bus_times['calendar'][$k][$kk]['school_train'],$hour.$text[$start].$text[$end].'~');
                            $start = $yori == 4 ? 9 : 10;
                            $end = $yori == 4 ? 10 : 11;
                            array_push($bus_times['calendar'][$k][$kk]['school_train'],'~'.$hour.$text[$start].$text[$end]);
                        }
                    }
                    if(!empty($vvv['bus_right']['num2'])){
                        $time_array = explode('.',$vvv['bus_right']['num2']);
                        foreach ($time_array as $kkkk){
                            $hour = $this->hour($vvv['time']);
                            array_push($bus_times['calendar'][$k][$kk]['school_train'],$hour.$kkkk);
                        }
                    }
                    if(!empty($vvv['bus_right']['memo2'])){
                        //删除字符串空格再分割再删除空数组
                        $text = $this->utf8_str_split(str_replace(' ', '',$vvv['bus_right']['memo2']));
                        $yori = array_search('よ',$text);
                        $made = array_search('ま',$text);
                        $hour = $this->hour($vvv['time']);
                        if($yori == false && $made == false){
                            array_push($bus_times['calendar'][$k][$kk]['school_train'],$hour.'00~');
                        }
                        if($yori != false && $made == false){
                            $start = $yori == 4 ? 2 : 3;
                            $end = $yori == 4 ? 3 : 4;
                            array_push($bus_times['calendar'][$k][$kk]['school_train'],$hour.$text[$start].$text[$end].'~');
                        }
                        if($yori == false && $made != false){
                            $start = $made == 4 ? 2 : 3;
                            $end = $made == 4 ? 3 : 4;
                            array_push($bus_times['calendar'][$k][$kk]['school_train'],'~'.$hour.$text[$start].$text[$end]);
                        }
                        if($yori != false && $made != false){
                            $start = $yori == 4 ? 2 : 3;
                            $end = $yori == 4 ? 3 : 4;
                            array_push($bus_times['calendar'][$k][$kk]['school_train'],$hour.$text[$start].$text[$end].'~');
                            $start = $yori == 4 ? 9 : 10;
                            $end = $yori == 4 ? 10 : 11;
                            array_push($bus_times['calendar'][$k][$kk]['school_train'],'~'.$hour.$text[$start].$text[$end]);
                        }
                    }
                    //--------------------------------------------------------------------
                    if(!empty($vvv['bus_left']['num1'])){
                        $time_array = explode('.',$vvv['bus_left']['num1']);
                        foreach ($time_array as $kkkk){
                            $hour = $this->hour($vvv['time']);
                            array_push($bus_times['calendar'][$k][$kk]['train_school'],$hour.$kkkk);
                        }
                    }
                    if(!empty($vvv['bus_left']['memo1'])){
                        //删除字符串空格再分割再删除空数组
                        $text = $this->utf8_str_split(str_replace(' ', '',$vvv['bus_left']['memo1']));
                        $yori = array_search('よ',$text);
                        $made = array_search('ま',$text);
                        $hour = $this->hour($vvv['time']);
                        if($yori == false && $made == false){
                            array_push($bus_times['calendar'][$k][$kk]['train_school'],$hour.'00~');
                        }
                        if($yori != false && $made == false){
                            $start = $yori == 4 ? 2 : 3;
                            $end = $yori == 4 ? 3 : 4;
                            array_push($bus_times['calendar'][$k][$kk]['train_school'],$hour.$text[$start].$text[$end].'~');
                        }
                        if($yori == false && $made != false){
                            $start = $made == 4 ? 2 : 3;
                            $end = $made == 4 ? 3 : 4;
                            array_push($bus_times['calendar'][$k][$kk]['train_school'],'~'.$hour.$text[$start].$text[$end]);
                        }
                        if($yori != false && $made != false){
                            $start = $yori == 4 ? 2 : 3;
                            $end = $yori == 4 ? 3 : 4;
                            array_push($bus_times['calendar'][$k][$kk]['train_school'],$hour.$text[$start].$text[$end].'~');
                            $start = $yori == 4 ? 9 : 10;
                            $end = $yori == 4 ? 10 : 11;
                            array_push($bus_times['calendar'][$k][$kk]['train_school'],'~'.$hour.$text[$start].$text[$end]);
//                            }
                        }
                    }
                    if(!empty($vvv['bus_left']['num2'])){
                        $time_array = explode('.',$vvv['bus_left']['num2']);
                        foreach ($time_array as $kkkk){
                            $hour = $this->hour($vvv['time']);
                            array_push($bus_times['calendar'][$k][$kk]['train_school'],$hour.$kkkk);
                        }
                    }
                    if(!empty($vvv['bus_left']['memo2'])){
                        //删除字符串空格再分割再删除空数组
                        $text = $this->utf8_str_split(str_replace(' ', '',$vvv['bus_left']['memo2']));
                        $yori = array_search('よ',$text);
                        $made = array_search('ま',$text);
                        $hour = $this->hour($vvv['time']);
                        if($yori == false && $made == false){
                            array_push($bus_times['calendar'][$k][$kk]['train_school'],$hour.'00~');
                        }
                        if($yori != false && $made == false){
                            $start = $yori == 4 ? 2 : 3;
                            $end = $yori == 4 ? 3 : 4;
                            array_push($bus_times['calendar'][$k][$kk]['train_school'],$hour.$text[$start].$text[$end].'~');
                        }
                        if($yori == false && $made != false){
                            $start = $made == 4 ? 2 : 3;
                            $end = $made == 4 ? 3 : 4;
                            array_push($bus_times['calendar'][$k][$kk]['train_school'],'~'.$hour.$text[$start].$text[$end]);
                        }
                        if($yori != false && $made != false){
                            $start = $yori == 4 ? 2 : 3;
                            $end = $yori == 4 ? 3 : 4;
                            array_push($bus_times['calendar'][$k][$kk]['train_school'],$hour.$text[$start].$text[$end].'~');
                            $start = $yori == 4 ? 9 : 10;
                            $end = $yori == 4 ? 10 : 11;
                            array_push($bus_times['calendar'][$k][$kk]['train_school'],'~'.$hour.$text[$start].$text[$end]);
                        }
                    }
                    //--------------------------------------------------------------------
                    if(!empty($vvv['train_right']['num1'])){
                        $time_array = explode('.',$vvv['train_right']['num1']);
                        foreach ($time_array as $kkkk){
                            $hour = $this->hour($vvv['time']);
                            array_push($bus_times['calendar'][$k][$kk]['jr_nobori'],$hour.$kkkk);
                        }
                    }
                    //--------------------------------------------------------------------
                    if(!empty($vvv['train_left']['num1'])){
                        $time_array = explode('.',$vvv['train_left']['num1']);
                        foreach ($time_array as $kkkk){
                            $hour = $this->hour($vvv['time']);
                            array_push($bus_times['calendar'][$k][$kk]['jr_kudari'],$hour.$kkkk);
                        }
                    }
                    //--------------------------------------------------------------------

                }

            }

        }

        return $bus_times;
    }

    /**
     * @param $str
     * @return string
     * 8 -> 08
     */
    public function hour($str){
        if(strlen($str) == 1){
            $str = '0'.$str;
        }
        return $str;
    }

    public function b_time($str){
        return substr($str,0,2).':'.substr($str,2,2);
    }

    public function plus($str,$leng = 5,$b_time = true){
        if(strlen($str) == 5) $str = substr($str,0,4);
        $h = substr($str,0,2);
        $m = substr($str,2,4)+$leng;
        if($m >= 60){
            $h++;
            $m = '0'.($m-60);
        }else if($m < 10){
            $m = '0'.$m;
        }
        if(strlen($h) == 1){
            $h = '0'.$h;
        }


        if($b_time){
            return $this->b_time($h.$m);
        }else{
            return $h.$m;
        }

    }


    /**
     * @package utf8
     * @subpackage strings
     */
    public function utf8_str_split($str, $split_len = 1)
    {
        if (!preg_match('/^[0-9]+$/', $split_len) || $split_len < 1)
            return FALSE;

        $len = mb_strlen($str, 'UTF-8');
        if ($len <= $split_len)
            return array($str);

        preg_match_all('/.{'.$split_len.'}|[^\x00]{1,'.$split_len.'}$/us', $str, $ar);

        return $ar[0];
    }

    public function train_tip_change($bus_direction,$tip)
    {

        $train_up = array(
                'default' => array('上','上野'),
                'a' => array('逗','逗子'),
                'b' => array('大','大船'),
                'c' => array('宮','大宮'),
                'd' => array('熱','熱海'),
                'e' => array('小','小田原'),
                'f' => array('沼','沼津'),
                'g' => array('平','平塚'),
                'h' => array('国','国府津'),
                'i' => array('伊','伊東'),
                'j' => array('品','品川')
        );
        $train_down = array(
                'default' => array('金','小金井'),
                'a' => array('宇','宇都宮'),
                'b' => array('古','古河'),
                'c' => array('黒','黒磯'),
                'd' => array('い','いわき')
        );

        if($bus_direction == 'jr_nobori'){
            return $train_up[$tip][0];
        }else if($bus_direction == 'jr_kudari'){
            return $train_down[$tip][0];
        }


    }



}