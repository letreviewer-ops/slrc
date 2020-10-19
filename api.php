<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Origin, Content-Type, Authorization, Accept, X-REquested-With, x-xsrf-token');

    //include "config.php";
    define('DB_NAME', 'reviewer');
    define('DB_USER', 'root');
    define('DB_PASSWORD', '');
    define('DB_HOST', 'localhost');
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    date_default_timezone_set('Asia/Manila');
    //include "config.php";

    $postjson = json_decode(file_get_contents('php://input'), true);


    //include "functions.php";
    function query_questions($subject, $start = 0, $limit = 100) {
        global $mysqli;
    
        $data = [
            'name' => $subject,
            'questions' => []
        ];
    
        $query = mysqli_query($mysqli, "SELECT * FROM questions WHERE subject_taken = '$subject' ORDER BY id ASC LIMIT $start, $limit");
    
        while ($rows = mysqli_fetch_array($query)) {
    
            $data['questions'][] = array(
                'name' =>    utf8_encode($rows['question']),
                'options' => [
                    [
                        'name' => utf8_encode($rows['correct_answer']),
                        'isAnswer' => true
                    ],
                    [
                        'name' => utf8_encode($rows['optionA']),
                        'isAnswer' => false
                    ],
                    [
                        'name' => utf8_encode($rows['optionB']),
                        'isAnswer' => false
                    ],
                    [
                        'name' => utf8_encode($rows['optionC']),
                        'isAnswer' => false
                    ],
                ]
            );
        }
    
        if($query) {
            $result = json_encode($data, JSON_THROW_ON_ERROR);
        } else {
            $result = json_encode(array('success' => false));
        }
    
        return $result;
    }
    //include "functions.php";

    
switch($postjson['aksi'])
{

    case "process_examinee_login" :
        $examinee_pin = $postjson['examinee_pin'];

        $examinee_login_data = mysqli_fetch_array(mysqli_query($mysqli, "SELECT * FROM examinees_table WHERE examinee_id = '$postjson[examinee_id]' AND examinee_pin = '$examinee_pin'"));
    
        $data = array(
            'id'                 =>      $examinee_login_data['id'],
            'examinee_id'        =>      $examinee_login_data['examinee_id'],
            'examinee_pin'       =>      $examinee_login_data['examinee_pin'],
            'contact_email'      =>      $examinee_login_data['contact_email'],
            'fullname'           =>      $examinee_login_data['fullname'],
            'gender'             =>      $examinee_login_data['gender'],
            'date_birth'         =>      $examinee_login_data['date_birth']
        );
    
        if($examinee_login_data) {
            $result = json_encode(array('success' => true, 'result' => $data));
        } else {
            $result = json_encode(array('success' => false));
        }
    
        echo $result;

    break;



    
    case "process_admin_login" :
        $admin_pin = md5($postjson['admin_pin']);

        $admin_login_data = mysqli_fetch_array(mysqli_query($mysqli, "SELECT * FROM admin_table WHERE admin_id = '$postjson[admin_id]' AND admin_pin = '$admin_pin'"));
    
        $data = array(
            'id'            =>    $admin_login_data['id'],
            'admin_id'      =>    $admin_login_data['admin_id'],
            'role'          =>    $admin_login_data['role'],
            'email'         =>    $admin_login_data['email']
        );
    
        if($admin_login_data) {
            $result = json_encode(array('success' => true, 'result' => $data));
        } else {
            $result = json_encode(array('success' => false));
        }
    
        echo $result;
    
    break;



    case "count_examinees" :

        $data = array();

        $query = mysqli_query($mysqli, "SELECT COUNT(id) FROM examinees_table");

        while ($rows = mysqli_fetch_array($query)) {
        
            $data = array(
                'success'   =>    true,
                'count'     =>    $rows[0],
            );
        }

        if($query) {
            $result = json_encode($data);
        } else {
            $result = json_encode(array('success' => false));
        }

        echo $result;

    break;




   


    

    
    case "load_examinees" :

        $data = array();

        $query = mysqli_query($mysqli, "SELECT * FROM examinees_table ORDER BY id ASC LIMIT $postjson[start], $postjson[limit]");

        while ($rows = mysqli_fetch_array($query)) {
        
            $data[] = array(
                'id'                =>    $rows['id'],
                'examinee_id'       =>    $rows['examinee_id'],
                'examinee_pin'      =>    $rows['examinee_pin'],
                'contact_email'     =>    $rows['contact_email'],
                'fullname'          =>    $rows['fullname'],
                'gender'            =>    $rows['gender'],
                'date_birth'        =>    $rows['date_birth']
            );
        }

        if($query) {
            $result = json_encode(array('success' => true, 'result' => $data));
        } else {
            $result = json_encode(array('success' => false));
        }

        echo $result;

    break;




    case "delete_user" :

        $query = mysqli_query($mysqli, "DELETE FROM examinees_table WHERE id='$postjson[id]'");

        if($query) {
            $result = json_encode(array('success' => true));
        } else {
            $result = json_encode(array('success' => false));
        }

        echo $result;

    break;





    case "add_examinee" :
        $today = date('Y-m-d H:i:s');

        $checkEID = mysqli_fetch_array(mysqli_query($mysqli, "SELECT examinee_id FROM examinees_table WHERE examinee_id='$postjson[examinee_id]'"));
    
        if ($checkEID['examinee_id'] == $postjson['examinee_id']) {
            
            $result = json_encode(array('success' => false, 'msg' => 'Examinee ID is Already Registered!'));

        } else {
            $examinee_pin = $postjson['examinee_pin'];
    
            $query = mysqli_query($mysqli, "INSERT INTO examinees_table SET
                id                  =       '$postjson[id]',
                examinee_id         =       '$postjson[examinee_id]',
                examinee_pin        =       '$examinee_pin',
                contact_email       =       '$postjson[contact_email]',
                fullname            =       '$postjson[fullname]',
                gender              =       '$postjson[gender]',
                date_birth          =       '$postjson[date_birth]',
                created_at          =       '$today'
            ");
    
             if($query) {
                $result = json_encode(array('success' => true, 'msg' => 'Successfully Added'));
            }
            
        }

        echo $result;
    break;




    case "update_examinee" :

        $today = date('Y-m-d H:i:s');

        $checkEID = mysqli_fetch_array(mysqli_query($mysqli, "SELECT examinee_id FROM examinees_table WHERE examinee_id = '$postjson[examinee_id]'"));

        if ($checkEID['examinee_id'] == $postjson['examinee_id']) {

            $examinee_id = $postjson['examinee_id'];

            $examinee_pin = $postjson['examinee_pin'];

            $query = mysqli_query($mysqli, "UPDATE examinees_table SET
                id                  =       '$postjson[id]',
                examinee_id         =       '$examinee_id',
                examinee_pin        =       '$examinee_pin',
                contact_email       =       '$postjson[contact_email]',
                fullname            =       '$postjson[fullname]',
                gender              =       '$postjson[gender]',
                date_birth          =       '$postjson[date_birth]',
                updated_at          =       '$today'
                WHERE id = '$postjson[id]'
            ");

            if($query) {
                $result = json_encode(array('success' => true, 'msg' => 'Updated'));
            } else {
                $result = json_encode(array('success' => false,'msg' => 'Update Error'));
            }

            echo $result;

        } else {

            $examinee_pin = $postjson['examinee_pin'];

            $query = mysqli_query($mysqli, "UPDATE examinees_table SET
                id                  =       '$postjson[id]',
                examinee_id         =       '$postjson[examinee_id]',
                examinee_pin        =       '$examinee_pin',
                contact_email       =       '$postjson[contact_email]',
                fullname            =       '$postjson[fullname]',
                gender              =       '$postjson[gender]',
                date_birth          =       '$postjson[date_birth]',
                updated_at          =       '$today'
                WHERE id = '$postjson[id]'
            ");
            
            if($query) {
                $result = json_encode(array('success' => true, 'msg' => 'Updated'));
            } else {
                $result = json_encode(array('success' => false,'msg' => 'Update Error'));
            }

            echo $result;
        }

    break;

















    case "load_single_data" :
        $query = mysqli_query($mysqli, "SELECT * FROM examinees_table WHERE id='$postjson[id]'");

        while ($rows = mysqli_fetch_array($query)) {
        
            $data = array(
                'id'                =>    $rows['id'],
                'examinee_id'       =>    $rows['examinee_id'],
                'examinee_pin'      =>    $rows['examinee_pin'],
                'contact_email'     =>    $rows['contact_email'],
                'fullname'          =>    $rows['fullname'],
                'gender'            =>    $rows['gender'],
                'date_birth'        =>    $rows['date_birth']
            );
        }

        if($query) {
            $result = json_encode(array('success' => true, 'result' => $data));
        } else {
            $result = json_encode(array('success' => false));
        }

        echo $result;
    break;













    case "submit_score_stats" :
        $today = date('Y-m-d H:i:s');
        
        $query = mysqli_query($mysqli, "INSERT INTO scores_stats SET
            examinee_id        =       '$postjson[examinee_id]',
            subject_taken      =       '$postjson[subject_taken]',
            score              =       '$postjson[score]',
            date_taken         =       '$today'
        ");

        if($query) {
            $result = json_encode(array('success' => true, 'msg' => 'Added to Score and Stats Table!'));
        }
    
        echo $result;
    break;








    case "count_examinee_scores_stats" :

        $data = array();

        $query = mysqli_query($mysqli, "SELECT COUNT(id) FROM scores_stats");

        while ($rows = mysqli_fetch_array($query)) {
        
            $data = array(
                'success'   =>    true,
                'count'     =>    $rows[0],
            );
        }

        if($query) {
            $result = json_encode($data);
        } else {
            $result = json_encode(array('success' => false));
        }

        echo $result;

    break;













    case "load_examinee_scores_stats" :

        $data = array();

        $query = mysqli_query($mysqli, "SELECT * FROM scores_stats ORDER BY id ASC LIMIT $postjson[start], $postjson[limit]");

        while ($rows = mysqli_fetch_array($query)) {
        
            $data[] = array(
                'id'                  =>    $rows['id'],
                'examinee_id'         =>    $rows['examinee_id'],
                'subject_taken'       =>    $rows['subject_taken'],
                'score'               =>    $rows['score'],
                'date_taken'          =>    $rows['date_taken'],
            );
        }

        if($query) {
            $result = json_encode(array('success' => true, 'result' => $data));
        } else {
            $result = json_encode(array('success' => false));
        }

        echo $result;

    break;

    















    case "load_score_stats_details" :

        $query = mysqli_query($mysqli, "SELECT * FROM scores_stats WHERE id='$postjson[id]'");

        while ($rows = mysqli_fetch_array($query)) {
        
            $data = array(
                'id'                  =>    $rows['id'],
                'examinee_id'         =>    $rows['examinee_id'],
                'subject_taken'       =>    $rows['subject_taken'],
                'score'               =>    $rows['score'],
                'date_taken'          =>    $rows['date_taken'],
            );
        }

        if($query) {
            $result = json_encode(array('success' => true, 'result' => $data));
        } else {
            $result = json_encode(array('success' => false));
        }

        echo $result;
    break;










    
    case "count_load_scores" :

        $data = array();

        $query = mysqli_query($mysqli, "SELECT COUNT(id) FROM scores_stats");

        while ($rows = mysqli_fetch_array($query)) {
        
            $data = array(
                'success'   =>    true,
                'count'     =>    $rows[0],
            );
        }

        if($query) {
            $result = json_encode($data);
        } else {
            $result = json_encode(array('success' => false));
        }

        echo $result;

    break;










    case "load_scores" :

        $data = array();

        $query = mysqli_query($mysqli, "SELECT * FROM scores_stats WHERE examinee_id = '$postjson[examinee_id]' ORDER BY id ASC LIMIT $postjson[start], $postjson[limit]");

        while ($rows = mysqli_fetch_array($query)) {
        
            $data[] = array(
                'id'                  =>    $rows['id'],
                'examinee_id'         =>    $rows['examinee_id'],
                'subject_taken'       =>    $rows['subject_taken'],
                'score'               =>    $rows['score'],
                'date_taken'          =>    $rows['date_taken'],
            );
        }

        if($query) {
            $result = json_encode(array('success' => true, 'result' => $data));
        } else {
            $result = json_encode(array('success' => false));
        }

        echo $result;

    break;



















    case "add_question" :
        $today = date('Y-m-d H:i:s');

        $check = mysqli_fetch_array(mysqli_query($mysqli, "SELECT question FROM questions WHERE question='$postjson[question]'"));
    
        if ($check['question'] == $postjson['question']) {
            
            $result = json_encode(array('success' => false, 'msg' => 'Already Added Question!'));

        } else {
            $query = mysqli_query($mysqli, "INSERT INTO questions SET
                subject_taken       =       '$postjson[subject_taken]',
                question            =       '$postjson[question]',
                correct_answer      =       '$postjson[correct_answer]',
                optionA             =       '$postjson[optionA]',
                optionB             =       '$postjson[optionB]',
                optionC             =       '$postjson[optionC]',
                created_at          =       '$today',
                updated_at          =       '$today'
            ");
    
             if($query) {
                $result = json_encode(array('success' => true, 'msg' => 'Successfully Added'));
            }
        }

        echo $result;
    break;














    case "load_questions" :

        $data = array();

        $query = mysqli_query($mysqli, "SELECT * FROM questions ORDER BY id ASC LIMIT $postjson[start], $postjson[limit]");

        while ($rows = mysqli_fetch_array($query)) {
        
            $data[] = array(
                'id'                =>    $rows['id'],
                'subject_taken'     =>    $rows['subject_taken'],
                'question'          =>    $rows['question'],
                'correct_answer'    =>    $rows['correct_answer'],
                'optionA'           =>    $rows['optionA'],
                'optionB'           =>    $rows['optionB'],
                'optionC'           =>    $rows['optionC'],
                'created_at'        =>    $rows['created_at'],
                'updated_at'        =>    $rows['updated_at'],
            );
        }

        if($query) {
            $result = json_encode(array('success' => true, 'result' => $data));
        } else {
            $result = json_encode(array('success' => false));
        }

        echo $result;

    break;




















    
    case "delete_question" :

        $query = mysqli_query($mysqli, "DELETE FROM questions WHERE id='$postjson[id]'");

        if($query) {
            $result = json_encode(array('success' => true));
        } else {
            $result = json_encode(array('success' => false));
        }

        echo $result;

    break;



















    
    case "update_question" :

        $today = date('Y-m-d H:i:s');

        $checkEID = mysqli_fetch_array(mysqli_query($mysqli, "SELECT question FROM questions WHERE question = '$postjson[question]'"));

        if ($checkEID['question'] == $postjson['question']) {

            $question = $postjson['question'];

            $query = mysqli_query($mysqli, "UPDATE questions SET
                id                  =       '$postjson[id]',
                subject_taken       =       '$postjson[subject_taken]',
                question            =       '$question',
                correct_answer      =       '$postjson[correct_answer]',
                optionA             =       '$postjson[optionA]',
                optionB             =       '$postjson[optionB]',
                optionC             =       '$postjson[optionC]',
                created_at          =       '$postjson[created_at]',
                updated_at          =       '$today'
                WHERE id = '$postjson[id]'
            ");

            if($query) {
                $result = json_encode(array('success' => true, 'msg' => 'Updated'));
            } else {
                $result = json_encode(array('success' => false,'msg' => 'Update Error'));
            }

            echo $result;

        } else {

            $query = mysqli_query($mysqli, "UPDATE questions SET
                id                  =       '$postjson[id]',
                subject_taken       =       '$postjson[subject_taken]',
                question            =       '$postjson[question]',
                correct_answer      =       '$postjson[correct_answer]',
                optionA             =       '$postjson[optionA]',
                optionB             =       '$postjson[optionB]',
                optionC             =       '$postjson[optionC]',
                created_at          =       '$postjson[created_at]',
                updated_at          =       '$today'
                WHERE id = '$postjson[id]'
            ");
            
            if($query) {
                $result = json_encode(array('success' => true, 'msg' => 'Updated'));
            } else {
                $result = json_encode(array('success' => false,'msg' => 'Update Error'));
            }

            echo $result;
        }

    break;










    case "details_question" :

        $query = mysqli_query($mysqli, "SELECT * FROM questions WHERE id='$postjson[id]'");

        while ($rows = mysqli_fetch_array($query)) {
        
            $data = array(
                'id'                =>    $rows['id'],
                'subject_taken'     =>    $rows['subject_taken'],
                'question'          =>    $rows['question'],
                'correct_answer'    =>    $rows['correct_answer'],
                'optionA'           =>    $rows['optionA'],
                'optionB'           =>    $rows['optionB'],
                'optionC'           =>    $rows['optionC'],
                'created_at'        =>    $rows['created_at'],
                'updated_at'        =>    $rows['updated_at'],
            );
        }

        if($query) {
            $result = json_encode(array('success' => true, 'result' => $data));
        } else {
            $result = json_encode(array('success' => false));
        }

        echo $result;
    break;



    case "load_english_questions" :
        $data = array();

        $query = mysqli_query($mysqli, "SELECT * FROM questions WHERE subject_taken = 'ENGLISH' ORDER BY id ASC LIMIT $postjson[start], $postjson[limit]");

        while ($rows = mysqli_fetch_array($query)) {
            $data[] = array(
                'id'                =>    $rows['id'],
                'subject_taken'     =>    $rows['subject_taken'],
                'question'          =>    $rows['question'],
                'correct_answer'    =>    $rows['correct_answer'],
                'optionA'           =>    $rows['optionA'],
                'optionB'           =>    $rows['optionB'],
                'optionC'           =>    $rows['optionC'],
                'created_at'        =>    $rows['created_at'],
                'updated_at'        =>    $rows['updated_at'],
            );
        }
        if($query) {
            $result = json_encode(array('success' => true, 'result' => $data));
        } else {
            $result = json_encode(array('success' => false));
        }

        echo $result;

    break;



    case "load_filipino_questions" :

        $data = array();

        $query = mysqli_query($mysqli, "SELECT * FROM questions WHERE subject_taken = 'FILIPINO' ORDER BY id ASC LIMIT $postjson[start], $postjson[limit]");

        while ($rows = mysqli_fetch_array($query)) {
        
            $data[] = array(
                'id'                =>    $rows['id'],
                'subject_taken'     =>    $rows['subject_taken'],
                'question'          =>    $rows['question'],
                'correct_answer'    =>    $rows['correct_answer'],
                'optionA'           =>    $rows['optionA'],
                'optionB'           =>    $rows['optionB'],
                'optionC'           =>    $rows['optionC'],
                'created_at'        =>    $rows['created_at'],
                'updated_at'        =>    $rows['updated_at'],
            );
        }

        if($query) {
            $result = json_encode(array('success' => true, 'result' => $data));
        } else {
            $result = json_encode(array('success' => false));
        }

        echo $result;

    break;


    case "load_math_questions" :

        $data = array();

        $query = mysqli_query($mysqli, "SELECT * FROM questions WHERE subject_taken = 'MATHEMATICS' ORDER BY id ASC LIMIT $postjson[start], $postjson[limit]");

        while ($rows = mysqli_fetch_array($query)) {
        
            $data[] = array(
                'id'                =>    $rows['id'],
                'subject_taken'     =>    $rows['subject_taken'],
                'question'          =>    $rows['question'],
                'correct_answer'    =>    $rows['correct_answer'],
                'optionA'           =>    $rows['optionA'],
                'optionB'           =>    $rows['optionB'],
                'optionC'           =>    $rows['optionC'],
                'created_at'        =>    $rows['created_at'],
                'updated_at'        =>    $rows['updated_at'],
            );
        }

        if($query) {
            $result = json_encode(array('success' => true, 'result' => $data));
        } else {
            $result = json_encode(array('success' => false));
        }

        echo $result;

    break;


    case "load_science_questions" :
        $data = array();

        $query = mysqli_query($mysqli, "SELECT * FROM questions WHERE subject_taken = 'SCIENCE' ORDER BY id ASC LIMIT $postjson[start], $postjson[limit]");

        while ($rows = mysqli_fetch_array($query)) {
            $data[] = array(
                'id'                =>    $rows['id'],
                'subject_taken'     =>    $rows['subject_taken'],
                'question'          =>    $rows['question'],
                'correct_answer'    =>    $rows['correct_answer'],
                'optionA'           =>    $rows['optionA'],
                'optionB'           =>    $rows['optionB'],
                'optionC'           =>    $rows['optionC'],
                'created_at'        =>    $rows['created_at'],
                'updated_at'        =>    $rows['updated_at'],
            );
        }
        if($query) {
            $result = json_encode(array('success' => true, 'result' => $data));
        } else {
            $result = json_encode(array('success' => false));
        }
        echo $result;
    break;


    case "load_socstud_questions" :

        $data = array();

        $query = mysqli_query($mysqli, "SELECT * FROM questions WHERE subject_taken = 'SOCIAL STUDIES' ORDER BY id ASC LIMIT $postjson[start], $postjson[limit]");

        while ($rows = mysqli_fetch_array($query)) {
            $data[] = array(
                'id'                =>    $rows['id'],
                'subject_taken'     =>    $rows['subject_taken'],
                'question'          =>    $rows['question'],
                'correct_answer'    =>    $rows['correct_answer'],
                'optionA'           =>    $rows['optionA'],
                'optionB'           =>    $rows['optionB'],
                'optionC'           =>    $rows['optionC'],
                'created_at'        =>    $rows['created_at'],
                'updated_at'        =>    $rows['updated_at'],
            );
        }
        if($query) {
            $result = json_encode(array('success' => true, 'result' => $data));
        } else {
            $result = json_encode(array('success' => false));
        }
        echo $result;
    break;


    case "count_questions" :

        $data = array();

        $query = mysqli_query($mysqli, "SELECT COUNT(id) FROM questions");

        while ($rows = mysqli_fetch_array($query)) {
            $data = array(
                'success'   =>    true,
                'count'     =>    $rows[0],
            );
        }

        if($query) {
            $result = json_encode($data);
        } else {
            $result = json_encode(array('success' => false));
        }
        echo $result;
    break;

    case "load_english_qa" :
        // See functions.php
        echo query_questions('ENGLISH', $postjson['start'], $postjson['limit']);
    break;

    case "load_filipino_qa" :
        // See functions.php
        echo query_questions('FILIPINO', $postjson['start'], $postjson['limit']);
    break;

    case "load_math_qa" :
        // See functions.php
        echo query_questions('MATHEMATICS', $postjson['start'], $postjson['limit']);
    break;

    case "load_science_qa" :
        // See functions.php
        echo query_questions('SCIENCE', $postjson['start'], $postjson['limit']);
    break;

    case "load_socstud_qa" :
        // See functions.php
        echo query_questions('SOCIAL STUDIES', $postjson['start'], $postjson['limit']);
    break;

    default:
        echo 'api.php';
}